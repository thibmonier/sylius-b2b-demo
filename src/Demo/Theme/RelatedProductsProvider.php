<?php

declare(strict_types=1);

namespace App\Demo\Theme;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Core\Model\ProductInterface;

/**
 * US-189: the other products of the aisle (main taxon) of a product page, in the order of their codes starting after the
 * product itself, so that two pages of an aisle show different neighbours. Never the product itself, enabled products of
 * the channel only; an aisle with no other product gives an empty list (the page shows no block).
 */
final readonly class RelatedProductsProvider
{
    public const LIMIT = 4;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ChannelContextInterface $channelContext,
    ) {
    }

    /** @return list<ProductInterface> */
    public function forProduct(ProductInterface $product, int $limit = self::LIMIT): array
    {
        $taxon = $product->getMainTaxon();
        if (null === $taxon) {
            return [];
        }

        try {
            $channel = $this->channelContext->getChannel();
        } catch (ChannelNotFoundException) {
            return [];
        }

        /** @var list<ProductInterface> $siblings */
        $siblings = $this->entityManager->getRepository(ProductInterface::class)->createQueryBuilder('p')
            ->andWhere('p.mainTaxon = :taxon')
            ->andWhere('p.enabled = true')
            ->andWhere(':channel MEMBER OF p.channels')
            ->andWhere('p.id != :self')
            ->orderBy('p.code', 'ASC')
            ->setParameter('taxon', $taxon)
            ->setParameter('channel', $channel)
            ->setParameter('self', $product->getId())
            ->getQuery()->getResult();

        $after = array_values(array_filter($siblings, static fn (ProductInterface $sibling): bool => strcmp((string) $sibling->getCode(), (string) $product->getCode()) > 0));
        $before = array_values(array_filter($siblings, static fn (ProductInterface $sibling): bool => strcmp((string) $sibling->getCode(), (string) $product->getCode()) <= 0));

        return \array_slice([...$after, ...$before], 0, $limit);
    }
}
