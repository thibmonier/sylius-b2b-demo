<?php

declare(strict_types=1);

namespace App\Demo\Theme;

use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/** Data of the dense B2B catalogue rows (US-013). */
final class B2bThemeExtension extends AbstractExtension
{
    public function __construct(
        private readonly PackagingUnitProvider $packagingUnits,
        private readonly TierFromPriceProvider $fromPrices,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('app_b2b_packaging_unit', fn (?ProductInterface $product): ?string => $this->packagingUnits->forProductId($product?->getId())),
            new TwigFunction('app_b2b_tier_from_price', fn (?ProductInterface $product): ?int => $this->fromPrices->fromPrice($this->firstVariant($product))),
        ];
    }

    private function firstVariant(?ProductInterface $product): ?ProductVariantInterface
    {
        $variant = $product?->getEnabledVariants()->first();

        return $variant instanceof ProductVariantInterface ? $variant : null;
    }
}
