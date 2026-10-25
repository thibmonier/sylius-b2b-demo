<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

/** A piece of the account page, already in words of the buyer (formatted amounts and dates, translated texts). */
final readonly class AccountCredit
{
    public function __construct(
        public string $available,
        public string $limit,
        public int $usedPercent,
        public string $note,
        public AccountTone $noteTone,
        public string $statementUrl,
    ) {
    }
}
