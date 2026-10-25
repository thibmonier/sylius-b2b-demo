<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

/** A piece of the account page, already in words of the buyer (formatted amounts and dates, translated texts). */
final readonly class AccountOrderRow
{
    public function __construct(
        public string $number,
        public string $url,
        public string $date,
        public string $state,
        public AccountTone $tone,
        public string $total,
        public string $reorderUrl,
    ) {
    }
}
