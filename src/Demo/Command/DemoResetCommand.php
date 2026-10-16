<?php

declare(strict_types=1);

namespace App\Demo\Command;

use Psr\Log\LoggerInterface;
use Sylius\Bundle\FixturesBundle\Loader\SuiteLoaderInterface;
use Sylius\Bundle\FixturesBundle\Suite\SuiteRegistryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * Resets the demo environment to its initial state (US-012).
 *
 * Steps:
 *   1. Reload the `demo` fixture suite (SuiteRegistry + SuiteLoader, identical to
 *      `sylius:fixtures:load demo --no-interaction`, orm_purger listener included).
 *      The purger truncates the data, so no DDL is needed; running drop/create/migrate
 *      as in-process sub-commands here would corrupt the shared connection (savepoints).
 *   2. Optionally purge the LiipImagine cache to force image regeneration on next hit.
 *   3. Clear and warm up the application cache.
 *
 * All steps are logged; any exception returns Command::FAILURE (exit code 1).
 *
 * Options:
 *   --dry-run   Log what would happen but execute nothing.
 *   --no-media  Skip the media-cache purge step.
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
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Log steps without executing anything.')
            ->addOption('no-media', null, InputOption::VALUE_NONE, 'Skip the LiipImagine media-cache purge step.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $noMedia = (bool) $input->getOption('no-media');
        $startedAt = \microtime(true);

        $io->title($dryRun ? '[DRY-RUN] Demo reset' : 'Demo reset');
        $this->logger->info('app:demo:reset started', ['dry_run' => $dryRun]);

        try {
            $this->stepLoadFixtures($io, $dryRun);

            if (!$noMedia) {
                $this->stepPurgeMediaCache($io, $output, $dryRun);
            }

            $this->stepClearWarmUpCache($io, $output, $dryRun);
        } catch (Throwable $e) {
            $this->logger->error('app:demo:reset failed', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $io->error(\sprintf('Demo reset failed: %s', $e->getMessage()));

            return Command::FAILURE;
        }

        $elapsed = \round(\microtime(true) - $startedAt, 1);
        $this->logger->info('app:demo:reset finished', ['elapsed_seconds' => $elapsed]);
        $io->success(\sprintf('Demo reset completed in %s s.', $elapsed));

        return Command::SUCCESS;
    }

    // -------------------------------------------------------------------------
    // Step 1 — Load fixtures (purge + reload)
    // -------------------------------------------------------------------------

    private function stepLoadFixtures(SymfonyStyle $io, bool $dryRun): void
    {
        $io->section('Step 1 — Fixture suite "demo" (purge + reload)');
        $this->logger->info('Step 1: loading fixture suite demo');

        if ($dryRun) {
            $io->text('[DRY-RUN] Would load suite "demo" via SuiteLoader.');

            return;
        }

        $t = \microtime(true);
        $suite = $this->suiteRegistry->getSuite('demo');
        $this->suiteLoader->load($suite);
        $elapsed = \round(\microtime(true) - $t, 1);

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

        $this->runSubCommand('cache:clear', ['--no-debug' => true, '--no-interaction' => true], $output, $dryRun);
        $this->runSubCommand('cache:warmup', ['--no-debug' => true, '--no-interaction' => true], $output, $dryRun);

        $io->text('Cache refreshed.');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

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
        $elapsed = \round(\microtime(true) - $t, 1);

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
