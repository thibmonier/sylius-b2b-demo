<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Channel\DemoChannelContext;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Bundle\OrderBundle\Controller\AddToCartCommand;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Model\ShippingMethodInterface;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * US-044: the container-fill plugin lives in the demo. Run against a database loaded with the `demo` suite;
 * carts and orders are created inside a transaction that is rolled back. Assertions are on the served HTML.
 *
 * Products: EMBA-001 (carton pack, 136 per EUR pallet, 117 by layers), EMBA-008 (24 kg, 9 per EUR pallet),
 * VISS-001 (a box of 100 screws is the sold unit: 350 g, 8 x 5 x 5 cm, 28 per carton S, bought one box at a time).
 */
final class ContainerFillDemoTest extends WebTestCase
{
    use DatabaseAccess;

    private const CLAIRE = 'claire@acme.example';

    private const LEA = 'lea@demo.example';

    private ?KernelBrowser $client = null;

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
        if ($this->client instanceof KernelBrowser && $this->db()->isTransactionActive()) {
            $this->db()->rollBack();
        }
        parent::tearDown();
    }

    public function testClaireSeesAnAccessibleGaugeAndADisabledCheckoutForAPartialPallet(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $this->cartOf(self::CLAIRE, ['EMBA-001' => 10], 'B2B');

        $html = $this->get($client, '/fr_FR/cart/');

        self::assertStringContainsString('data-test-tmonier-container-fill-gauge', $html);
        self::assertStringContainsString('role="meter"', $html);
        self::assertMatchesRegularExpression('/aria-valuetext="[^"]*%[^"]*"/', $html, 'The meter says its fill in words.');
        self::assertStringContainsString('data-test-tmonier-container-fill-status', $html);
        self::assertStringContainsString('Emballage : palettes complètes', $html);
        self::assertMatchesRegularExpression('/<button[^>]*\bdisabled\b[^>]*aria-describedby="tmonier-container-fill-checkout-reason"/', $html);
        self::assertStringContainsString('data-test-tmonier-container-fill-checkout-reason', $html);
    }

    public function testTheGaugeOffersToCompleteThePallet(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $this->cartOf(self::CLAIRE, ['EMBA-008' => 5], 'B2B');

        $html = $this->get($client, '/fr_FR/cart/');

        self::assertStringContainsString('data-test-tmonier-container-fill-suggestion', $html);
        self::assertStringContainsString('data-test-tmonier-container-fill-complete', $html, 'A button adds the missing quantity.');
        self::assertMatchesRegularExpression('/data-test-tmonier-container-fill-complete[^>]*>\s*Ajouter 4\b/u', $html, '5 of 9 per EUR pallet: 4 are missing.');
    }

    public function testAPalletPlannedByLayersOffersToRoundUpToTheLayer(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $this->cartOf(self::CLAIRE, ['EMBA-001' => 10], 'B2B');

        $html = $this->get($client, '/fr_FR/cart/');

        self::assertStringContainsString('data-test-tmonier-container-fill-stack', $html, 'A side view of the pallet layers.');
        self::assertStringContainsString('data-test-tmonier-container-fill-round-layer="EMBA-001"', $html);
    }

    public function testAFullPalletCartMeetsTheRuleAndKeepsTheCheckoutEnabled(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $this->cartOf(self::CLAIRE, ['EMBA-008' => 9], 'B2B');

        $html = $this->get($client, '/fr_FR/cart/');

        self::assertStringContainsString('data-test-tmonier-container-fill-gauge', $html);
        self::assertStringContainsString('data-test-tmonier-container-fill-status', $html);
        self::assertStringNotContainsString('tmonier-container-fill-checkout-reason', $html);
        self::assertDoesNotMatchRegularExpression('/<button[^>]*\bdisabled\b[^>]*data-test-cart-checkout-button/', $html);
    }

    public function testACartOfAnotherFamilyIsNotGauged(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $this->cartOf(self::CLAIRE, ['EPI-CASK-001' => 3], 'B2B');

        $html = $this->get($client, '/fr_FR/cart/');

        self::assertStringNotContainsString('data-test-tmonier-container-fill-gauge', $html);
        self::assertStringNotContainsString('tmonier-container-fill-checkout-reason', $html);
    }

    public function testTheProductPageOfABoxShowsThePreviewAndSellsTheBoxOneAtATime(): void
    {
        $client = $this->clientOf(self::CLAIRE);

        $html = $this->get($client, '/fr_FR/products/viss-001-fr');

        self::assertStringContainsString('data-test-tmonier-container-fill-preview=', $html);
        self::assertStringContainsString('data-test-tmonier-container-fill-preview-row', $html);
        self::assertDoesNotMatchRegularExpression('/id="sylius_shop_add_to_cart_cartItem_quantity"[^>]*step="/', $html, 'A box is the sold unit: no quantity step.');
        self::assertStringNotContainsString('Vendu par cartons complets', $html);
    }

    public function testAnyNumberOfBoxesIsAccepted(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $client->request('GET', '/fr_FR/');
        $variant = $this->variant('VISS-001');
        $cart = $this->cartOf(self::CLAIRE, [], 'B2B');
        $validator = self::getContainer()->get('validator');
        self::assertInstanceOf(ValidatorInterface::class, $validator);

        foreach ([1, 3, 150, 200] as $boxes) {
            $violations = $validator->validate(new AddToCartCommand($cart, $this->orderItem($variant, $boxes)), null, ['sylius']);
            self::assertSame(0, $violations->count(), $boxes . ' boxes of 100 screws are a valid quantity.');
        }
    }

    public function testABoxOfScrewsFitsACartonWithoutOverload(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $this->cartOf(self::CLAIRE, ['VISS-001' => 2], 'B2B');

        $html = $this->get($client, '/fr_FR/cart/');

        self::assertStringContainsString('data-test-tmonier-container-fill-gauge', $html);
        self::assertStringNotContainsString('surchargé', $html, 'Two boxes of 350 g are far below the 10 kg of a carton S.');
    }

    public function testClairesSharedListGivesAValidLoadPlanForTheFastenersRule(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $this->cartOf(self::CLAIRE, ['VISS-001' => 5, 'VISS-002' => 2], 'B2B');

        $html = $this->get($client, '/fr_FR/cart/');

        self::assertStringContainsString('Visserie : cartons remplis à 20 %', $html);
        self::assertStringNotContainsString('surchargé', $html);
        self::assertStringNotContainsString('tmonier-container-fill-checkout-reason', $html, 'Seven boxes fill one carton beyond 20 %: the rule is met.');
        self::assertDoesNotMatchRegularExpression('/<button[^>]*\bdisabled\b[^>]*data-test-cart-checkout-button/', $html);
    }

    public function testTheSharedListHoldsBoxesNotPieces(): void
    {
        $this->clientOf(self::CLAIRE);

        $quantity = fn (string $sku): int => $this->intValue('SELECT wp.quantity FROM sylius_wishlist_product wp JOIN sylius_product_variant v ON v.id = wp.variant_id WHERE v.code = ?', [$sku]);

        self::assertSame(5, $quantity('VISS-001'));
        self::assertSame(2, $quantity('VISS-002'));
    }

    public function testTheB2cChannelHasNoRuleNoGaugeAndNoBlock(): void
    {
        $client = $this->clientOf(self::LEA);
        $client->getCookieJar()->set(new Cookie(DemoChannelContext::COOKIE, DemoChannelContext::B2C));
        $this->cartOf(self::LEA, ['EMBA-001' => 10], 'B2C');

        $html = $this->get($client, '/fr_FR/cart/');

        self::assertStringContainsString('data-test-cart-item-product-row', $html, 'The B2C cart renders its line.');
        self::assertStringNotContainsString('data-test-tmonier-container-fill-gauge', $html);
        self::assertStringNotContainsString('tmonier-container-fill-checkout-reason', $html);
        self::assertSame(0, $this->intValue("SELECT COUNT(*) FROM tmonier_container_rule r JOIN sylius_channel c ON c.id = r.channel_id WHERE c.code = 'B2C'"));
    }

    public function testACompleteTransitionIsRefusedForAPartialPalletAndAcceptedForAFullOne(): void
    {
        $this->clientOf(self::CLAIRE);

        $partial = $this->cartOf(self::CLAIRE, ['EMBA-001' => 10], 'B2B');
        self::assertFalse($this->canComplete($partial), 'The workflow guard refuses a partial pallet.');

        $full = $this->cartOf(self::CLAIRE, ['EMBA-008' => 9], 'B2B');
        self::assertTrue($this->canComplete($full), 'A full EUR pallet meets the rule.');

        $byLayers = $this->cartOf(self::CLAIRE, ['EMBA-001' => 117], 'B2B');
        self::assertTrue($this->canComplete($byLayers), '13 layers of 9 cartons fill the EUR pallet planned by layers.');
    }

    public function testAPlacedOrderCarriesItsLoadPlanInTheAdministration(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $order = $this->cartOf(self::CLAIRE, ['EMBA-008' => 9], 'B2B');
        $this->prepareCheckout($order);
        $this->forgetNextInvoiceFile();
        $this->stateMachine()->apply($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_COMPLETE);
        $this->entityManager()->flush();

        self::assertSame(1, $this->intValue('SELECT COUNT(*) FROM tmonier_container_order_load_plan WHERE order_id = ?', [$order->getId()]));

        $sophie = $this->entityManager()->getRepository(AdminUserInterface::class)->findOneBy(['emailCanonical' => 'sophie@fournitures-pro.example']);
        self::assertInstanceOf(AdminUserInterface::class, $sophie);
        $client->loginUser($sophie, 'admin');
        $html = $this->get($client, '/admin/orders/' . $order->getId());
        self::assertStringContainsString('data-test-tmonier-container-fill-load-plan', $html);
        self::assertStringNotContainsString('data-test-tmonier-container-fill-no-load-plan', $html);
    }

    /**
     * The invoice PDF is a file: it is not rolled back with the database, so a number reused by the rolled back
     * orders of earlier runs would collide with it. The file of the next invoice number is cleared, and removed at the end.
     */
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

    public function testThePluginsPageDescribesTheDemoJourneyInBothLanguages(): void
    {
        $client = $this->clientOf(self::CLAIRE);

        foreach (['fr_FR' => 'se vendent à la boîte', 'en_US' => 'are sold by the box'] as $locale => $sentence) {
            $html = html_entity_decode($this->get($client, '/' . $locale . '/plugins'), \ENT_QUOTES);

            self::assertStringContainsString('EMBA-001', $html, $locale);
            self::assertStringContainsString($sentence, $html, $locale);
            self::assertStringContainsString(self::CLAIRE, $html, $locale);
        }
    }

    private function canComplete(OrderInterface $order): bool
    {
        $this->prepareCheckout($order);

        return $this->stateMachine()->can($order, OrderCheckoutTransitions::GRAPH, OrderCheckoutTransitions::TRANSITION_COMPLETE);
    }

    /** Address, shipping and payment of the checkout, up to the step before "complete". */
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

    private function stateMachine(): StateMachineInterface
    {
        $machine = self::getContainer()->get('sylius_abstraction.state_machine');
        self::assertInstanceOf(StateMachineInterface::class, $machine);

        return $machine;
    }

    private function clientOf(string $email): KernelBrowser
    {
        $client = self::createClient();
        $client->disableReboot();
        $this->client = $client;
        $this->db()->beginTransaction();
        $customer = $this->customer($email);
        $user = $customer->getUser();
        self::assertNotNull($user, $email . ' must have an account (demo fixtures).');
        $client->loginUser($user, 'shop');

        return $client;
    }

    private function get(KernelBrowser $client, string $url): string
    {
        $client->request('GET', $url);
        self::assertResponseIsSuccessful($url);

        return (string) $client->getResponse()->getContent();
    }

    /** @param array<string, int> $lines */
    private function cartOf(string $email, array $lines, string $channelCode): OrderInterface
    {
        $container = self::getContainer();
        $channel = $this->entityManager()->getRepository(ChannelInterface::class)->findOneBy(['code' => $channelCode]);
        self::assertInstanceOf(ChannelInterface::class, $channel);
        $orderFactory = $container->get('sylius.factory.order');
        self::assertInstanceOf(FactoryInterface::class, $orderFactory);

        $order = $orderFactory->createNew();
        self::assertInstanceOf(OrderInterface::class, $order);
        $order->setChannel($channel);
        $order->setCurrencyCode('EUR');
        $order->setLocaleCode('fr_FR');
        $order->setCustomerWithAuthorization($this->customer($email));
        foreach ($lines as $sku => $quantity) {
            $order->addItem($this->orderItem($this->variant($sku), $quantity));
        }
        $processor = $container->get('sylius.order_processing.order_processor');
        self::assertInstanceOf(OrderProcessorInterface::class, $processor);
        $processor->process($order);
        // Sylius shows the latest cart (createdAt DESC, to the second): a cart of the same customer leaked by
        // another test in the same second would tie with this one. Rolled back with the transaction.
        $order->setCreatedAt(new \DateTime('+1 hour'));
        $this->entityManager()->persist($order);
        $this->entityManager()->flush();

        return $order;
    }

    private function orderItem(ProductVariantInterface $variant, int $quantity): OrderItemInterface
    {
        $container = self::getContainer();
        $factory = $container->get('sylius.factory.order_item');
        $modifier = $container->get('sylius.modifier.order_item_quantity');
        self::assertInstanceOf(FactoryInterface::class, $factory);
        self::assertInstanceOf(OrderItemQuantityModifierInterface::class, $modifier);

        $item = $factory->createNew();
        self::assertInstanceOf(OrderItemInterface::class, $item);
        $item->setVariant($variant);
        $modifier->modify($item, $quantity);

        return $item;
    }

    private function variant(string $sku): ProductVariantInterface
    {
        $variant = $this->entityManager()->getRepository(ProductVariantInterface::class)->findOneBy(['code' => $sku]);
        self::assertInstanceOf(ProductVariantInterface::class, $variant, $sku . ' must exist (demo fixtures).');

        return $variant;
    }

    private function customer(string $email): CustomerInterface
    {
        $customer = $this->entityManager()->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => $email]);
        self::assertInstanceOf(CustomerInterface::class, $customer, $email . ' must exist (demo fixtures).');

        return $customer;
    }

    private function entityManager(): EntityManagerInterface
    {
        $em = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $em);

        return $em;
    }
}
