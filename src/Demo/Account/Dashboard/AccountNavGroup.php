<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

/** A block of the grouped menu; `$key` is also the key of its title in the translations (`nav.group.<key>`). */
final readonly class AccountNavGroup
{
    /** @param list<AccountNavItem> $items */
    public function __construct(
        public string $key,
        public array $items,
    ) {
    }
}
