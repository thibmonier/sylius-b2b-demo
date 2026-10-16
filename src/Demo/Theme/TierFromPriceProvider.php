<?php

declare(strict_types=1);

namespace App\Demo\Theme;

use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Tmonier\SyliusTierPricingPlugin\Pricing\TierPriceCalculatorInterface;

/**
 * Lowest unit price given by the volume tiers of a variant ("from X"), for the current channel and
 * customer. Uses the tier-pricing calculator (rules are served by its cached rule provider, so no
 * per-card database query once warm). Null when the variant has no tier: nothing is displayed.
 */
final class TierFromPriceProvider
{
    public function __construct(
        private readonly TierPriceCalculatorInterface $calculator,
        private readonly ChannelContextInterface $channelContext,
        private readonly CustomerContextInterface $customerContext,
    ) {
    }

    public function fromPrice(?ProductVariantInterface $variant): ?int
    {
        if (null === $variant) {
            return null;
        }

        try {
            $channel = $this->channelContext->getChannel();
        } catch (ChannelNotFoundException) {
            return null;
        }
        if (!$channel instanceof ChannelInterface) {
            return null;
        }

        $table = $this->calculator->table($variant, $channel, $this->customerContext->getCustomer()?->getGroup());
        if (!$table->hasTiers()) {
            return null;
        }
        $prices = array_map(static fn ($row): int => $row->unitPrice, $table->rows);

        return [] === $prices ? null : min($prices);
    }
}
