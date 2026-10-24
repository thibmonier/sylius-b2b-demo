<?php

declare(strict_types=1);

namespace App\Demo\Einvoice;

/**
 * US-183: which scenario each invoiced order of the demo plays, by purchase order reference. The data is fixed here, so that the
 * journal is the same after every nightly reset (ADR 0007). Any other order is deposited.
 */
final class DemoTransmissionScenarioPlan
{
    /** @return array<string, DemoTransmissionScenario> */
    public static function all(): array
    {
        return [
            'ACME-PO-2026-0412' => DemoTransmissionScenario::Deposited,
            'ACME-PO-2026-0458' => DemoTransmissionScenario::Rejected,
            'ACME-PO-2026-0503' => DemoTransmissionScenario::Refused,
            'ACME-PO-2026-0591' => DemoTransmissionScenario::Collected,
        ];
    }

    /**
     * US-184: orders whose first send meets an outage of the fictitious platform (status "error"), to show the relaunch. The
     * outage lasts until an administrator relaunches the send.
     *
     * @return list<string>
     */
    public static function outageOrders(): array
    {
        return ['TP-2026-142'];
    }

    public static function for(?string $poReference): DemoTransmissionScenario
    {
        return null === $poReference ? DemoTransmissionScenario::Deposited : self::all()[$poReference] ?? DemoTransmissionScenario::Deposited;
    }

    private function __construct()
    {
    }
}
