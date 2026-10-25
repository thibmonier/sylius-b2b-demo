<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Account;

use App\Demo\Account\Dashboard\AccountNavGrouper;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;
use PHPUnit\Framework\TestCase;

/** US-199: the entries of the account menu (core, plugins, demo) are grouped in four blocks, in a fixed order, from what the menu really holds. */
final class AccountNavGrouperTest extends TestCase
{
    public function testEntriesAreGroupedInTheOrderOfTheMockUp(): void
    {
        $groups = (new AccountNavGrouper())->group($this->menu(['tmonier_equipment', 'order_history', 'personal_information', 'tmonier_company', 'tmonier_b2b_mcp', 'tmonier_quote_requests']));

        self::assertSame(['purchases', 'company', 'profile', 'tools'], array_map(static fn ($group): string => $group->key, $groups));
        self::assertSame(['order_history', 'tmonier_quote_requests'], $this->names($groups[0]->items));
        self::assertSame(['tmonier_company', 'tmonier_equipment'], $this->names($groups[1]->items));
        self::assertSame(['personal_information'], $this->names($groups[2]->items));
        self::assertSame(['tmonier_b2b_mcp'], $this->names($groups[3]->items));
    }

    public function testEntriesFollowTheOrderOfTheirGroupNotTheOrderOfTheMenu(): void
    {
        $groups = (new AccountNavGrouper())->group($this->menu(['wishlists', 'app_company_wishlists', 'tmonier_quote_requests', 'tmonier_quick_order', 'order_history']));

        self::assertSame(['order_history', 'tmonier_quick_order', 'tmonier_quote_requests', 'app_company_wishlists', 'wishlists'], $this->names($groups[0]->items));
    }

    public function testTheDashboardIsNotAnEntryAndAnEmptyGroupDisappears(): void
    {
        $groups = (new AccountNavGrouper())->group($this->menu(['dashboard', 'order_history']));

        self::assertSame(['purchases'], array_map(static fn ($group): string => $group->key, $groups));
        self::assertSame(['order_history'], $this->names($groups[0]->items));
    }

    public function testAnEntryAddedByAFuturePluginLandsInTools(): void
    {
        $groups = (new AccountNavGrouper())->group($this->menu(['order_history', 'some_new_plugin']));

        self::assertSame('tools', $groups[1]->key);
        self::assertSame(['some_new_plugin'], $this->names($groups[1]->items));
    }

    public function testACountIsCarriedByTheEntryItIsGivenFor(): void
    {
        $groups = (new AccountNavGrouper())->group($this->menu(['tmonier_approvals', 'tmonier_purchase_limits']), ['tmonier_approvals' => 3, 'tmonier_purchase_limits' => 0]);

        self::assertSame(3, $groups[0]->items[0]->count);
        self::assertNull($groups[0]->items[1]->count, 'a zero is not shown');
    }

    /** @param list<string> $names */
    private function menu(array $names): ItemInterface
    {
        $menu = (new MenuFactory())->createItem('root');
        foreach ($names as $name) {
            $menu->addChild($name, ['uri' => '/' . $name])->setLabel('label.' . $name)->setLabelAttribute('icon', 'tabler:circle');
        }

        return $menu;
    }

    /**
     * @param list<\App\Demo\Account\Dashboard\AccountNavItem> $items
     *
     * @return list<string>
     */
    private function names(array $items): array
    {
        return array_map(static fn ($item): string => $item->name, $items);
    }
}
