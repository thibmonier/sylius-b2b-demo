<?php

declare(strict_types=1);

namespace App\Demo\Theme;

use Sylius\Component\Core\Model\ProductVariantInterface;

/**
 * Lowest unit price given by the volume tiers of a variant ("from X"), for the current channel and customer. Null when
 * the variant has no tier, or when the lowest tier is not strictly more than 5 % under the catalogue price (8.90 ->
 * 8.89 is noise, not a price argument): nothing is displayed. The rule lives in PriceBreaks (US-091).
 */
final class TierFromPriceProvider
{
    public function __construct(private readonly PriceBreaksProvider $priceBreaks)
    {
    }

    public function fromPrice(?ProductVariantInterface $variant): ?int
    {
        $breaks = $this->priceBreaks->forVariant($variant);

        return null !== $breaks && $breaks->hasSignificantGap() ? $breaks->lowestPrice() : null;
    }
}
