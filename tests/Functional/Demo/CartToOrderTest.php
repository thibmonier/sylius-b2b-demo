<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Tests\Support\BuildsDemoCarts;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Model\ShippingMethodInterface;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-200: the order from the cart to the validation, end to end. Claire opens her cart, validates it (the cart form
 * leads to the checkout), goes through address, shipping and payment, and places an order above her limit: it is
 * placed and waits for the approval of her company. Rolled back with the transaction.
 */
final class CartToOrderTest extends WebTestCase
{
    use BuildsDemoCarts {
        tearDown as private rollBackSharedAccounts;
    }
    use DatabaseAccess;

    private const CLAIRE = 'claire@acme.example';

    /** @var list<string> */
    private array $invoiceFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->invoiceFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        $this->invoiceFiles = [];
        $this->rollBackSharedAccounts();
    }

    public function testClairesCartAboveHerLimitBecomesAnOrderWaitingForApproval(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $order = $this->cartOf(self::CLAIRE, ['EPI-CASK-001' => 10, 'EPI-CHAU-001' => 6, 'EPI-GANT-001' => 24]);

        $crawler = $client->request('GET', '/fr_FR/cart/');
        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('[data-test-b2b-cart-approval]'), 'The cart announces the approval.');

        $client->submit($this->checkoutForm($crawler));
        self::assertResponseRedirects();
        self::assertStringContainsString('/checkout', (string) $client->getResponse()->headers->get('Location'), 'The cart leads to the checkout.');

        $this->prepareCheckout($order);
        $this->forgetNextInvoiceFile();
        $this->stateMachine()->apply($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_COMPLETE);
        $this->entityManager()->flush();

        self::assertSame(OrderInterface::STATE_NEW, $order->getState(), 'The order is placed.');
        self::assertSame('pending', $this->stringValue('SELECT state FROM tmonier_purchase_approval_order_approval WHERE order_id = ?', [$order->getId()]), 'It waits for the approval of the company.');
        self::assertSame($order->getTotal(), $this->intValue('SELECT total FROM tmonier_purchase_approval_order_approval WHERE order_id = ?', [$order->getId()]));
    }

    public function testACartUnderHerLimitIsPlacedWithoutApproval(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $order = $this->cartOf(self::CLAIRE, ['EPI-GANT-001' => 1]);

        $client->request('GET', '/fr_FR/cart/');
        self::assertResponseIsSuccessful();

        $this->prepareCheckout($order);
        $this->forgetNextInvoiceFile();
        $this->stateMachine()->apply($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_COMPLETE);
        $this->entityManager()->flush();

        self::assertSame(OrderInterface::STATE_NEW, $order->getState());
        self::assertSame(0, $this->intValue('SELECT COUNT(*) FROM tmonier_purchase_approval_order_approval WHERE order_id = ?', [$order->getId()]));
    }

    private function checkoutForm(Crawler $crawler): \Symfony\Component\DomCrawler\Form
    {
        return $crawler->filter('[data-test-cart-checkout-button]')->form();
    }

    private function prepareCheckout(OrderInterface $order): void
    {
        $container = self::getContainer();
        $addressFactory = $container->get('sylius.factory.address');
        $processor = $container->get('sylius.order_processing.order_processor');
        self::assertInstanceOf(FactoryInterface::class, $addressFactory);
        self::assertInstanceOf(OrderProcessorInterface::class, $processor);

        $address = $addressFactory->createNew();
        self::assertInstanceOf(AddressInterface::class, $address);
        $address->setFirstName('Claire');
        $address->setLastName('Acme');
        $address->setStreet('12 rue de l\'Industrie');
        $address->setCountryCode('FR');
        $address->setCity('Lyon');
        $address->setPostcode('69003');
        $order->setShippingAddress($address);
        $order->setBillingAddress(clone $address);
        $this->stateMachine()->apply($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_ADDRESS);
        $processor->process($order);

        $shippingMethod = $this->entityManager()->getRepository(ShippingMethodInterface::class)->findOneBy(['code' => 'carrier']);
        self::assertInstanceOf(ShippingMethodInterface::class, $shippingMethod);
        foreach ($order->getShipments() as $shipment) {
            $shipment->setMethod($shippingMethod);
        }
        $this->stateMachine()->apply($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING);
        $processor->process($order);

        $paymentMethod = $this->entityManager()->getRepository(PaymentMethodInterface::class)->findOneBy(['code' => 'bank_transfer']);
        self::assertInstanceOf(PaymentMethodInterface::class, $paymentMethod);
        foreach ($order->getPayments() as $payment) {
            $payment->setMethod($paymentMethod);
        }
        $this->stateMachine()->apply($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_SELECT_PAYMENT);
        $this->entityManager()->flush();
    }

    /** The invoice PDF is a file, not rolled back: the file of the next number is cleared and removed at the end. */
    private function forgetNextInvoiceFile(): void
    {
        $next = $this->intValue('SELECT idx FROM sylius_invoicing_plugin_sequence') + 1;
        $projectDir = self::getContainer()->getParameter('kernel.project_dir');
        self::assertIsString($projectDir);
        $file = $projectDir . '/private/invoices/' . date('Y_m') . '_' . sprintf('%09d', $next) . '.pdf';
        $this->invoiceFiles[] = $file;
        if (is_file($file)) {
            unlink($file);
        }
    }

    private function stateMachine(): StateMachineInterface
    {
        $machine = self::getContainer()->get('sylius_abstraction.state_machine');
        self::assertInstanceOf(StateMachineInterface::class, $machine);

        return $machine;
    }
}
