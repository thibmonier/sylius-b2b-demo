<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

/**
 * Single source of the PunchOut buyer codes of the demo: the fixture creates the buyers under these codes, the
 * simulator looks them up by the same codes (the simulator never depends on the fixture).
 */
final class DemoBuyerCodes
{
    public const CXML = 'acme-punchout';

    public const OCI = 'acme-punchout-oci';
}
