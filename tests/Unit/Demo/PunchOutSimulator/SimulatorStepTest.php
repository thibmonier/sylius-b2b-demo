<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\PunchOutSimulator;

use App\Demo\PunchOutSimulator\SimulatorStep;
use PHPUnit\Framework\TestCase;

/** US-099: the four numbered steps of the simulator (launch, shop, cart received, order); the page only ever shows launch, cart or order. */
final class SimulatorStepTest extends TestCase
{
    public function testTheSessionStartsAtTheLaunchStep(): void
    {
        self::assertSame(SimulatorStep::LAUNCH, SimulatorStep::of([]));
        self::assertSame(SimulatorStep::LAUNCH, SimulatorStep::of(['error' => ['key' => 'session_expired']]));
    }

    public function testAReceivedCartIsTheThirdStep(): void
    {
        self::assertSame(SimulatorStep::CART, SimulatorStep::of(['cart' => new \stdClass()]));
    }

    public function testASentOrderIsTheFourthStep(): void
    {
        self::assertSame(SimulatorStep::ORDER, SimulatorStep::of(['cart' => new \stdClass(), 'sent' => ['orderId' => 'PO-1']]));
    }

    public function testTheStepsAreNumberedFromOneToFour(): void
    {
        self::assertSame([1, 2, 3, 4], [SimulatorStep::LAUNCH, SimulatorStep::SHOP, SimulatorStep::CART, SimulatorStep::ORDER]);
    }

    public function testAnErrorOnACartKeepsTheCartStep(): void
    {
        self::assertSame(SimulatorStep::CART, SimulatorStep::of(['cart' => new \stdClass(), 'error' => ['key' => 'invalid_order_id']]));
    }
}
