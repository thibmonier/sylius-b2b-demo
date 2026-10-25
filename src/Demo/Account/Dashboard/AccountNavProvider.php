<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\MatcherInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Member\CurrentMemberResolverInterface;
use Tmonier\SyliusPurchaseApprovalPlugin\Approval\DecidableApprovalCounter;

/** The menu of the account of the shop (`sylius_shop.account`, fed by the core and the plugins), grouped, with the approvals waiting for the member. */
final class AccountNavProvider
{
    private const MENU = 'sylius_shop.account';

    public function __construct(
        private readonly MenuProviderInterface $menus,
        private readonly MatcherInterface $matcher,
        private readonly AccountNavGrouper $grouper,
        private readonly CurrentMemberResolverInterface $members,
        private readonly DecidableApprovalCounter $decidable,
    ) {
    }

    public function provide(): AccountNav
    {
        $menu = $this->menus->get(self::MENU);
        $current = $this->currentName($menu);
        $home = $menu->getChild('dashboard');

        return new AccountNav(
            (string) $home?->getUri(),
            'dashboard' === $current,
            $this->grouper->group($menu, ['tmonier_approvals' => (int) $this->decidable->count($this->members->getMember())], $current),
        );
    }

    private function currentName(ItemInterface $menu): ?string
    {
        foreach ($menu->getChildren() as $name => $child) {
            if ($this->matcher->isCurrent($child)) {
                return (string) $name;
            }
        }

        return null;
    }
}
