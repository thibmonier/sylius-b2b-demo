<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Order\DemoOrderPlan;
use App\Demo\Order\DemoOrderSpec;
use Doctrine\DBAL\ArrayParameterType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * T-TECH-31-03 (action 3 of the retrospective of the sprint 30): the amounts of the demo are never half excluding and
 * half including VAT. Every order of the history is without VAT (the purchasing dashboard counts total minus tax: it
 * stays excluding VAT), the recent orders (carts of the fixtures) carry their VAT.
 */
final class OrderHistoryTaxConsistencyTest extends KernelTestCase
{
    use DatabaseAccess;

    public function testNoOrderOfTheHistoryCarriesATaxAdjustment(): void
    {
        self::bootKernel();

        $taxed = $this->countRows(
            'SELECT COUNT(DISTINCT o.id) FROM sylius_order o JOIN sylius_adjustment a ON a.order_id = o.id '
            . "WHERE o.po_reference IN (:references) AND a.type = 'tax'",
            $this->historyReferences(),
        );

        self::assertSame(0, $taxed, 'An order of the history carries a tax adjustment: the dashboard would mix amounts with and without VAT.');
    }

    public function testTheTotalOfAnOrderOfTheHistoryIsItsItemsPlusItsShipping(): void
    {
        self::bootKernel();

        $inconsistent = $this->countRows(
            'SELECT COUNT(*) FROM sylius_order WHERE po_reference IN (:references) AND total <> items_total + adjustments_total',
            $this->historyReferences(),
        );

        self::assertSame(0, $inconsistent);
    }

    public function testTheOrdersOutsideTheHistoryCarryTheirVat(): void
    {
        self::bootKernel();

        $recent = $this->countRows(
            "SELECT COUNT(*) FROM sylius_order WHERE state = 'cart' AND items_total > 0 AND (po_reference IS NULL OR po_reference NOT IN (:references))",
            $this->historyReferences(),
        );
        $withoutVat = $this->countRows(
            'SELECT COUNT(*) FROM sylius_order o WHERE o.state = \'cart\' AND o.items_total > 0 AND (o.po_reference IS NULL OR o.po_reference NOT IN (:references)) '
            . "AND NOT EXISTS (SELECT 1 FROM sylius_adjustment a WHERE a.order_id = o.id AND a.type = 'tax')",
            $this->historyReferences(),
        );

        self::assertGreaterThan(0, $recent, 'The fixtures leave recent carts.');
        self::assertSame(0, $withoutVat, 'A recent order has no VAT.');
    }

    /** @return list<string> */
    private function historyReferences(): array
    {
        return array_map(static fn (DemoOrderSpec $spec): string => $spec->poReference, DemoOrderPlan::all($this->demoResetDate()));
    }

    /** @param list<string> $references */
    private function countRows(string $sql, array $references): int
    {
        $count = $this->db()->fetchOne($sql, ['references' => $references], ['references' => ArrayParameterType::STRING]);

        return (int) (is_numeric($count) ? $count : -1);
    }
}
