<?php

declare(strict_types=1);

namespace App\Tests\Unit\Wishlist;

use App\Wishlist\CompanyWishlistCartAdder;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Inventory\Checker\AvailabilityCheckerInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Modifier\OrderModifierInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Sylius\WishlistPlugin\Entity\WishlistProduct;

final class CompanyWishlistCartAdderTest extends TestCase
{
    public function testItAddsTheGivenItemsWithTheirQuantitiesAndSkipsTheUnavailableOnes(): void
    {
        $cart = $this->createStub(OrderInterface::class);
        $cartContext = $this->createStub(CartContextInterface::class);
        $cartContext->method('getCart')->willReturn($cart);

        $orderItemFactory = $this->createStub(FactoryInterface::class);
        $orderItemFactory->method('createNew')->willReturnCallback(fn () => $this->createStub(OrderItemInterface::class));

        $inStock = $this->variant(true);
        $outOfStock = $this->variant(true);
        $disabled = $this->variant(false);

        $availability = $this->createStub(AvailabilityCheckerInterface::class);
        $availability->method('isStockSufficient')->willReturnCallback(static fn (ProductVariantInterface $v): bool => $v !== $outOfStock);

        $quantities = [];
        $quantityModifier = $this->createStub(OrderItemQuantityModifierInterface::class);
        $quantityModifier->method('modify')->willReturnCallback(static function ($item, int $quantity) use (&$quantities): void {
            $quantities[] = $quantity;
        });

        $orderModifier = $this->createMock(OrderModifierInterface::class);
        $orderModifier->expects(self::once())->method('addToOrder')->with($cart, self::isInstanceOf(OrderItemInterface::class));

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with($cart);
        $entityManager->expects(self::once())->method('flush');

        $adder = new CompanyWishlistCartAdder($cartContext, $orderItemFactory, $quantityModifier, $orderModifier, $availability, $entityManager);

        $result = $adder->add([$this->item($inStock, 24), $this->item($outOfStock, 5), $this->item($disabled, 1)]);

        self::assertSame(['added' => 1, 'skipped' => 2], $result);
        self::assertSame([24], $quantities);
    }

    public function testNothingIsSavedWhenNothingIsAdded(): void
    {
        $cartContext = $this->createStub(CartContextInterface::class);
        $cartContext->method('getCart')->willReturn($this->createStub(OrderInterface::class));
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('flush');

        $adder = new CompanyWishlistCartAdder(
            $cartContext,
            $this->createStub(FactoryInterface::class),
            $this->createStub(OrderItemQuantityModifierInterface::class),
            $this->createStub(OrderModifierInterface::class),
            $this->createStub(AvailabilityCheckerInterface::class),
            $entityManager,
        );

        self::assertSame(['added' => 0, 'skipped' => 0], $adder->add([]));
    }

    private function variant(bool $enabled): ProductVariantInterface
    {
        $variant = $this->createStub(ProductVariantInterface::class);
        $variant->method('isEnabled')->willReturn($enabled);

        return $variant;
    }

    private function item(ProductVariantInterface $variant, int $quantity): WishlistProduct
    {
        $item = new WishlistProduct();
        $item->setVariant($variant);
        $item->setQuantity($quantity);

        return $item;
    }
}
