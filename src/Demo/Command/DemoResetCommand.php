<?php

declare(strict_types=1);

namespace App\Demo\Command;

use App\Demo\Media\OrphanImagePurger;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\FixturesBundle\Loader\SuiteLoaderInterface;
use Sylius\Bundle\FixturesBundle\Suite\SuiteRegistryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;

/**
 * Resets the demo environment to its initial state (US-012, US-033).
 *
 * Steps:
 *   1. Reload the `demo` fixture suite (SuiteRegistry + SuiteLoader, identical to
 *      `sylius:fixtures:load demo --no-interaction`, orm_purger listener included), inside ONE database
 *      transaction: the purger deletes rows (no DDL), so a failure anywhere rolls the whole reload back and the
 *      demo keeps the data it had (ADR-0003). The same purge removes the orders and the trade-credit
 *      consumption left by the PunchOut simulator (US-027).
 *   2. Optionally purge the LiipImagine cache to force image regeneration on next hit.
 *   3. Clear and warm up the application cache, each in a PHP process of its own (fresh memory, US-049).
 *   4. Purge the uploaded images that no image row points to (US-040): after the commit, a failure is reported
 *      (DEMO_RESET_FAILED, /healthz) and never rolls the data back.
 *
 * The reset is wanted in the `prod` environment of the demo and nowhere else: it only runs with DEMO_MODE=1.
 *
 * Outcome, for the logs of the platform (supercronic forwards both streams): `DEMO_RESET_OK duration=…` on stdout,
 * `DEMO_RESET_FAILED cause=… duration=…` or `DEMO_RESET_REFUSED …` on stderr with a non-zero exit code.
 *
 * Options:
 *   --dry-run           Log what would happen but execute nothing.
 *   --no-media          Skip the media-cache purge step.
 *   --no-cache          Skip the cache clear and warm-up step.
 *   --simulate-failure  Fail after the reload, before the commit (operator check of the alert and of the rollback).
 */
#[AsCommand(
    name: 'app:demo:reset',
    description: 'Resets the demo environment to its initial state (purge + reload fixtures + cache warm-up).',
)]
final class DemoResetCommand extends Command
{
    public function __construct(
        private readonly SuiteRegistryInterface $suiteRegistry,
        private readonly SuiteLoaderInterface $suiteLoader,
        private readonly LoggerInterface $logger,
        private readonly Connection $connection,
        #[Autowire(param: 'demo.mode')]
        private readonly bool $demoMode,
        private readonly ResetStatus $resetStatus,
        private readonly OrphanImagePurger $imagePurger,
        private readonly ConsoleSubprocess $subprocess,
        private readonly ResetLock $lock,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Log steps without executing anything.')
            ->addOption('no-media', null, InputOption::VALUE_NONE, 'Skip the LiipImagine media-cache purge step.')
            ->addOption('no-cache', null, InputOption::VALUE_NONE, 'Skip the cache clear and warm-up step.')
            ->addOption('simulate-failure', null, InputOption::VALUE_NONE, 'Fail after the reload and before the commit, to check the rollback and the alert.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $errors = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        $dryRun = (bool) $input->getOption('dry-run');
        $startedAt = \microtime(true);

        if (!$this->demoMode) {
            $this->logger->error('app:demo:reset refused: DEMO_MODE is not enabled');
            $errors->writeln('DEMO_RESET_REFUSED cause="DEMO_MODE=1 is required: this command wipes the whole database"');

            return Command::FAILURE;
        }

        if (!$dryRun && !$this->lock->acquire()) {
            $this->logger->error('app:demo:reset refused: another reset is running');
            $errors->writeln('DEMO_RESET_REFUSED cause="another reset is already running (cron or manual): nothing was touched"');

            return Command::FAILURE;
        }

        try {
            return $this->reset($io, $output, $errors, $input, $dryRun, $startedAt);
        } finally {
            $this->lock->release();
        }
    }

    private function reset(SymfonyStyle $io, OutputInterface $output, OutputInterface $errors, InputInterface $input, bool $dryRun, float $startedAt): int
    {
        $io->title($dryRun ? '[DRY-RUN] Demo reset' : 'Demo reset');
        if (!$dryRun) {
            // Written before anything can go wrong: only a reset that ends well removes it (US-049).
            $this->resetStatus->markStarted();
        }
        $this->logger->info('app:demo:reset started', ['dry_run' => $dryRun]);

        try {
            $this->runSteps($io, $output, $input, $dryRun);
        } catch (Throwable $e) {
            return $this->fail($e, $errors, $dryRun, $startedAt);
        }

        $elapsed = $this->elapsedSince($startedAt);
        $this->logger->info('app:demo:reset finished', ['elapsed_seconds' => $elapsed]);
        $output->writeln(\sprintf('DEMO_RESET_OK duration=%ss', $elapsed));
        if (!$dryRun) {
            $this->resetStatus->markSucceeded();
        }

        return Command::SUCCESS;
    }

    private function runSteps(SymfonyStyle $io, OutputInterface $output, InputInterface $input, bool $dryRun): void
    {
        $this->stepLoadFixtures($io, $dryRun, (bool) $input->getOption('simulate-failure'));

        if (!$input->getOption('no-media')) {
            $this->stepPurgeMediaCache($io, $output, $dryRun);
        }

        if (!$input->getOption('no-cache')) {
            $this->stepClearWarmUpCache($io, $output, $dryRun);
        }

        $this->stepPurgeOrphanImages($io, $dryRun);
    }

    private function fail(Throwable $e, OutputInterface $errors, bool $dryRun, float $startedAt): int
    {
        $this->logger->error('app:demo:reset failed', [
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        $errors->writeln(\sprintf(
            'DEMO_RESET_FAILED cause=%s duration=%ss',
            \json_encode($e->getMessage(), \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES),
            $this->elapsedSince($startedAt),
        ));
        if (!$dryRun) {
            $this->resetStatus->markFailed();
        }

        return Command::FAILURE;
    }

    private function elapsedSince(float $startedAt): float
    {
        return \round(\microtime(true) - $startedAt, 1);
    }

    // -------------------------------------------------------------------------
    // Step 1 — Load fixtures (purge + reload)
    // -------------------------------------------------------------------------

    private function stepLoadFixtures(SymfonyStyle $io, bool $dryRun, bool $simulateFailure): void
    {
        $io->section('Step 1 — Fixture suite "demo" (purge + reload)');
        $this->logger->info('Step 1: loading fixture suite demo');

        if ($dryRun) {
            $io->text('[DRY-RUN] Would load suite "demo" via SuiteLoader.');

            return;
        }

        $t = \microtime(true);
        $this->connection->beginTransaction();

        try {
            $this->suiteLoader->load($this->suiteRegistry->getSuite('demo'));

            if ($simulateFailure) {
                throw new \RuntimeException('Simulated failure requested with --simulate-failure.');
            }

            $this->connection->commit();
        } catch (Throwable $e) {
            if ($this->connection->isTransactionActive()) {
                $this->connection->rollBack();
            }
            $io->text('Reload failed: the previous data is kept (rollback).');

            throw $e;
        }

        $elapsed = $this->elapsedSince($t);

        $this->logger->info('Step 1: fixtures loaded', ['elapsed_seconds' => $elapsed]);
        $io->text(\sprintf('Fixtures loaded in %s s.', $elapsed));
    }

    // -------------------------------------------------------------------------
    // Step 2 — Media cache purge (optional)
    // -------------------------------------------------------------------------

    private function stepPurgeMediaCache(SymfonyStyle $io, OutputInterface $output, bool $dryRun): void
    {
        $io->section('Step 2 — Media cache purge');
        $this->logger->info('Step 2: media cache purge');

        $application = $this->getApplication();

        // Only execute when LiipImagineBundle is available.
        if ($application !== null && $application->has('liip:imagine:cache:remove')) {
            $this->runSubCommand('liip:imagine:cache:remove', ['--no-interaction' => true], $output, $dryRun);
            $io->text('LiipImagine cache purged.');
        } else {
            $io->text('LiipImagine not available — media step skipped.');
            $this->logger->info('Step 2: liip:imagine not available, skipped');
        }
    }

    // -------------------------------------------------------------------------
    // Step 3 — Cache clear + warm-up
    // -------------------------------------------------------------------------

    private function stepClearWarmUpCache(SymfonyStyle $io, OutputInterface $output, bool $dryRun): void
    {
        $io->section('Step 3 — Cache: clear and warm-up');
        $this->logger->info('Step 3: cache clear + warmup');

        $this->runInSubprocess('cache:clear', $output, $dryRun);
        $this->runInSubprocess('cache:warmup', $output, $dryRun);

        $io->text('Cache refreshed.');
    }

    // -------------------------------------------------------------------------
    // Step 4 — Orphan uploads
    // -------------------------------------------------------------------------

    private function stepPurgeOrphanImages(SymfonyStyle $io, bool $dryRun): void
    {
        $io->section('Step 4 — Orphan images');
        $this->logger->info('Step 4: orphan image purge');

        if ($dryRun) {
            $io->text('[DRY-RUN] Would remove the images no row points to.');

            return;
        }

        $report = $this->imagePurger->purge();
        $io->text(\sprintf('%d orphan image(s) removed.', $report->deleted));
        if ($report->hasFailures()) {
            throw new \RuntimeException(\sprintf('%d orphan image(s) could not be removed (see the logs).', \count($report->failed)));
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function runInSubprocess(string $name, OutputInterface $output, bool $dryRun): void
    {
        $this->logger->info(\sprintf('Sub-process: %s', $name), ['dry_run' => $dryRun]);

        if ($dryRun) {
            $output->writeln(\sprintf('[DRY-RUN] Would run: %s', $name));

            return;
        }

        $t = \microtime(true);
        $returnCode = $this->subprocess->run($name, ['--no-debug'], $output);
        $this->logger->info(\sprintf('Sub-process "%s" finished', $name), [
            'return_code' => $returnCode,
            'elapsed_seconds' => $this->elapsedSince($t),
        ]);

        if ($returnCode !== Command::SUCCESS) {
            throw new \RuntimeException(\sprintf('Sub-process "%s" failed with return code %d.', $name, $returnCode));
        }
    }

    /**
     * @param array<string, bool|string|null> $args
     */
    private function runSubCommand(string $name, array $args, OutputInterface $output, bool $dryRun): void
    {
        $this->logger->info(\sprintf('Sub-command: %s', $name), ['args' => $args, 'dry_run' => $dryRun]);

        if ($dryRun) {
            $output->writeln(\sprintf('[DRY-RUN] Would run: %s', $name));

            return;
        }

        $application = $this->getApplication();

        if ($application === null) {
            throw new \LogicException(\sprintf(
                'Cannot run sub-command "%s": no Application is attached to this command.',
                $name,
            ));
        }

        $command = $application->find($name);
        $input = new ArrayInput($args);
        $input->setInteractive(false);

        $t = \microtime(true);
        $returnCode = $command->run($input, $output);
        $elapsed = $this->elapsedSince($t);

        $this->logger->info(\sprintf('Sub-command "%s" finished', $name), [
            'return_code' => $returnCode,
            'elapsed_seconds' => $elapsed,
        ]);

        if ($returnCode !== Command::SUCCESS) {
            throw new \RuntimeException(\sprintf(
                'Sub-command "%s" failed with return code %d.',
                $name,
                $returnCode,
            ));
        }
    }
}
