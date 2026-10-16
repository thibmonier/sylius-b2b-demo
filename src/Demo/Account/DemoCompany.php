<?php

declare(strict_types=1);

namespace App\Demo\Account;

/** A demo B2B company with its members. */
final readonly class DemoCompany
{
    /** @param list<DemoAccount> $members */
    public function __construct(
        public string $code,
        public string $name,
        public ?string $vatNumber,
        public ?string $registrationNumber,
        public array $members,
    ) {
    }
}
