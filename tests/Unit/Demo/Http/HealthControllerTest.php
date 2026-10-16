<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Http;

use App\Demo\Command\ResetStatus;
use App\Demo\Controller\HealthController;
use App\Demo\Http\BasicAuthGate;
use App\Demo\Http\DemoConfigurationGuard;
use Doctrine\DBAL\Connection;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Metadata\AvailableMigration;
use Doctrine\Migrations\Metadata\AvailableMigrationsList;
use Doctrine\Migrations\Version\MigrationStatusCalculator;
use Doctrine\Migrations\Version\Version;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/** US-032 / T-032-04: the healthcheck answers on the database and the migrations, and says nothing more. */
final class HealthControllerTest extends TestCase
{
    public function testItIsOkWhenTheDatabaseAnswersAndEveryMigrationIsApplied(): void
    {
        $response = $this->controller()();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('ok', $response->getContent());
    }

    public function testAnUnavailableDatabaseAnswers503WithoutTraceNorHostName(): void
    {
        $connection = $this->createStub(Connection::class);
        $connection->method('executeQuery')->willThrowException(new \RuntimeException('SQLSTATE[HY000] [2002] Connection refused to db.internal.example:3306'));

        $response = $this->controller(connection: $connection)();

        self::assertSame(503, $response->getStatusCode());
        self::assertSame('database unavailable', $response->getContent());
        self::assertStringNotContainsString('example', (string) $response->getContent());
        self::assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    public function testPendingMigrationsAnswer503(): void
    {
        $response = $this->controller(pending: 1)();

        self::assertSame(503, $response->getStatusCode());
        self::assertSame('migrations pending', $response->getContent());
    }

    public function testAnUnreadableMigrationStateIsNotHealthy(): void
    {
        $response = $this->controller(migrationsFail: true)();

        self::assertSame(503, $response->getStatusCode());
    }

    public function testAnUnsafeConfigurationAnswers503(): void
    {
        $guard = new DemoConfigurationGuard(new BasicAuthGate(true, false, '', ''), true, 'smtp://smtp.provider.example', 'mailpit', str_repeat('s', 40), '^demo\.example$');

        $response = $this->controller(guard: $guard)();

        self::assertSame(503, $response->getStatusCode());
        self::assertSame('misconfigured', $response->getContent());
    }

    public function testAFailedNightlyResetAnswers503UntilTheNextSuccess(): void
    {
        $file = sys_get_temp_dir() . '/demo-reset-status-' . bin2hex(random_bytes(4));
        $status = new ResetStatus($file);
        $status->markFailed();

        try {
            $response = $this->controller(resetStatus: $status)();
            self::assertSame(503, $response->getStatusCode(), 'An uptime monitor on /healthz alerts on a failed reset (US-033).');
            self::assertSame('reset failed', $response->getContent());

            $status->markSucceeded();
            self::assertSame(200, $this->controller(resetStatus: $status)()->getStatusCode());
        } finally {
            @unlink($file);
        }
    }

    public function testTheDatabaseIsNotQueriedAgainWithinTheCacheWindow(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())->method('executeQuery');
        $cache = new ArrayAdapter();

        $first = $this->controller(connection: $connection, cache: $cache)();
        $second = $this->controller(connection: $connection, cache: $cache)();

        self::assertSame(200, $first->getStatusCode());
        self::assertSame(200, $second->getStatusCode());
    }

    public function testAFailedResetIsSeenAtOnceEvenWhenTheStorageVerdictIsCached(): void
    {
        $file = sys_get_temp_dir() . '/demo-reset-status-' . bin2hex(random_bytes(4));
        $status = new ResetStatus($file);
        $cache = new ArrayAdapter();

        try {
            self::assertSame(200, $this->controller(resetStatus: $status, cache: $cache)()->getStatusCode());
            $status->markFailed();
            self::assertSame(503, $this->controller(resetStatus: $status, cache: $cache)()->getStatusCode(), 'The reset marker is a file check, never cached.');
        } finally {
            @unlink($file);
        }
    }

    private function controller(?Connection $connection = null, int $pending = 0, bool $migrationsFail = false, ?DemoConfigurationGuard $guard = null, ?ResetStatus $resetStatus = null, ?CacheItemPoolInterface $cache = null): HealthController
    {
        $connection ??= $this->createStub(Connection::class);
        $calculator = $this->createStub(MigrationStatusCalculator::class);
        if ($migrationsFail) {
            $calculator->method('getNewMigrations')->willThrowException(new \RuntimeException('metadata table missing'));
        } else {
            $calculator->method('getNewMigrations')->willReturn(new AvailableMigrationsList($this->migrations($pending)));
        }
        $factory = $this->createStub(DependencyFactory::class);
        $factory->method('getMigrationStatusCalculator')->willReturn($calculator);

        return new HealthController(
            $connection,
            $factory,
            $guard ?? new DemoConfigurationGuard(new BasicAuthGate(false, false, '', ''), false, 'null://null', '', '', ''),
            $resetStatus ?? new ResetStatus(sys_get_temp_dir() . '/demo-reset-status-absent-' . bin2hex(random_bytes(4))),
            $cache ?? new ArrayAdapter(),
        );
    }

    /** @return list<AvailableMigration> */
    private function migrations(int $count): array
    {
        $migrations = [];
        for ($index = 1; $index <= $count; ++$index) {
            $migration = $this->createStub(AbstractMigration::class);
            $migrations[] = new AvailableMigration(new Version('Version' . $index), $migration);
        }

        return $migrations;
    }
}
