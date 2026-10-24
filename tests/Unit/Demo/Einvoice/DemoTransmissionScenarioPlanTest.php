<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Einvoice;

use App\Demo\Einvoice\DemoTransmissionScenario;
use App\Demo\Einvoice\DemoTransmissionScenarioPlan;
use App\Demo\Order\DemoOrderPlan;
use PHPUnit\Framework\TestCase;

/** US-183: the scenario of each order is fixed by the demo data, so that it is the same after every nightly reset. */
final class DemoTransmissionScenarioPlanTest extends TestCase
{
    public function testEveryScenarioIsPlayedByAtLeastOneOrder(): void
    {
        $played = array_unique(array_map(static fn (DemoTransmissionScenario $scenario): string => $scenario->value, DemoTransmissionScenarioPlan::all()));
        sort($played);

        self::assertSame(['collected', 'deposited', 'refused', 'rejected'], $played);
    }

    public function testEveryPlannedOrderIsACoreOrderOfTheDemo(): void
    {
        $core = array_map(static fn ($spec): string => $spec->poReference, DemoOrderPlan::core());

        foreach (array_keys(DemoTransmissionScenarioPlan::all()) as $poReference) {
            self::assertContains($poReference, $core, 'the history written in bulk has no invoice');
        }
    }

    public function testTheOrderWaitingForItsApprovalHasNoScenarioBecauseItHasNoInvoice(): void
    {
        self::assertArrayNotHasKey('ACME-PO-2026-0634', DemoTransmissionScenarioPlan::all());
    }

    public function testAnOrderOutsideThePlanIsDeposited(): void
    {
        self::assertSame(DemoTransmissionScenario::Deposited, DemoTransmissionScenarioPlan::for('NOT-IN-THE-PLAN'));
        self::assertSame(DemoTransmissionScenario::Deposited, DemoTransmissionScenarioPlan::for(null));
    }

    public function testTheRejectionAndTheRefusalCarryASimulatedReasonAndTheOthersNone(): void
    {
        foreach (DemoTransmissionScenario::cases() as $scenario) {
            $reason = $scenario->reason();
            if (\in_array($scenario, [DemoTransmissionScenario::Rejected, DemoTransmissionScenario::Refused], true)) {
                self::assertNotNull($reason);
                self::assertStringStartsWith('Simulated', $reason);
            } else {
                self::assertNull($reason);
            }
        }
    }
}
