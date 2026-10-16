<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use App\Entity\Wishlist\CompanyWishlist;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\WishlistPlugin\Entity\WishlistInterface;
use Sylius\WishlistPlugin\Factory\WishlistFactoryInterface;
use Sylius\WishlistPlugin\Factory\WishlistProductFactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;

/**
 * Creates the "Atelier" purchase list of Acme Industries (US-024), created by Claire (buyer): the other
 * members read it, only Claire and the company admin (Isabelle) edit it.
 *
 * Depends on: demo_products_csv (-30), demo_companies (-15)
 * Priority: -40 (declared in config/packages/demo_fixtures.yaml)
 */
final class CompanyWishlistDemoFixture extends AbstractFixture
{
    private const COMPANY_CODE = 'ACME_IND';

    private const CREATOR_EMAIL = 'claire@acme.example';

    private const LIST_NAME = 'Atelier';

    /** Variant code (= product SKU of the catalogue CSV) => quantity. */
    private const ITEMS = [
        'EPI-CASK-001' => 10,
        'EPI-GANT-001' => 24,
        'EPI-CHAU-001' => 6,
        'VISS-001' => 200,
        'VISS-002' => 100,
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'sylius_wishlist_plugin.factory.wishlist')]
        private readonly WishlistFactoryInterface $wishlistFactory,
        #[Autowire(service: 'sylius_wishlist_plugin.factory.wishlist_product')]
        private readonly WishlistProductFactoryInterface $wishlistProductFactory,
    ) {
    }

    public function load(array $options): void
    {
        $company = $this->entityManager->getRepository(CompanyInterface::class)->findOneBy(['code' => self::COMPANY_CODE]);
        $creator = $this->entityManager->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => self::CREATOR_EMAIL]);
        $variants = $this->variants();
        if (!$company instanceof CompanyInterface || !$creator instanceof CustomerInterface || [] === $variants || $this->exists($company)) {
            return;
        }

        $wishlist = $this->wishlistFactory->createNew();
        if (!$wishlist instanceof WishlistInterface) {
            throw new \LogicException('The wishlist factory must build a WishlistInterface.');
        }
        $wishlist->setName(self::LIST_NAME);
        foreach ($variants as $variant) {
            $item = $this->wishlistProductFactory->createForWishlistAndVariant($wishlist, $variant);
            $item->setQuantity(self::ITEMS[(string) $variant->getCode()]);
            $wishlist->addWishlistProduct($item);
        }

        $this->entityManager->persist(new CompanyWishlist($company, $wishlist, $creator));
        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'demo_company_wishlists';
    }

    /** @return list<ProductVariantInterface> */
    private function variants(): array
    {
        /** @var list<ProductVariantInterface> $variants */
        $variants = $this->entityManager->getRepository(ProductVariantInterface::class)->findBy(['code' => array_keys(self::ITEMS)]);

        return $variants;
    }

    private function exists(CompanyInterface $company): bool
    {
        return null !== $this->entityManager->createQuery(
            'SELECT cw.id FROM ' . CompanyWishlist::class . ' cw JOIN cw.wishlist w WHERE cw.company = :company AND w.name = :name',
        )->setParameter('company', $company)->setParameter('name', self::LIST_NAME)->setMaxResults(1)->getOneOrNullResult();
    }
}
