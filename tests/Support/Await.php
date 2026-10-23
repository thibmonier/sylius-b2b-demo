<?php

declare(strict_types=1);

namespace App\Tests\Support;

use PHPUnit\Framework\Assert;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Clock\NativeClock;

/**
 * Waits for a condition with a deadline instead of sleeping a fixed time (US-166). The clock is injectable: a test
 * of the helper passes a MockClock, whose sleep() moves the time forward without waiting.
 */
final class Await
{
    private const POLL_SECONDS = 0.05;

    /**
     * @param callable(): bool $condition
     */
    public static function until(callable $condition, float $timeoutSeconds, string $failure, ?ClockInterface $clock = null): void
    {
        $clock ??= new NativeClock();
        $deadline = self::seconds($clock) + $timeoutSeconds;

        while (!$condition()) {
            if (self::seconds($clock) >= $deadline) {
                Assert::fail($failure);
            }
            $clock->sleep(self::POLL_SECONDS);
        }
    }

    public static function listening(string $host, int $port, float $timeoutSeconds, ?ClockInterface $clock = null): void
    {
        self::until(
            static function () use ($host, $port): bool {
                $socket = @fsockopen($host, $port);
                if (false === $socket) {
                    return false;
                }
                fclose($socket);

                return true;
            },
            $timeoutSeconds,
            \sprintf('Nothing listens on %s:%d after %s seconds.', $host, $port, $timeoutSeconds),
            $clock,
        );
    }

    private static function seconds(ClockInterface $clock): float
    {
        return (float) $clock->now()->format('U.u');
    }
}
