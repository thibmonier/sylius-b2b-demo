<?php

declare(strict_types=1);

namespace App\Wishlist;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Inventory\Checker\AvailabilityCheckerInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Modifier\OrderModifierInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Sylius\WishlistPlugin\Entity\WishlistProductInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Adds the items of a list (all of them, or the selected ones) to the current cart with their quantities.
 * An item whose variant is disabled or out of stock is skipped and counted, not fatal.
 */
class CompanyWishlistCartAdder
{
    /** @param FactoryInterface<OrderItemInterface> $orderItemFactory */
    public function __construct(
        private readonly CartContextInterface $cartContext,
        #[Autowire(service: 'sylius.factory.order_item')]
        private readonly FactoryInterface $orderItemFactory,
        #[Autowire(service: 'sylius.modifier.order_item_quantity')]
        private readonly OrderItemQuantityModifierInterface $quantityModifier,
        #[Autowire(service: 'sylius.modifier.order')]
        private readonly OrderModifierInterface $orderModifier,
        #[Autowire(service: 'sylius.checker.inventory.availability')]
        private readonly AvailabilityCheckerInterface $availabilityChecker,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param iterable<WishlistProductInterface> $items
     *
     * @return array{added: int, skipped: int}
     */
    public function add(iterable $items): array
    {
        $cart = $this->cartContext->getCart();
        \assert($cart instanceof OrderInterface);

        $added = 0;
        $skipped = 0;
        foreach ($items as $item) {
            $this->addItem($cart, $item) ? ++$added : ++$skipped;
        }

        if ($added > 0) {
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        }

        return ['added' => $added, 'skipped' => $skipped];
    }

    private function addItem(OrderInterface $cart, WishlistProductInterface $item): bool
    {
        $variant = $item->getVariant();
        $quantity = max(1, $item->getQuantity());
        if (null === $variant || !$variant->isEnabled() || !$this->availabilityChecker->isStockSufficient($variant, $quantity)) {
            return false;
        }

        $cartItem = $this->orderItemFactory->createNew();
        $cartItem->setVariant($variant);
        $this->quantityModifier->modify($cartItem, $quantity);
        $this->orderModifier->addToOrder($cart, $cartItem);

        return true;
    }
}
