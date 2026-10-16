<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Http;

use App\Demo\PunchOutSimulator\SimulatorOrderGate;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\InMemoryStore;

/** US-036 / T-036-03: one order per simulator session, and a daily cap of anonymous orders. */
final class SimulatorOrderGateTest extends TestCase
{
    public function testASessionCanBeClaimedOnceThenItIsAReplay(): void
    {
        $gate = $this->gate(10);

        self::assertSame(SimulatorOrderGate::CLAIMED, $gate->claim('nonce-1'));
        self::assertSame(SimulatorOrderGate::REPLAYED, $gate->claim('nonce-1'));
        self::assertSame(SimulatorOrderGate::CLAIMED, $gate->claim('nonce-2'));
    }

    public function testAReleasedSessionCanBeUsedAgainAndGivesBackItsPlaceInTheDay(): void
    {
        $gate = $this->gate(1);

        self::assertSame(SimulatorOrderGate::CLAIMED, $gate->claim('nonce-1'));
        $gate->release('nonce-1');

        self::assertSame(SimulatorOrderGate::CLAIMED, $gate->claim('nonce-1'));
    }

    public function testTheDailyCapRefusesFurtherOrders(): void
    {
        $gate = $this->gate(2);

        self::assertSame(SimulatorOrderGate::CLAIMED, $gate->claim('a'));
        self::assertSame(SimulatorOrderGate::CLAIMED, $gate->claim('b'));
        self::assertSame(SimulatorOrderGate::CAP_REACHED, $gate->claim('c'));
        self::assertSame(SimulatorOrderGate::CAP_REACHED, $gate->claim('c'), 'Still capped.');
    }

    public function testTheCapStartsAgainTheNextDay(): void
    {
        $clock = new MockClock('2026-10-16 10:00:00');
        $gate = new SimulatorOrderGate(new ArrayAdapter(), $clock, 1, new LockFactory(new InMemoryStore()));

        self::assertSame(SimulatorOrderGate::CLAIMED, $gate->claim('a'));
        self::assertSame(SimulatorOrderGate::CAP_REACHED, $gate->claim('b'));
        $clock->modify('+1 day');

        self::assertSame(SimulatorOrderGate::CLAIMED, $gate->claim('b'));
    }

    /** Sprint 8 review N1: parallel posts must not all read "not claimed", so the cache is only read under the lock. */
    public function testTheCacheIsReadAndWrittenUnderTheGateLock(): void
    {
        $locks = new LockFactory(new InMemoryStore());
        $cache = new class($locks) extends ArrayAdapter {
            /** @var list<bool> whether the gate lock was held at each cache read */
            public array $seen = [];

            public function __construct(private readonly LockFactory $locks)
            {
                parent::__construct();
            }

            public function getItem(mixed $key): CacheItem
            {
                $this->seen[] = SimulatorOrderGateTest::isHeld($this->locks);

                return parent::getItem($key);
            }
        };
        $gate = new SimulatorOrderGate($cache, new MockClock('2026-10-16 10:00:00'), 10, $locks);

        $gate->claim('nonce-1');
        $gate->release('nonce-1');

        self::assertNotEmpty($cache->seen);
        self::assertNotContains(false, $cache->seen, 'Every cache read happens while the gate lock is held.');
        self::assertFalse(self::isHeld($locks), 'The lock is released afterwards.');
    }

    /** Another holder (a parallel request) would fail to take the gate lock right now. */
    public static function isHeld(LockFactory $locks): bool
    {
        $probe = $locks->createLock('demo_punchout_order_gate');
        if (!$probe->acquire(false)) {
            return true;
        }
        $probe->release();

        return false;
    }

    private function gate(int $limit): SimulatorOrderGate
    {
        return new SimulatorOrderGate(new ArrayAdapter(), new MockClock('2026-10-16 10:00:00'), $limit, new LockFactory(new InMemoryStore()));
    }
}
