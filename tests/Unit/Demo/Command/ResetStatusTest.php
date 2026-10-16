<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Command;

use App\Demo\Command\ResetStatus;
use PHPUnit\Framework\TestCase;

/** US-033: the last failed nightly reset stays visible (to /healthz, hence to an uptime monitor) until a reset succeeds. */
final class ResetStatusTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = sys_get_temp_dir() . '/demo-reset-status-' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        @unlink($this->file);
    }

    public function testNoFailureIsRecordedAtFirst(): void
    {
        self::assertFalse(new ResetStatus($this->file)->hasFailed());
    }

    public function testAFailureIsRecordedUntilAResetSucceeds(): void
    {
        $status = new ResetStatus($this->file);

        $status->markFailed();
        self::assertTrue(new ResetStatus($this->file)->hasFailed(), 'Another process (the web server) sees it.');

        $status->markSucceeded();
        self::assertFalse(new ResetStatus($this->file)->hasFailed());
    }

    public function testASuccessWithoutPreviousFailureIsHarmless(): void
    {
        $status = new ResetStatus($this->file);

        $status->markSucceeded();

        self::assertFalse($status->hasFailed());
    }
}
