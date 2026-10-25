<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Order\DemoOrderPlan;
use App\Demo\Order\DemoOrderSpec;
use Doctrine\DBAL\ArrayParameterType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * US-202 (registry of the sprint 30): the order history of the demo stays without VAT, the 47 written in bulk and the ones built through
 * the checkout alike, so that the amounts of the purchasing dashboard do not change silently. The products carry a tax category now:
 * the history is built without the tax processor (and without its lookups).
 */
final class OrderHistoryWithoutTaxTest extends KernelTestCase
{
    use DatabaseAccess;

    private const SHIPPING = 1500;

    public function testNoOrderOfTheHistoryCarriesAnyTax(): void
    {
        self::bootKernel();
        $references = array_map(static fn (DemoOrderSpec $spec): string => $spec->poReference, DemoOrderPlan::all($this->demoResetDate()));

        $withTax = $this->db()->fetchOne(
            'SELECT COUNT(*) FROM sylius_order WHERE po_reference IN (:references) AND adjustments_total <> :shipping',
            ['references' => $references, 'shipping' => self::SHIPPING],
            ['references' => ArrayParameterType::STRING],
        );

        self::assertSame(0, (int) (is_numeric($withTax) ? $withTax : -1), 'Only the shipping is an adjustment of the history orders.');
    }
}
