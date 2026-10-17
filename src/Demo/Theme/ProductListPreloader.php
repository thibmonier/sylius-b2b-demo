<?php

declare(strict_types=1);

namespace App\Demo\Theme;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ProductInterface;

/**
 * Loads in two queries what the cards of a product list read product by product (variants and their option
 * values, channel prices, applied catalog promotions, images): the collections of the already managed products are initialised by the
 * fetch joins, so the cards (and the "from" price) then trigger no further query (S4-R3).
 */
final class ProductListPreloader
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /** @param iterable<mixed> $products */
    public function preload(iterable $products): void
    {
        $ids = [];
        foreach ($products as $product) {
            if ($product instanceof ProductInterface && null !== $product->getId()) {
                $ids[] = $product->getId();
            }
        }
        if ([] === $ids) {
            return;
        }

        $this->entityManager->createQueryBuilder()
            ->select('product', 'variant', 'optionValue', 'pricing', 'promotion')
            ->from(ProductInterface::class, 'product')
            ->leftJoin('product.variants', 'variant')
            ->leftJoin('variant.optionValues', 'optionValue')
            ->leftJoin('variant.channelPricings', 'pricing')
            ->leftJoin('pricing.appliedPromotions', 'promotion')
            ->where('product.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        $this->entityManager->createQueryBuilder()
            ->select('product', 'image')
            ->from(ProductInterface::class, 'product')
            ->leftJoin('product.images', 'image')
            ->where('product.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }
}
