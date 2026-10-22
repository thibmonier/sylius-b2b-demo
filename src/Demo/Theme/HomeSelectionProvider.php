<?php

declare(strict_types=1);

namespace App\Demo\Theme;

use App\Entity\Wishlist\CompanyWishlist;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Sylius\WishlistPlugin\Entity\WishlistProduct;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Service\ResetInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Repository\CompanyMemberRepositoryInterface;
use Tmonier\SyliusTierPricingPlugin\Entity\TierPriceRuleInterface;

/**
 * Products of the B2B home page for the signed-in buyer (US-089): the references they usually order (order history),
 * a quick restock (the shared purchase list of the company, "Atelier") and the products at a negotiated price. The
 * blocks are filled in this order and never share a product; a buyer without history gets catalogue suggestions
 * instead of an empty block, a product withdrawn from sale is simply left out.
 */
final class HomeSelectionProvider implements ResetInterface
{
    private const LIMIT = 4;

    private ?HomeSelection $selection = null;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ChannelContextInterface $channelContext,
        private readonly CustomerContextInterface $customerContext,
        #[Autowire(service: 'tmonier_company_accounts.repository.company_member')]
        private readonly CompanyMemberRepositoryInterface $memberRepository,
        private readonly PriceBreaksProvider $priceBreaks,
    ) {
    }

    public function forCurrentCustomer(): HomeSelection
    {
        return $this->selection ??= $this->build();
    }

    public function reset(): void
    {
        $this->selection = null;
    }

    private function build(): HomeSelection
    {
        $customer = $this->customerContext->getCustomer();
        $channel = $this->channel();
        if (!$customer instanceof CustomerInterface || null === $channel) {
            return HomeSelection::empty();
        }

        $taken = [];
        $usual = $this->history($customer, $channel);
        $fromHistory = [] !== $usual;
        $usual = $fromHistory ? $usual : $this->suggestions($channel, $taken);
        $taken = $this->ids($usual);

        $company = $this->memberRepository->findOneByCustomer($customer)?->getCompany();
        $restock = null === $company ? [] : $this->sharedList($company, $channel, $taken);
        $fromList = [] !== $restock;
        $restock = $fromList ? $restock : $this->suggestions($channel, $taken);
        $taken = [...$taken, ...$this->ids($restock)];

        return new HomeSelection($usual, $restock, $this->negotiated($customer, $channel, $taken), $fromHistory, $fromList);
    }

    /**
     * Most ordered first (number of orders, then quantity), cancelled orders and carts left out.
     *
     * @return list<ProductInterface>
     */
    private function history(CustomerInterface $customer, ChannelInterface $channel): array
    {
        /** @var list<array{id: int|string}> $rows */
        $rows = $this->entityManager->getRepository(OrderInterface::class)->createQueryBuilder('o')
            ->select('p.id AS id')
            ->join('o.items', 'i')
            ->join('i.variant', 'v')
            ->join('v.product', 'p')
            ->andWhere('o.customer = :customer')
            ->andWhere('o.state NOT IN (:states)')
            ->andWhere('p.enabled = true')
            ->andWhere(':channel MEMBER OF p.channels')
            ->groupBy('p.id')
            ->orderBy('COUNT(DISTINCT o.id)', 'DESC')
            ->addOrderBy('SUM(i.quantity)', 'DESC')
            ->addOrderBy('p.id', 'ASC')
            ->setParameter('customer', $customer)
            ->setParameter('states', [OrderInterface::STATE_CART, OrderInterface::STATE_CANCELLED])
            ->setParameter('channel', $channel)
            ->setMaxResults(self::LIMIT)
            ->getQuery()->getArrayResult();

        return $this->products(array_map(static fn (array $row): int => (int) $row['id'], $rows));
    }

    /**
     * @param list<int> $taken
     *
     * @return list<ProductInterface>
     */
    private function sharedList(CompanyInterface $company, ChannelInterface $channel, array $taken): array
    {
        $query = $this->enabledProducts($channel, $taken)
            ->join(WishlistProduct::class, 'wp', Join::ON, 'wp.product = p')
            ->join(CompanyWishlist::class, 'cw', Join::ON, 'cw.wishlist = wp.wishlist')
            ->andWhere('cw.company = :company')
            ->setParameter('company', $company)
            ->orderBy('wp.id', 'ASC');

        return $this->limited($query);
    }

    /**
     * Products with a rule of the customer group (the company price) and a real gap with the public price.
     *
     * @param list<int> $taken
     *
     * @return list<ProductInterface>
     */
    private function negotiated(CustomerInterface $customer, ChannelInterface $channel, array $taken): array
    {
        $group = $customer->getGroup();
        if (null === $group) {
            return [];
        }
        $query = $this->enabledProducts($channel, $taken)
            ->andWhere('p.id IN (SELECT IDENTITY(r.product) FROM ' . TierPriceRuleInterface::class . ' r WHERE r.customerGroup = :group AND r.channel = :channel AND r.enabled = true)')
            ->setParameter('group', $group)
            ->orderBy('p.code', 'ASC');

        $products = [];
        foreach ($this->products($this->limitedIds($query, self::LIMIT * 3)) as $product) {
            $variant = $product->getEnabledVariants()->first();
            if ($variant instanceof ProductVariantInterface && null !== $this->priceBreaks->forVariant($variant)?->negotiated()) {
                $products[] = $product;
            }
        }

        return array_slice($products, 0, self::LIMIT);
    }

    /**
     * @param list<int> $taken
     *
     * @return list<ProductInterface>
     */
    private function suggestions(ChannelInterface $channel, array $taken): array
    {
        return $this->limited($this->enabledProducts($channel, $taken)->orderBy('p.createdAt', 'DESC')->addOrderBy('p.id', 'ASC'));
    }

    /** @param list<int> $taken */
    private function enabledProducts(ChannelInterface $channel, array $taken): QueryBuilder
    {
        return $this->entityManager->getRepository(ProductInterface::class)->createQueryBuilder('p')
            ->andWhere('p.enabled = true')
            ->andWhere(':channel MEMBER OF p.channels')
            ->andWhere('p.id NOT IN (:taken)')
            ->setParameter('channel', $channel)
            ->setParameter('taken', [] === $taken ? [0] : $taken);
    }

    /** @return list<ProductInterface> */
    private function limited(QueryBuilder $query): array
    {
        return $this->products($this->limitedIds($query, self::LIMIT));
    }

    /** @return list<int> distinct ids, in the order of the query */
    private function limitedIds(QueryBuilder $query, int $limit): array
    {
        /** @var list<ProductInterface> $products */
        $products = $query->setMaxResults($limit * 3)->getQuery()->getResult();
        $ids = [];
        foreach ($products as $product) {
            $ids[(int) $product->getId()] = (int) $product->getId();
        }

        return array_slice($ids, 0, $limit);
    }

    /**
     * @param list<int> $ids
     *
     * @return list<ProductInterface>
     */
    private function products(array $ids): array
    {
        $products = [];
        foreach ($ids as $id) {
            $product = $this->entityManager->getRepository(ProductInterface::class)->find($id);
            if ($product instanceof ProductInterface) {
                $products[] = $product;
            }
        }

        return $products;
    }

    /**
     * @param list<ProductInterface> $products
     *
     * @return list<int>
     */
    private function ids(array $products): array
    {
        return array_map(static fn (ProductInterface $product): int => (int) $product->getId(), $products);
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
