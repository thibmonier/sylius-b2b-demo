<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

/** A piece of the account page, already in words of the buyer (formatted amounts and dates, translated texts). */
final readonly class AccountShortcut
{
    public function __construct(
        public string $title,
        public string $text,
        public string $url,
    ) {
    }
}
