<?php

declare(strict_types=1);

namespace App\Demo\Theme;

use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Tmonier\SyliusTierPricingPlugin\Pricing\TierPriceCalculatorInterface;
use Tmonier\SyliusTierPricingPlugin\Pricing\TierPriceTable;

/**
 * Price breaks of a variant for the signed-in customer (US-090, decision D2). The plugin has no rule "per company":
 * the company price is a rule of the customer group of the company, so it is already part of the table computed for
 * that group. The public table (no group) tells which rows are the negotiated ones.
 */
final class PriceBreaksProvider
{
    public function __construct(
        private readonly TierPriceCalculatorInterface $calculator,
        private readonly ChannelContextInterface $channelContext,
        private readonly CustomerContextInterface $customerContext,
    ) {
    }

    public function forVariant(?ProductVariantInterface $variant): ?PriceBreaks
    {
        $channel = $this->channel();
        if (null === $variant || null === $channel) {
            return null;
        }

        $group = $this->customerContext->getCustomer()?->getGroup();
        $table = $this->calculator->table($variant, $channel, $group);
        if (!$table->hasTiers()) {
            return null;
        }
        $public = null === $group ? $table : $this->calculator->table($variant, $channel, null);

        $rows = [];
        foreach ($table->rows as $row) {
            $rows[] = new PriceBreakRow($row->minQuantity, $row->unitPrice, $this->rate($table->rows[0]->unitPrice, $row->unitPrice, $row->percentage), $row->unitPrice < $this->publicPrice($public, $row->minQuantity));
        }

        return new PriceBreaks($rows, $group?->getName());
    }

    /**
     * The percentage of the rule behind a price. The plugin only gives the gap of the rounded price (5.08 for a 5 %
     * rule on 8.45): the closest half-point rate that gives back exactly this price is the rate of the rule. Without
     * such a rate (fixed unit price), the gap itself.
     */
    private function rate(int $base, int $price, float $gap): float
    {
        if ($price >= $base) {
            return 0.0;
        }
        $best = null;
        for ($rate = 0.5; $rate <= 100.0; $rate += 0.5) {
            if ((int) floor($base * (100 - $rate) / 100 + 0.5) === $price && (null === $best || abs($rate - $gap) < abs($best - $gap))) {
                $best = $rate;
            }
        }

        return $best ?? round($gap, 1);
    }

    private function publicPrice(TierPriceTable $public, int $quantity): int
    {
        $price = $public->rows[0]->unitPrice ?? \PHP_INT_MAX;
        foreach ($public->rows as $row) {
            if ($row->minQuantity <= $quantity) {
                $price = $row->unitPrice;
            }
        }

        return $price;
    }

    private function channel(): ?ChannelInterface
    {
        try {
            $channel = $this->channelContext->getChannel();
        } catch (ChannelNotFoundException) {
            return null;
        }

        return $channel instanceof ChannelInterface ? $channel : null;
    }
}
