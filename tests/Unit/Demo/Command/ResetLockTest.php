<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Command;

use App\Demo\Command\ResetLock;
use PHPUnit\Framework\TestCase;

/** US-049: two resets (cron and manual) never run together; a lock left by a dead process never blocks the next one. */
final class ResetLockTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = sys_get_temp_dir() . '/demo-reset-lock-' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        @unlink($this->file);
    }

    public function testTheSecondResetCannotStartWhileTheFirstOneRuns(): void
    {
        $first = new ResetLock($this->file);
        $second = new ResetLock($this->file);

        self::assertTrue($first->acquire());
        self::assertFalse($second->acquire(), 'Non blocking: it answers at once.');

        $first->release();
        self::assertTrue($second->acquire(), 'The lock is free again once the first reset is over.');
        $second->release();
    }

    public function testALockFileLeftByADeadProcessDoesNotBlock(): void
    {
        file_put_contents($this->file, 'left by a killed process');

        $lock = new ResetLock($this->file);

        self::assertTrue($lock->acquire());
        $lock->release();
    }

    public function testTheLockDisappearsWithTheProcessThatHoldsIt(): void
    {
        $script = $this->file . '.php';
        file_put_contents($script, "<?php\nrequire " . var_export(\dirname(__DIR__, 4) . '/vendor/autoload.php', true) . ";\nvar_dump((new App\\Demo\\Command\\ResetLock(" . var_export($this->file, true) . "))->acquire());\n");
        exec(escapeshellarg(\PHP_BINARY) . ' ' . escapeshellarg($script), $output);
        @unlink($script);

        self::assertSame('bool(true)', $output[0]);
        $lock = new ResetLock($this->file);
        self::assertTrue($lock->acquire(), 'The holder exited without releasing: flock went with it.');
        $lock->release();
    }
}
