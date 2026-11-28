<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\PlatformHealth;

use App\Demo\Command\ResetStatus;
use App\Demo\PlatformHealth\DemoResetCheck;
use PHPUnit\Framework\TestCase;
use Tmonier\SyliusPlatformHealthPlugin\Health\HealthCategory;
use Tmonier\SyliusPlatformHealthPlugin\Health\HealthStatus;

/** US-321: the last nightly reset is a probe of the platform health plugin; critical while it failed, until the next success. */
final class DemoResetCheckTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = sys_get_temp_dir() . '/demo-reset-check-' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        @unlink($this->file);
        @unlink($this->file . '.last-success');
    }

    public function testItIsIdentifiedAndFiledUnderTheEnvironment(): void
    {
        $check = new DemoResetCheck(new ResetStatus($this->file));

        self::assertSame('demo.reset', $check->identifier());
        self::assertSame(HealthCategory::Environment, $check->category());
    }

    public function testWithoutAnyMarkerItIsOk(): void
    {
        self::assertSame(HealthStatus::Ok, new DemoResetCheck(new ResetStatus($this->file))->run()->status);
    }

    public function testAResetInProgressIsOk(): void
    {
        file_put_contents($this->file, 'running ' . date(\DATE_ATOM));

        self::assertSame(HealthStatus::Ok, new DemoResetCheck(new ResetStatus($this->file))->run()->status);
    }

    public function testAFailedResetIsCriticalUntilTheNextSuccess(): void
    {
        $status = new ResetStatus($this->file);
        $status->markFailed();
        $check = new DemoResetCheck($status);

        self::assertSame(HealthStatus::Critical, $check->run()->status);

        $status->markSucceeded();
        self::assertSame(HealthStatus::Ok, $check->run()->status);
    }

    public function testAResetThatDiedWithoutAWordIsCriticalAfterTheGraceDelay(): void
    {
        file_put_contents($this->file, 'running ' . date(\DATE_ATOM));
        touch($this->file, time() - ResetStatus::MAX_RUN_SECONDS - 60);

        self::assertSame(HealthStatus::Critical, new DemoResetCheck(new ResetStatus($this->file))->run()->status);
    }
}
