<?php

declare(strict_types=1);

namespace App\Tests\Unit\PunchOut;

use App\PunchOut\AuthorizedPunchOutOrderFactory;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\Customer;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Resource\Factory\FactoryInterface;

final class AuthorizedPunchOutOrderFactoryTest extends TestCase
{
    public function testThePunchOutCartOfTheBuyerIsNotAGuestOrder(): void
    {
        $order = new AuthorizedPunchOutOrderFactory(self::syliusOrderFactory())->createNew();
        // What PunchoutCartFactory does next.
        $order->setCustomer(new Customer());

        self::assertFalse($order->isCreatedByGuest());
        self::assertNotNull($order->getCustomer());
    }

    public function testAPlainSyliusOrderWithACustomerIsAGuestOrder(): void
    {
        $order = self::syliusOrderFactory()->createNew();
        $order->setCustomer(new Customer());

        self::assertTrue($order->isCreatedByGuest(), 'The defect the factory works around.');
    }

    /** @return FactoryInterface<OrderInterface> */
    private static function syliusOrderFactory(): FactoryInterface
    {
        return new /** @implements FactoryInterface<OrderInterface> */ class() implements FactoryInterface {
            public function createNew(): OrderInterface
            {
                return new Order();
            }
        };
    }
}
