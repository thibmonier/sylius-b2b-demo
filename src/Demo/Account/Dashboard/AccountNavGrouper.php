<?php

declare(strict_types=1);

namespace App\Demo\Account\Dashboard;

use Knp\Menu\ItemInterface;

/**
 * Groups the entries of the account menu (the core's, the plugins' and the demo's) in four blocks. The menu stays the source: an
 * entry is here only if the menu holds it, so the rights of the account (approvers, administrators) and the features switched on
 * decide what is shown. An entry nobody listed yet lands in "tools".
 */
final class AccountNavGrouper
{
    private const DASHBOARD = 'dashboard';

    /** Group, then the entries of the group in the order they are shown. */
    private const GROUPS = [
        'purchases' => ['order_history', 'tmonier_quick_order', 'tmonier_quote_requests', 'app_company_wishlists', 'wishlists'],
        'company' => ['tmonier_company', 'tmonier_approvals', 'tmonier_purchase_limits', 'app_insights', 'tmonier_trade_credit', 'tmonier_equipment'],
        'profile' => ['personal_information', 'change_password', 'address_book'],
        'tools' => ['tmonier_b2b_mcp'],
    ];

    /**
     * @param array<string, int> $counts number to show by item name
     *
     * @return list<AccountNavGroup>
     */
    public function group(ItemInterface $menu, array $counts = [], ?string $currentName = null): array
    {
        $byGroup = array_fill_keys(array_keys(self::GROUPS), []);
        foreach ($menu->getChildren() as $name => $child) {
            $name = (string) $name;
            if (self::DASHBOARD === $name) {
                continue;
            }
            $group = $this->groupOf($name);
            $position = array_search($name, self::GROUPS[$group], true);
            $byGroup[$group][false === $position ? \count(self::GROUPS[$group]) : $position][] = $this->item($child, $name, $counts, $currentName);
        }
        $groups = [];
        foreach ($byGroup as $key => $positions) {
            if ([] === $positions) {
                continue;
            }
            ksort($positions);
            $groups[] = new AccountNavGroup($key, array_merge(...$positions));
        }

        return $groups;
    }

    private function groupOf(string $name): string
    {
        foreach (self::GROUPS as $group => $names) {
            if (\in_array($name, $names, true)) {
                return $group;
            }
        }

        return 'tools';
    }

    /** @param array<string, int> $counts */
    private function item(ItemInterface $child, string $name, array $counts, ?string $currentName): AccountNavItem
    {
        $icon = $child->getLabelAttribute('icon');
        $count = $counts[$name] ?? 0;

        return new AccountNavItem(
            $name,
            (string) $child->getLabel(),
            (string) $child->getUri(),
            \is_string($icon) ? $icon : null,
            $count > 0 ? $count : null,
            $name === $currentName,
        );
    }
}
