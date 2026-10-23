<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use App\Demo\DataFixtures\TradeCreditDemoFixture;
use App\Demo\PunchOutSimulator\DemoBuyerCodes;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Model\ShippingMethodInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tmonier\SyliusPunchoutCxmlPlugin\Buyer\Buyer;
use Tmonier\SyliusTradeCreditPlugin\Repository\PaymentTermsPolicyRepositoryInterface;

/**
 * Fix I3: an OrderRequest of the Acme buyer is accepted (the buyer has order payment/shipping methods) and
 * its order is paid on account, so the checkout transitions it replays reserve the Acme trade credit
 * (workflow listener) and the purchase approval of the flush applies, as for a shop order.
 *
 * Must be run AFTER loading the demo fixtures:
 *   bin/console sylius:fixtures:load --suite=demo --no-interaction
 */
final class PunchOutOrderRequestSmokeTest extends KernelTestCase
{
    public function testAcmeBuyerOrdersPayOnAccountWithTradeCreditTerms(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.orm.entity_manager');
        $buyer = $em->getRepository(Buyer::class)->findOneBy(['code' => DemoBuyerCodes::CXML]);
        self::assertInstanceOf(Buyer::class, $buyer);
        self::assertSame(TradeCreditDemoFixture::ON_ACCOUNT_PAYMENT_METHOD, $buyer->getOrderPaymentMethod());

        $method = $em->getRepository(PaymentMethodInterface::class)->findOneBy(['code' => $buyer->getOrderPaymentMethod()]);
        self::assertInstanceOf(PaymentMethodInterface::class, $method);
        self::assertTrue($method->isEnabled());
        self::assertTrue($method->hasChannel($buyer->getChannel() ?? self::fail('The buyer has a channel.')));

        /** @var PaymentTermsPolicyRepositoryInterface $policies */
        $policies = $container->get('tmonier_trade_credit.repository.payment_terms_policy');
        $policy = $policies->findOneByPaymentMethod($method);
        self::assertNotNull($policy, 'The on-account payment method must carry a trade-credit policy.');
        self::assertTrue($policy->isEnabled());
    }

    public function testAcmeBuyerShippingMethodExists(): void
    {
        self::bootKernel();

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        $buyer = $em->getRepository(Buyer::class)->findOneBy(['code' => DemoBuyerCodes::CXML]);
        self::assertInstanceOf(Buyer::class, $buyer);

        $shipping = $em->getRepository(ShippingMethodInterface::class)->findOneBy(['code' => $buyer->getOrderShippingMethod()]);
        self::assertInstanceOf(ShippingMethodInterface::class, $shipping);
    }
}
