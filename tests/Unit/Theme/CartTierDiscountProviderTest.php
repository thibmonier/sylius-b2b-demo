<?php

declare(strict_types=1);

namespace App\Tests\Unit\Theme;

use App\Demo\Theme\CartTierDiscountProvider;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\Adjustment;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderItem;
use Sylius\Component\Core\Model\OrderItemUnit;
use Sylius\Component\Customer\Model\CustomerGroup;
use Tmonier\SyliusTierPricingPlugin\Entity\TierPriceRule;
use Tmonier\SyliusTierPricingPlugin\Entity\TierPriceRuleInterface;
use Tmonier\SyliusTierPricingPlugin\OrderProcessing\TierPriceOrderProcessor;

/**
 * US-091: the discount line of the cart, split between public tiers and the company price.
 */
final class CartTierDiscountProviderTest extends TestCase
{
    public function testAnEmptyCartHasNoDiscount(): void
    {
        self::assertNull($this->provider([])->forOrder(new Order()));
    }

    public function testALineWithoutTierHasNoDiscount(): void
    {
        $order = new Order();
        $order->addItem($this->item(1850, 5));

        self::assertNull($this->provider([])->forOrder($order));
    }

    public function testThePublicTierDiscountIsTheGapTimesTheQuantity(): void
    {
        $order = new Order();
        $order->addItem($this->item(1757, 10, withoutTier: 1850, ruleId: 1));

        $discount = $this->provider([1 => $this->rule(false)])->forOrder($order);

        self::assertNotNull($discount);
        self::assertSame(930, $discount->tierAmount);
        self::assertSame(0, $discount->negotiatedAmount);
    }

    public function testTheCompanyPriceIsCountedApart(): void
    {
        $order = new Order();
        $order->addItem($this->item(1757, 10, withoutTier: 1850, ruleId: 1));
        $negotiated = $this->item(1628, 2, withoutTier: 1850, ruleId: 2);
        $order->addItem($negotiated);

        $provider = $this->provider([1 => $this->rule(false), 2 => $this->rule(true)]);
        $discount = $provider->forOrder($order);

        self::assertNotNull($discount);
        self::assertSame(930, $discount->tierAmount);
        self::assertSame(444, $discount->negotiatedAmount);
        self::assertSame(1374, $discount->total());
        self::assertTrue($provider->isNegotiated($negotiated));
    }

    public function testALineSavesTheGapToTheCataloguePriceAndItsRate(): void
    {
        $item = $this->item(1663, 10, withoutTier: 1890, ruleId: 2);

        $saving = $this->provider([2 => $this->rule(true)])->forItem($item);

        self::assertNotNull($saving);
        self::assertSame(1890, $saving->catalogueUnitPrice);
        self::assertSame(2270, $saving->amount, '10 pieces at 2,27 € less.');
        self::assertSame(12.0, $saving->percent);
        self::assertTrue($saving->negotiated);
    }

    public function testALineWithoutTierOrWithoutGainHasNoSaving(): void
    {
        $provider = $this->provider([]);

        self::assertNull($provider->forItem($this->item(1850, 5)), 'No tier marker.');
        self::assertNull($provider->forItem($this->item(1850, 5, withoutTier: 1850, ruleId: 1)), 'Same price as the catalogue.');
    }

    private function item(int $unitPrice, int $quantity, ?int $withoutTier = null, ?int $ruleId = null): OrderItem
    {
        $item = new OrderItem();
        $item->setUnitPrice($unitPrice);
        for ($i = 0; $i < $quantity; ++$i) {
            new OrderItemUnit($item);
        }
        if (null !== $withoutTier) {
            $marker = new Adjustment();
            $marker->setType(TierPriceOrderProcessor::ADJUSTMENT_TYPE);
            $marker->setNeutral(true);
            $marker->setDetails(['ruleId' => $ruleId, 'priceWithoutTier' => $withoutTier]);
            $item->addAdjustment($marker);
        }

        return $item;
    }

    private function rule(bool $forAGroup): TierPriceRuleInterface
    {
        $rule = new TierPriceRule();
        $rule->setCustomerGroup($forAGroup ? new CustomerGroup() : null);

        return $rule;
    }

    /** @param array<int, TierPriceRuleInterface> $rules */
    private function provider(array $rules): CartTierDiscountProvider
    {
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('find')->willReturnCallback(static fn (string $class, int $id): ?TierPriceRuleInterface => $rules[$id] ?? null);

        return new CartTierDiscountProvider($entityManager);
    }
}
