<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

/** A piece of the account page, already in words of the buyer (formatted amounts and dates, translated texts). */
final readonly class AccountTodo
{
    public function __construct(
        public AccountTone $tone,
        public string $icon,
        public string $title,
        public string $text,
        public string $linkLabel,
        public string $url,
    ) {
    }
}
