<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Order\DemoOrderPlan;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * US-155: the history written in bulk (BulkOrderWriter) leaves the rows the Sylius services leave, so the order pages
 * and the purchasing dashboard read it like any other order. Runs against the database loaded with the "demo" suite.
 */
final class OrderHistoryBulkTest extends KernelTestCase
{
    use DatabaseAccess;

    private const HISTORY = "po_reference LIKE '%-H-%'";

    public function testEveryHistoryOrderOfThePlanIsInTheDatabase(): void
    {
        $expected = \count(DemoOrderPlan::history($this->demoResetDate()));

        self::assertGreaterThanOrEqual(45, $expected);
        self::assertSame($expected, $this->intValue('SELECT COUNT(*) FROM sylius_order WHERE ' . self::HISTORY));
    }

    public function testEveryHistoryOrderBelongsToTheCompanyOfItsBuyer(): void
    {
        $unlinked = $this->intValue(
            'SELECT COUNT(*) FROM sylius_order o
             LEFT JOIN tmonier_company_order co ON co.order_id = o.id
             LEFT JOIN tmonier_company_member m ON m.customer_id = o.customer_id AND m.company_id = co.company_id
             WHERE o.' . self::HISTORY . ' AND m.id IS NULL',
        );

        self::assertSame(0, $unlinked);
    }

    public function testTotalsAddUp(): void
    {
        self::assertSame(0, $this->intValue(
            'SELECT COUNT(*) FROM sylius_order o WHERE o.' . self::HISTORY . '
             AND (o.total <> o.items_total + o.adjustments_total
                  OR o.items_total <> (SELECT COALESCE(SUM(i.total), 0) FROM sylius_order_item i WHERE i.order_id = o.id)
                  OR o.total <> (SELECT p.amount FROM sylius_payment p WHERE p.order_id = o.id))',
        ));
    }

    public function testOneUnitPerQuantityAndOneShippingAdjustmentPerOrder(): void
    {
        self::assertSame(0, $this->intValue(
            'SELECT COUNT(*) FROM sylius_order_item i JOIN sylius_order o ON o.id = i.order_id
             WHERE o.' . self::HISTORY . ' AND i.quantity <> (SELECT COUNT(*) FROM sylius_order_item_unit u WHERE u.order_item_id = i.id)',
        ));
        self::assertSame(0, $this->intValue(
            "SELECT COUNT(*) FROM sylius_order o WHERE o.po_reference LIKE '%-H-%'
             AND (SELECT COUNT(*) FROM sylius_adjustment a WHERE a.order_id = o.id AND a.type = 'shipping') <> 1",
        ));
    }

    public function testStatesFollowTheScenario(): void
    {
        self::assertSame(0, $this->intValue(
            "SELECT COUNT(*) FROM sylius_order o JOIN sylius_shipment s ON s.order_id = o.id JOIN sylius_payment p ON p.order_id = o.id
             WHERE o.po_reference LIKE '%-H-%' AND NOT (
                 (o.state = 'fulfilled' AND o.shipping_state = 'shipped' AND s.state = 'shipped' AND s.shipped_at IS NOT NULL)
                 OR (o.state = 'new' AND o.shipping_state = 'ready' AND s.state = 'ready' AND s.shipped_at IS NULL))
             OR (o.po_reference LIKE '%-H-%' AND (o.checkout_state <> 'completed' OR o.payment_state <> 'paid' OR p.state <> 'completed'))",
        ));
    }

    public function testNumbersAreUniqueAndTheSequenceIsAtTheLastOne(): void
    {
        self::assertSame($this->intValue('SELECT COUNT(*) FROM sylius_order WHERE number IS NOT NULL'), $this->intValue('SELECT COUNT(DISTINCT number) FROM sylius_order WHERE number IS NOT NULL'));
        self::assertSame($this->intValue('SELECT MAX(CAST(number AS UNSIGNED)) FROM sylius_order'), $this->intValue('SELECT idx FROM sylius_order_sequence'));
    }

    public function testTheHistoryNeverWaitsForAnApproval(): void
    {
        self::assertSame(0, $this->intValue('SELECT COUNT(*) FROM tmonier_purchase_approval_order_approval a JOIN sylius_order o ON o.id = a.order_id WHERE o.' . self::HISTORY));
    }
}
