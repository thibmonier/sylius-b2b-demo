<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Order;

use App\Demo\Order\DeterministicOrderFactory;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Checker\OrderPaymentMethodSelectionRequirementCheckerInterface;
use Sylius\Component\Core\Checker\OrderShippingMethodSelectionRequirementCheckerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItem;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Core\Repository\ShippingMethodRepositoryInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;

final class DeterministicOrderFactoryTest extends TestCase
{
    public function testItBuildsExactlyTheRequestedLinesInOrder(): void
    {
        $quantities = [];
        $modifier = $this->createMock(OrderItemQuantityModifierInterface::class);
        $modifier->method('modify')->willReturnCallback(static function (OrderItem $item, int $quantity) use (&$quantities): void {
            $quantities[(string) $item->getVariant()?->getCode()] = $quantity;
        });
        $variants = $this->variantRepository(['EPI-GANT-001', 'VISS-001']);
        $orderItemFactory = $this->createMock(FactoryInterface::class);
        $orderItemFactory->method('createNew')->willReturnCallback(static fn (): OrderItem => new OrderItem());

        $factory = $this->factory($modifier, $orderItemFactory, $variants);
        $order = $this->createMock(OrderInterface::class);
        $order->expects(self::exactly(2))->method('addItem');

        $this->generate($factory, ['EPI-GANT-001' => 24, 'VISS-001' => 100], $order);

        self::assertSame(['EPI-GANT-001' => 24, 'VISS-001' => 100], $quantities);
    }

    public function testAnUnknownVariantFailsLoudly(): void
    {
        $factory = $this->factory(
            $this->createMock(OrderItemQuantityModifierInterface::class),
            $this->createMock(FactoryInterface::class),
            $this->variantRepository([]),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('NOPE-001');

        $this->generate($factory, ['NOPE-001' => 1], $this->createMock(OrderInterface::class));
    }

    public function testAnOrderWithoutLinesIsRefused(): void
    {
        $factory = $this->factory(
            $this->createMock(OrderItemQuantityModifierInterface::class),
            $this->createMock(FactoryInterface::class),
            $this->variantRepository([]),
        );

        $this->expectException(\LogicException::class);

        $this->generate($factory, [], $this->createMock(OrderInterface::class));
    }

    /** @param array<string, int> $items */
    private function generate(DeterministicOrderFactory $factory, array $items, OrderInterface $order): void
    {
        $property = new \ReflectionProperty($factory, 'items');
        $property->setValue($factory, $items);
        (new \ReflectionMethod($factory, 'generateItems'))->invoke($factory, $order);
    }

    /**
     * @param list<string> $codes
     *
     * @return RepositoryInterface<ProductVariantInterface>
     */
    private function variantRepository(array $codes): RepositoryInterface
    {
        $repository = $this->createMock(RepositoryInterface::class);
        $repository->method('findOneBy')->willReturnCallback(function (array $criteria) use ($codes): ?ProductVariantInterface {
            if (!in_array($criteria['code'], $codes, true)) {
                return null;
            }
            $variant = $this->createMock(ProductVariantInterface::class);
            $variant->method('getCode')->willReturn($criteria['code']);

            return $variant;
        });

        return $repository;
    }

    /**
     * @param FactoryInterface<OrderItemInterface>          $orderItemFactory
     * @param RepositoryInterface<ProductVariantInterface> $variants
     */
    private function factory(OrderItemQuantityModifierInterface $modifier, FactoryInterface $orderItemFactory, RepositoryInterface $variants): DeterministicOrderFactory
    {
        $factory = new DeterministicOrderFactory(
            $this->createMock(FactoryInterface::class),
            $orderItemFactory,
            $modifier,
            $this->createMock(ObjectManager::class),
            $this->createMock(RepositoryInterface::class),
            $this->createMock(RepositoryInterface::class),
            $this->createMock(ProductRepositoryInterface::class),
            $this->createMock(RepositoryInterface::class),
            $this->createMock(PaymentMethodRepositoryInterface::class),
            $this->createMock(ShippingMethodRepositoryInterface::class),
            $this->createMock(FactoryInterface::class),
            $this->createMock(StateMachineInterface::class),
            $this->createMock(OrderShippingMethodSelectionRequirementCheckerInterface::class),
            $this->createMock(OrderPaymentMethodSelectionRequirementCheckerInterface::class),
        );
        $factory->setVariantRepository($variants);

        return $factory;
    }
}
