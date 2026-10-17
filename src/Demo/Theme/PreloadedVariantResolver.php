<?php

declare(strict_types=1);

namespace App\Demo\Theme;

use Doctrine\ORM\PersistentCollection;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

/**
 * The core resolver queries the first enabled variant of each product, even when the variants are already loaded:
 * one query per card of a list. When ProductListPreloader has initialised the collection (same order: position,
 * id), the answer is read from memory; otherwise the core resolver is used unchanged (S4-R3).
 */
#[AsDecorator('sylius.resolver.product_variant.default')]
final class PreloadedVariantResolver implements ProductVariantResolverInterface
{
    public function __construct(private readonly ProductVariantResolverInterface $inner)
    {
    }

    public function getVariant(ProductInterface $subject): ?ProductVariantInterface
    {
        $variants = $subject->getVariants();
        if (!$variants instanceof PersistentCollection || !$variants->isInitialized()) {
            return $this->inner->getVariant($subject);
        }

        $first = $subject->getEnabledVariants()->first();

        return $first instanceof ProductVariantInterface ? $first : null;
    }
}
