<?php

declare(strict_types=1);

namespace App\Tests\Unit\Docker;

use PHPUnit\Framework\TestCase;

/**
 * US-195: a deployment starts a new container, whose exchange directory is empty while the database is not: the entrypoint rebuilds the
 * exchange after the migrations and the fixtures, as www-data (it comes after the `setpriv`), before the scheduled tasks, and a failure
 * never stops the start.
 */
final class ErpRefreshEntrypointTest extends TestCase
{
    private string $entrypoint;

    protected function setUp(): void
    {
        $this->entrypoint = (string) file_get_contents(__DIR__ . '/../../../docker/entrypoint.sh');
    }

    public function testTheRefreshRunsAfterTheMigrationsAndTheFixturesBeforeTheScheduledTasks(): void
    {
        $refresh = strpos($this->entrypoint, 'app:demo:erp-refresh');

        self::assertNotFalse($refresh);
        self::assertGreaterThan((int) strpos($this->entrypoint, 'doctrine:migrations:migrate'), $refresh);
        self::assertGreaterThan((int) strpos($this->entrypoint, 'sylius:fixtures:load demo'), $refresh);
        self::assertGreaterThan((int) strpos($this->entrypoint, 'setpriv --reuid=www-data'), $refresh, 'as www-data, who owns the directory');
        self::assertLessThan((int) strpos($this->entrypoint, 'supercronic'), $refresh);
    }

    public function testAFailureOfTheRefreshNeverStopsTheStart(): void
    {
        self::assertMatchesRegularExpression('/app:demo:erp-refresh[^\n]*\|\|/', $this->entrypoint, '`set -e` is on: the command is followed by `||`');
    }

    public function testTheDirectoryIsNotOnTheVolumeOfTheMedia(): void
    {
        $config = (string) file_get_contents(__DIR__ . '/../../../config/packages/tmonier_erp_flatfile.yaml');

        self::assertStringContainsString("exchange_directory: '%kernel.project_dir%/var/erp-exchange'", $config);
        self::assertStringNotContainsString('media', $config);
    }
}
