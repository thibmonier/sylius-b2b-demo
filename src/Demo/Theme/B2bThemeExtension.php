<?php

declare(strict_types=1);

namespace App\Demo\Theme;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
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
        private readonly ProductListPreloader $listPreloader,
        private readonly PriceBreaksProvider $priceBreaks,
        private readonly CartTierDiscountProvider $cartDiscounts,
        private readonly HomeSelectionProvider $homeSelection,
        private readonly RelatedProductsProvider $relatedProducts,
        private readonly ListCategories $listCategories,
        private readonly ListBuyer $listBuyer,
        private readonly ListState $listState,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('app_b2b_packaging_unit', fn (?ProductInterface $product): ?string => $this->packagingUnits->forProductId($product?->getId())),
            new TwigFunction('app_b2b_tier_from_price', fn (?ProductInterface $product): ?int => $this->fromPrices->fromPrice($this->firstVariant($product))),
            new TwigFunction('app_b2b_price_breaks', fn (ProductInterface|ProductVariantInterface|null $subject): ?PriceBreaks => $this->priceBreaks->forVariant($subject instanceof ProductInterface ? $this->firstVariant($subject) : $subject)),
            new TwigFunction('app_b2b_cart_tier_discount', fn (OrderInterface $order): ?CartTierDiscount => $this->cartDiscounts->forOrder($order)),
            new TwigFunction('app_b2b_item_negotiated', fn (OrderItemInterface $item): bool => $this->cartDiscounts->isNegotiated($item)),
            new TwigFunction('app_b2b_percent', static fn (float $rate, string $locale): string => self::percent($rate, $locale)),
            new TwigFunction('app_b2b_home_selection', fn (): HomeSelection => $this->homeSelection->forCurrentCustomer()),
            new TwigFunction('app_b2b_related_products', fn (ProductInterface $product): array => $this->relatedProducts->forProduct($product)),
            new TwigFunction('app_b2b_stock', fn (?ProductInterface $product): StockStatus => StockStatus::ofVariant($this->firstVariant($product))),
            new TwigFunction('app_b2b_list_categories', fn (): array => $this->listCategories->all()),
            new TwigFunction('app_b2b_list_state', fn (): ListState => $this->listState),
            new TwigFunction('app_b2b_list_buyer', fn (): array => $this->listBuyer->current()),
            new TwigFunction('app_b2b_preload_product_list', fn (iterable $products): string => $this->preload($products)),
        ];
    }

    /** "12 %" / "12,5 %" for the locale: the rate of a rule, at most one decimal. */
    private static function percent(float $rate, string $locale): string
    {
        $formatter = new \NumberFormatter($locale, \NumberFormatter::PERCENT);
        $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 1);

        return (string) $formatter->format($rate / 100);
    }

    /** @param iterable<mixed> $products */
    private function preload(iterable $products): string
    {
        $this->listPreloader->preload($products);

        return '';
    }

    private function firstVariant(?ProductInterface $product): ?ProductVariantInterface
    {
        $variant = $product?->getEnabledVariants()->first();

        return $variant instanceof ProductVariantInterface ? $variant : null;
    }
}
