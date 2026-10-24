<?php

declare(strict_types=1);

namespace App\Demo\Einvoice;

/**
 * US-183: the simulated answers of the fictitious platform. Lifecycle statuses are not confirmed by a source read (ADR 0016,
 * "to be verified"): the demo plays four and says so on the page.
 */
enum DemoTransmissionScenario: string
{
    case Deposited = 'deposited';
    case Rejected = 'rejected';
    case Refused = 'refused';
    case Collected = 'collected';

    /** The reason stored with the status: simulated, readable, the same whatever the language of the page. */
    public function reason(): ?string
    {
        return match ($this) {
            self::Rejected => 'Simulated reason: the buyer address is not in the directory of the fictitious platform.',
            self::Refused => 'Simulated reason: the buyer refused the invoice (purchase order not matched).',
            self::Deposited, self::Collected => null,
        };
    }
}
