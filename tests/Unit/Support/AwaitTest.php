<?php

declare(strict_types=1);

namespace App\Tests\Unit\Support;

use App\Tests\Support\Await;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class AwaitTest extends TestCase
{
    public function testItReturnsAtOnceWhenTheConditionAlreadyHolds(): void
    {
        $clock = new MockClock('2026-01-01 00:00:00');

        Await::until(static fn (): bool => true, 5, 'never', $clock);

        self::assertSame('2026-01-01 00:00:00', $clock->now()->format('Y-m-d H:i:s'));
    }

    public function testItPollsUntilTheConditionHoldsWithoutRealWaiting(): void
    {
        $clock = new MockClock('2026-01-01 00:00:00');
        $calls = 0;
        $startedAt = microtime(true);

        Await::until(static function () use (&$calls): bool {
            return ++$calls >= 4;
        }, 5, 'never', $clock);

        self::assertSame(4, $calls);
        self::assertSame('00:00:00.150000', $clock->now()->format('H:i:s.u'), 'three polls of 50 ms on the mock clock');
        self::assertLessThan(1.0, microtime(true) - $startedAt);
    }

    public function testItFailsWithTheGivenMessageOnceTheDeadlineIsPassed(): void
    {
        $clock = new MockClock('2026-01-01 00:00:00');

        try {
            Await::until(static fn (): bool => false, 1, 'the server never started', $clock);
            self::fail('The wait must give up.');
        } catch (AssertionFailedError $e) {
            self::assertSame('the server never started', $e->getMessage());
        }

        self::assertSame('00:00:01', $clock->now()->format('H:i:s'));
    }

    public function testListeningFailsOnAPortNobodyListensOn(): void
    {
        $probe = stream_socket_server('tcp://127.0.0.1:0');
        self::assertNotFalse($probe);
        $name = (string) stream_socket_get_name($probe, false);
        fclose($probe);

        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Nothing listens on 127.0.0.1:');

        Await::listening('127.0.0.1', (int) substr($name, (int) strrpos($name, ':') + 1), 0.2, new MockClock());
    }

    public function testListeningReturnsOnceTheServerAccepts(): void
    {
        $server = stream_socket_server('tcp://127.0.0.1:0');
        self::assertNotFalse($server);
        $name = (string) stream_socket_get_name($server, false);

        try {
            Await::listening('127.0.0.1', (int) substr($name, (int) strrpos($name, ':') + 1), 1, new MockClock());
            $this->addToAssertionCount(1);
        } finally {
            fclose($server);
        }
    }
}
