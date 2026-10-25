<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

/** Everything the page "My account" of a B2B buyer shows, read from the services of the shop and of the plugins; nothing is stored. */
final readonly class AccountDashboard
{
    /**
     * @param list<AccountTodo>     $todos
     * @param list<AccountOrderRow> $orders
     * @param list<AccountShortcut> $shortcuts
     * @param list<AccountFact>     $facts
     */
    public function __construct(
        public string $firstName,
        public ?string $roleLabel,
        public ?string $companyName,
        public string $email,
        public bool $verified,
        public string $todoTitle,
        public array $todos,
        public array $orders,
        public int $ordersTotal,
        public array $shortcuts,
        public array $facts,
        public ?AccountCredit $credit,
        public string $listsText,
        public string $quotesText,
    ) {
    }
}
