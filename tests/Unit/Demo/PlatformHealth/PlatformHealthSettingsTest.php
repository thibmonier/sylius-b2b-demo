<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\PlatformHealth;

use App\Tests\Support\Typed;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * T-TECH-58-03: the demo declares to the platform health plugin (v0.4) what it runs. Every command of the crontab has
 * a heartbeat at the rhythm of its cron, the asynchronous transport keeps its failed messages, the nightly reset keeps
 * the heartbeats, and the tables that a purge keeps small are watched.
 */
final class PlatformHealthSettingsTest extends TestCase
{
    private const HEARTBEAT_TABLE = 'tmonier_platform_health_heartbeat';

    public function testEveryCommandOfTheCrontabHasAHeartbeatAtTheRhythmOfItsFastestCron(): void
    {
        self::assertSame($this->crontabRhythms(), $this->heartbeats());
    }

    public function testTheCrontabRunsSixteenCommands(): void
    {
        self::assertCount(16, $this->crontabRhythms());
    }

    public function testTheAsyncTransportSendsItsFailedMessagesToADoctrineFailureTransport(): void
    {
        $transports = Typed::map(Typed::at($this->yaml('config/packages/messenger.yaml'), 'framework', 'messenger', 'transports'), 'transports');

        $async = $transports['async'] ?? null;
        self::assertIsArray($async, 'async must declare its failure transport.');
        self::assertSame('failed', $async['failure_transport'] ?? null);
        self::assertSame('doctrine://default?auto_setup=0', $async['dsn'] ?? null);
        // Same table as async (created by a migration, auto_setup=0): the failed messages are its rows of queue "failed".
        self::assertSame('doctrine://default?queue_name=failed&auto_setup=0', $transports['failed'] ?? null);
    }

    public function testTheTestsKeepBothTransportsInMemory(): void
    {
        $transports = Typed::map(Typed::at($this->yaml('config/packages/messenger.yaml'), 'when@test', 'framework', 'messenger', 'transports'), 'test transports');

        self::assertSame(['async' => 'in-memory://', 'failed' => 'in-memory://'], $transports);
    }

    public function testTheNightlyResetKeepsTheHeartbeats(): void
    {
        $purger = Typed::find($this->yaml('config/packages/demo_fixtures.yaml'), 'sylius_fixtures', 'suites', 'demo', 'listeners', 'orm_purger');

        self::assertSame([self::HEARTBEAT_TABLE], Typed::find($purger, 'exclude'));
    }

    public function testTheTablesKeptSmallByAPurgeAreWatched(): void
    {
        $tables = Typed::find($this->settings(), 'data', 'table_growth');

        self::assertIsArray($tables, 'No table is watched.');
        self::assertSame(['tmonier_audit_log_entry', 'tmonier_webhook_delivery', 'tmonier_punchout_message_log'], array_keys($tables));
        foreach ($tables as $table => $threshold) {
            self::assertIsInt($threshold, $table);
            self::assertGreaterThan(0, $threshold, $table);
        }
    }

    /** @return array<string, string> command => rhythm, in the order of the crontab */
    private function crontabRhythms(): array
    {
        $rhythms = [];
        foreach (file($this->path('docker/crontab'), \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            if (str_starts_with($line, '#') || 1 === preg_match('#^[A-Z_]+=#', $line)) {
                continue;
            }
            self::assertSame(1, preg_match('#^(\S+ \S+ \S+ \S+ \S+) .*/app/bin/console (\S+)#', $line, $match), 'Unreadable cron line: ' . $line);
            $rhythm = $this->rhythm($match[1]);
            $command = $match[2];
            if (!isset($rhythms[$command]) || $this->seconds($rhythm) < $this->seconds($rhythms[$command])) {
                $rhythms[$command] = $rhythm;
            }
        }

        return $rhythms;
    }

    private function rhythm(string $schedule): string
    {
        if ('* * * * *' === $schedule) {
            return '1m';
        }
        if (1 === preg_match('#^\*/(\d+) \* \* \* \*$#', $schedule, $match)) {
            return $match[1] . 'm';
        }
        self::assertSame(1, preg_match('#^\d+ \d+ \* \* \*$#', $schedule), 'Schedule without a known rhythm: ' . $schedule);

        return '1d';
    }

    private function seconds(string $rhythm): int
    {
        return (int) $rhythm * ['m' => 60, 'd' => 86400][substr($rhythm, -1)];
    }

    /** @return array<string, string> */
    private function heartbeats(): array
    {
        $heartbeats = Typed::find($this->settings(), 'scheduled_tasks', 'heartbeats');
        self::assertIsArray($heartbeats, 'No command has a heartbeat.');

        return array_map(static fn (mixed $rhythm): string => Typed::string($rhythm, 'rhythm'), Typed::map($heartbeats, 'heartbeats'));
    }

    private function settings(): mixed
    {
        return Typed::at($this->yaml('config/packages/tmonier_platform_health.yaml'), 'tmonier_sylius_platform_health');
    }

    private function yaml(string $file): mixed
    {
        return Yaml::parseFile($this->path($file));
    }

    private function path(string $file): string
    {
        return \dirname(__DIR__, 4) . '/' . $file;
    }
}
