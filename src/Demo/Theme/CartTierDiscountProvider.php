<?php

declare(strict_types=1);

namespace App\Demo\Theme;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Tmonier\SyliusTierPricingPlugin\Entity\TierPriceRuleInterface;
use Tmonier\SyliusTierPricingPlugin\OrderProcessing\TierPriceOrderProcessor;

/**
 * Discount line of the cart (US-091): the plugin changes the unit price of the lines and keeps the price without
 * tier in a neutral adjustment; the difference, split between public tiers and the company price (a rule of the
 * company customer group), is what the buyer checks before validating the order.
 */
final class CartTierDiscountProvider
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /** Null when no line gets a tier price (empty cart included): no discount block. */
    public function forOrder(OrderInterface $order): ?CartTierDiscount
    {
        $tier = 0;
        $negotiated = 0;
        foreach ($order->getItems() as $item) {
            $withoutTier = TierPriceOrderProcessor::priceWithoutTier($item);
            if (null === $withoutTier) {
                continue;
            }
            $amount = max(0, $withoutTier - $item->getUnitPrice()) * $item->getQuantity();
            $this->isNegotiated($item) ? $negotiated += $amount : $tier += $amount;
        }

        return $tier + $negotiated > 0 ? new CartTierDiscount($tier, $negotiated) : null;
    }

    /** The tier applied to the line comes from a rule of the customer group (the company price). */
    public function isNegotiated(OrderItemInterface $item): bool
    {
        $marker = $item->getAdjustments(TierPriceOrderProcessor::ADJUSTMENT_TYPE)->first();
        if (false === $marker) {
            return false;
        }
        $ruleId = $marker->getDetails()['ruleId'] ?? null;
        if (!is_int($ruleId)) {
            return false;
        }
        $rule = $this->entityManager->find(TierPriceRuleInterface::class, $ruleId);

        return $rule instanceof TierPriceRuleInterface && null !== $rule->getCustomerGroup();
    }
}
