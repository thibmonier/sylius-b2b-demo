<?php

declare(strict_types=1);

namespace App\Demo\DataFixtures;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\TokenAssigner\OrderTokenAssignerInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Modifier\OrderModifierInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * US-096: the starting cart of Claire (Acme buyer), the "Atelier" purchase list of her company put in the cart with
 * the same quantities. The visitor finds the cart gauge and the buyer limit at work without building the scenario.
 * The cart belongs to her (not "created by a guest"), so that the shop resumes it when she signs in.
 *
 * Depends on: products (-30), companies and customers (-15), the Atelier list (-40), the order history (-60).
 * Priority: -71 (after the container-fill data, before the webhook endpoint: no webhook for a fixture).
 */
final class CartDemoFixture extends AbstractFixture
{
    private const CUSTOMER_EMAIL = 'claire@acme.example';

    /**
     * @param FactoryInterface<OrderInterface>                  $orderFactory
     * @param FactoryInterface<OrderItemInterface>              $orderItemFactory
     * @param RepositoryInterface<ChannelInterface>             $channelRepository
     * @param RepositoryInterface<CustomerInterface>            $customerRepository
     * @param RepositoryInterface<ProductVariantInterface>      $variantRepository
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'sylius.factory.order')]
        private readonly FactoryInterface $orderFactory,
        #[Autowire(service: 'sylius.factory.order_item')]
        private readonly FactoryInterface $orderItemFactory,
        #[Autowire(service: 'sylius.modifier.order_item_quantity')]
        private readonly OrderItemQuantityModifierInterface $quantityModifier,
        #[Autowire(service: 'sylius.modifier.order')]
        private readonly OrderModifierInterface $orderModifier,
        private readonly OrderTokenAssignerInterface $tokenAssigner,
        #[Autowire(service: 'sylius.repository.channel')]
        private readonly RepositoryInterface $channelRepository,
        #[Autowire(service: 'sylius.repository.customer')]
        private readonly RepositoryInterface $customerRepository,
        #[Autowire(service: 'sylius.repository.product_variant')]
        private readonly RepositoryInterface $variantRepository,
    ) {
    }

    public function load(array $options): void
    {
        $channel = $this->channelRepository->findOneBy(['code' => 'B2B']);
        $customer = $this->customerRepository->findOneBy(['emailCanonical' => self::CUSTOMER_EMAIL]);
        $variants = $this->variantRepository->findBy(['code' => array_keys(CompanyWishlistDemoFixture::ITEMS)]);
        if (!$channel instanceof ChannelInterface || !$customer instanceof CustomerInterface || [] === $variants || $this->hasCart($customer)) {
            return;
        }

        $cart = $this->orderFactory->createNew();
        $cart->setChannel($channel);
        $cart->setCurrencyCode((string) $channel->getBaseCurrency()?->getCode());
        $cart->setLocaleCode((string) $channel->getDefaultLocale()?->getCode());
        $cart->setCustomerWithAuthorization($customer);
        $this->tokenAssigner->assignTokenValueIfNotSet($cart);

        foreach ($variants as $variant) {
            $item = $this->orderItemFactory->createNew();
            $item->setVariant($variant);
            $this->quantityModifier->modify($item, CompanyWishlistDemoFixture::ITEMS[(string) $variant->getCode()]);
            $this->orderModifier->addToOrder($cart, $item);
        }

        $this->entityManager->persist($cart);
        $this->entityManager->flush();
    }

    public function getName(): string
    {
        return 'demo_cart';
    }

    private function hasCart(CustomerInterface $customer): bool
    {
        return null !== $this->entityManager->createQuery(
            'SELECT o.id FROM ' . OrderInterface::class . ' o WHERE o.customer = :customer AND o.state = :state AND o.createdByGuest = false',
        )->setParameter('customer', $customer)->setParameter('state', OrderInterface::STATE_CART)->setMaxResults(1)->getOneOrNullResult();
    }
}
