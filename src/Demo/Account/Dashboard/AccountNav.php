<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

/** The grouped menu of the account: the link to the home of the account, then the blocks. */
final readonly class AccountNav
{
    /** @param list<AccountNavGroup> $groups */
    public function __construct(
        public string $homeUri,
        public bool $homeCurrent,
        public array $groups,
    ) {
    }
}
