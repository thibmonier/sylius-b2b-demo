<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

/** The state of an order as the buyer says it: `$key` names a text (`order_state.<key>`), `$dueDate` fills the text "to pay before…". */
final readonly class AccountOrderState
{
    public function __construct(
        public string $key,
        public AccountTone $tone,
        public ?\DateTimeImmutable $dueDate = null,
    ) {
    }
}
