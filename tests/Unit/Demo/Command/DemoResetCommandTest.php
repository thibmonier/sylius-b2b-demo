<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Command;

use App\Demo\Command\DemoResetCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sylius\Bundle\FixturesBundle\Loader\SuiteLoaderInterface;
use Sylius\Bundle\FixturesBundle\Suite\SuiteInterface;
use Sylius\Bundle\FixturesBundle\Suite\SuiteRegistryInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit tests for DemoResetCommand (US-012 / T-012-08, T-012-09).
 *
 * We use `--dry-run` to verify the happy-path exit code without a real database.
 * The error scenario is tested by making the SuiteLoader throw an exception when
 * the command is run without --dry-run (fixture step is the first step that can be
 * exercised in isolation after a mocked DB reset bypass via --dry-run not being used).
 *
 * The test also verifies command metadata (name, options) to guarantee correct
 * registration in the Symfony DI container.
 */
final class DemoResetCommandTest extends TestCase
{
    /** @var SuiteRegistryInterface&MockObject */
    private SuiteRegistryInterface $suiteRegistry;

    /** @var SuiteLoaderInterface&MockObject */
    private SuiteLoaderInterface $suiteLoader;

    /** @var LoggerInterface&MockObject */
    private LoggerInterface $logger;

    private DemoResetCommand $command;

    protected function setUp(): void
    {
        $this->suiteRegistry = $this->createMock(SuiteRegistryInterface::class);
        $this->suiteLoader = $this->createMock(SuiteLoaderInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->command = new DemoResetCommand(
            $this->suiteRegistry,
            $this->suiteLoader,
            $this->logger,
        );
    }

    // -------------------------------------------------------------------------
    // Command metadata
    // -------------------------------------------------------------------------

    public function testCommandName(): void
    {
        self::assertSame('app:demo:reset', $this->command->getName());
    }

    public function testCommandHasDryRunOption(): void
    {
        $definition = $this->command->getDefinition();

        self::assertTrue($definition->hasOption('dry-run'));
        self::assertFalse($definition->getOption('dry-run')->acceptValue(), '--dry-run must be a VALUE_NONE flag');
    }

    public function testCommandHasNoMediaOption(): void
    {
        $definition = $this->command->getDefinition();

        self::assertTrue($definition->hasOption('no-media'));
        self::assertFalse($definition->getOption('no-media')->acceptValue(), '--no-media must be a VALUE_NONE flag');
    }

    // -------------------------------------------------------------------------
    // Happy path — dry-run (no real DB / no sub-commands needed)
    // -------------------------------------------------------------------------

    public function testDryRunReturnsSuccessWithoutExecutingSubCommands(): void
    {
        // Arrange: registry and loader must NOT be called in dry-run mode
        $this->suiteRegistry->expects(self::never())->method('getSuite');
        $this->suiteLoader->expects(self::never())->method('load');

        // We still need a minimal Application so getApplication() returns non-null
        // even when running steps that check for liip:imagine, etc.
        $app = new Application();
        $app->setAutoExit(false);
        $app->addCommand($this->command);

        $tester = new CommandTester($this->command);

        // Act
        $exitCode = $tester->execute(['--dry-run' => true]);

        // Assert
        self::assertSame(Command::SUCCESS, $exitCode, 'Dry-run must return exit code 0.');
        self::assertStringContainsString('[DRY-RUN]', $tester->getDisplay());
    }

    // -------------------------------------------------------------------------
    // Error path — suite loader throws → FAILURE + non-zero exit code
    // -------------------------------------------------------------------------

    public function testFixtureErrorReturnsFailureExitCode(): void
    {
        // Arrange: DB sub-commands need a registered application; we skip them by
        // injecting fake stub commands that return SUCCESS instantly.
        $app = $this->buildFakeApplication();

        $suite = $this->createMock(SuiteInterface::class);
        $this->suiteRegistry->expects(self::once())
            ->method('getSuite')
            ->with('demo')
            ->willReturn($suite);

        $this->suiteLoader->expects(self::once())
            ->method('load')
            ->willThrowException(new \RuntimeException('Simulated fixture failure.'));

        $tester = new CommandTester($this->command);

        // Act
        $exitCode = $tester->execute([]);

        // Assert
        self::assertSame(Command::FAILURE, $exitCode, 'A fixture failure must return a non-zero exit code.');
        self::assertStringContainsString('Simulated fixture failure.', $tester->getDisplay());
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Builds a fake Symfony Application containing stub commands for the
     * cache sub-commands that DemoResetCommand delegates to.
     */
    private function buildFakeApplication(): Application
    {
        $app = new Application();
        $app->setAutoExit(false);
        $app->addCommand($this->command);

        foreach ([
            'cache:clear',
            'cache:warmup',
        ] as $commandName) {
            $stub = new class($commandName) extends Command {
                public function __construct(string $name)
                {
                    parent::__construct($name);
                }

                protected function configure(): void
                {
                    $this->ignoreValidationErrors();
                }

                protected function execute(InputInterface $input, OutputInterface $output): int
                {
                    return Command::SUCCESS;
                }
            };
            $app->addCommand($stub);
        }

        return $app;
    }
}
