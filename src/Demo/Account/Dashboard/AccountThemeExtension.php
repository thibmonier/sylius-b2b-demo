<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/** Data of the B2B account pages (US-199): the home of the account and the grouped menu. */
final class AccountThemeExtension extends AbstractExtension
{
    public function __construct(
        private readonly AccountDashboardProvider $dashboard,
        private readonly AccountNavProvider $nav,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('app_b2b_account_dashboard', fn (): ?AccountDashboard => $this->dashboard->provide()),
            new TwigFunction('app_b2b_account_nav', fn (): AccountNav => $this->nav->provide()),
        ];
    }
}
