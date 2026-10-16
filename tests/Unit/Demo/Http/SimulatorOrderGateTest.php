<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Http;

use App\Demo\PunchOutSimulator\SimulatorOrderGate;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Clock\MockClock;

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
        $gate = new SimulatorOrderGate(new ArrayAdapter(), $clock, 1);

        self::assertSame(SimulatorOrderGate::CLAIMED, $gate->claim('a'));
        self::assertSame(SimulatorOrderGate::CAP_REACHED, $gate->claim('b'));
        $clock->modify('+1 day');

        self::assertSame(SimulatorOrderGate::CLAIMED, $gate->claim('b'));
    }

    private function gate(int $limit): SimulatorOrderGate
    {
        return new SimulatorOrderGate(new ArrayAdapter(), new MockClock('2026-10-16 10:00:00'), $limit);
    }
}
