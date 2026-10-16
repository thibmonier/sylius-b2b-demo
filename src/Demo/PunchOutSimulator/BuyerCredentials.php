<?php

declare(strict_types=1);

namespace App\Demo\PunchOutSimulator;

/** The cXML credentials the simulated procurement tool presents (identities of the buyer, shared secret). */
final readonly class BuyerCredentials
{
    public function __construct(
        public string $fromDomain,
        public string $fromIdentity,
        public string $senderDomain,
        public string $senderIdentity,
        #[\SensitiveParameter]
        public string $sharedSecret,
    ) {
    }
}
