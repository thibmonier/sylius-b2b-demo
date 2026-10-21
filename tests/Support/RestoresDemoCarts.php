<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Demo\DataFixtures\CartDemoFixture;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\After;
use Sylius\Component\Core\Model\OrderInterface;

/**
 * For a test that fills the cart of a shared demo account through the shop (shared list to cart): at the end, the carts
 * of the demo accounts are removed and the cart of Claire is rebuilt by its fixture, so the next test finds the data
 * as the fixtures left it, whatever the order of the suite. A transaction cannot do it here: the shop code that adds
 * the list to the cart does not run on one kernel kept across requests.
 */
trait RestoresDemoCarts
{
    #[After]
    protected function restoreDemoCarts(): void
    {
        self::ensureKernelShutdown();
        self::bootKernel();
        $container = self::getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $em);
        $carts = $em->createQuery(
            'SELECT o FROM ' . OrderInterface::class . ' o JOIN o.customer c WHERE c.emailCanonical IN (:emails) AND o.state = :state',
        )->setParameter('emails', ['claire@acme.example', 'paul@acme.example'])->setParameter('state', OrderInterface::STATE_CART)->getResult();
        self::assertIsArray($carts);
        foreach ($carts as $cart) {
            self::assertInstanceOf(OrderInterface::class, $cart);
            $em->remove($cart);
        }
        $em->flush();
        $fixture = $container->get('test.demo_cart_fixture');
        self::assertInstanceOf(CartDemoFixture::class, $fixture);
        $fixture->load([]);
        self::ensureKernelShutdown();
    }
}
