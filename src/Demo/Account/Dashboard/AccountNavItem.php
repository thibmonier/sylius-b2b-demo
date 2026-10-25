<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

/** One entry of the grouped menu of the account: `$name` is the name of the item in the Sylius menu, `$count` a number to show (never zero). */
final readonly class AccountNavItem
{
    public function __construct(
        public string $name,
        public string $label,
        public string $uri,
        public ?string $icon = null,
        public ?int $count = null,
        public bool $current = false,
    ) {
    }
}
