<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Command;

use App\Demo\Command\ConsoleSubprocess;
use App\Demo\Command\DemoResetCommand;
use App\Demo\Command\ResetStatus;
use App\Demo\Media\ImagePathSource;
use App\Demo\Media\OrphanImagePurger;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sylius\Bundle\FixturesBundle\Loader\SuiteLoaderInterface;
use Sylius\Bundle\FixturesBundle\Suite\SuiteInterface;
use Sylius\Bundle\FixturesBundle\Suite\SuiteRegistryInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

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

    private Connection&MockObject $connection;

    private DemoResetCommand $command;

    private string $statusFile;

    private string $projectDir;

    protected function setUp(): void
    {
        $this->statusFile = sys_get_temp_dir() . '/demo-reset-status-' . bin2hex(random_bytes(4));
        $this->projectDir = sys_get_temp_dir() . '/demo-reset-project-' . bin2hex(random_bytes(4));
        mkdir($this->projectDir . '/bin', 0o777, true);
        $this->consoleStub('exit(0);');
        $this->suiteRegistry = $this->createMock(SuiteRegistryInterface::class);
        $this->suiteLoader = $this->createMock(SuiteLoaderInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->connection = $this->createMock(Connection::class);

        $this->command = $this->command(true);
    }

    private function command(bool $demoMode): DemoResetCommand
    {
        return new DemoResetCommand($this->suiteRegistry, $this->suiteLoader, $this->logger, $this->connection, $demoMode, new ResetStatus($this->statusFile), new OrphanImagePurger(new class() implements ImagePathSource {
            public function paths(): iterable
            {
                return [];
            }
        }, new Filesystem(), new NullLogger(), sys_get_temp_dir() . '/absent-images-' . bin2hex(random_bytes(4))), new ConsoleSubprocess($this->projectDir, 'test'));
    }

    protected function tearDown(): void
    {
        @unlink($this->statusFile);
        new Filesystem()->remove($this->projectDir);
    }

    private function consoleStub(string $body): void
    {
        file_put_contents($this->projectDir . '/bin/console', "<?php\n" . $body . "\n");
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

    public function testCommandHasCacheAndFailureSimulationOptions(): void
    {
        $definition = $this->command->getDefinition();

        self::assertFalse($definition->getOption('no-cache')->acceptValue());
        self::assertFalse($definition->getOption('simulate-failure')->acceptValue());
    }

    public function testWithoutDemoModeTheResetIsRefusedBeforeTouchingAnything(): void
    {
        $this->suiteRegistry->expects(self::never())->method('getSuite');
        $this->suiteLoader->expects(self::never())->method('load');
        $this->connection->expects(self::never())->method('beginTransaction');

        $tester = new CommandTester($this->command(false));
        $exitCode = $tester->execute([], ['capture_stderr_separately' => true]);

        self::assertSame(Command::FAILURE, $exitCode);
        self::assertStringContainsString('DEMO_RESET_REFUSED', $tester->getErrorOutput());
        self::assertSame('', $tester->getDisplay());
    }

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
        self::assertStringContainsString('DEMO_RESET_FAILED', $tester->getDisplay());
        self::assertStringContainsString('Simulated fixture failure.', $tester->getDisplay());
        self::assertTrue(new ResetStatus($this->statusFile)->hasFailed(), 'The failure stays visible to /healthz (US-033).');
    }

    // -------------------------------------------------------------------------
    // US-049 — cache clear and warm-up in a sub-process
    // -------------------------------------------------------------------------

    public function testTheCacheStepRunsClearThenWarmupInSubProcesses(): void
    {
        $log = $this->projectDir . '/calls.log';
        $this->consoleStub('file_put_contents(' . var_export($log, true) . ', $argv[1] . " " . getmypid() . "\n", FILE_APPEND);');
        $this->loadFixtures();

        $exitCode = new CommandTester($this->command)->execute(['--no-media' => true]);

        self::assertSame(Command::SUCCESS, $exitCode);
        $calls = array_map(static fn (string $line): array => explode(' ', $line), array_filter(explode("\n", (string) file_get_contents($log))));
        self::assertSame(['cache:clear', 'cache:warmup'], array_column($calls, 0));
        self::assertNotContains((string) getmypid(), array_column($calls, 1), 'Not in the process of the fixtures.');
        self::assertFileDoesNotExist($this->statusFile, 'A reset that succeeds leaves no marker.');
    }

    public function testAWarmupThatExitsNonZeroFailsTheResetAndRaisesTheMarker(): void
    {
        $this->consoleStub('exit($argv[1] === "cache:warmup" ? 255 : 0);');
        $this->loadFixtures();
        $tester = new CommandTester($this->command);

        $exitCode = $tester->execute(['--no-media' => true], ['capture_stderr_separately' => true]);

        self::assertSame(Command::FAILURE, $exitCode);
        self::assertStringContainsString('DEMO_RESET_FAILED', $tester->getErrorOutput());
        self::assertStringContainsString('cache:warmup', $tester->getErrorOutput());
        self::assertTrue(new ResetStatus($this->statusFile)->hasFailed(), '/healthz answers 503.');
    }

    public function testAFailedResetThenASuccessfulOneClearsTheMarker(): void
    {
        $this->consoleStub('exit(1);');
        $this->loadFixtures();
        new CommandTester($this->command)->execute(['--no-media' => true]);
        self::assertTrue(new ResetStatus($this->statusFile)->hasFailed());

        $this->consoleStub('exit(0);');
        $exitCode = new CommandTester($this->command)->execute(['--no-media' => true]);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertFalse(new ResetStatus($this->statusFile)->hasFailed());
    }

    public function testADryRunNeverTouchesTheMarkerNorTheSubProcess(): void
    {
        $this->consoleStub('exit(9);');

        $exitCode = new CommandTester($this->command)->execute(['--dry-run' => true]);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertFileDoesNotExist($this->statusFile);
    }

    private function loadFixtures(): void
    {
        $this->suiteRegistry->method('getSuite')->willReturn($this->createMock(SuiteInterface::class));
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
