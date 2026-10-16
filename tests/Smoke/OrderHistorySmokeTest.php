<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use App\Demo\Order\DemoOrderPlan;
use App\Order\OrderPoReferenceAwareInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\InvoicingPlugin\Entity\InvoiceInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyOrderInterface;

/**
 * US-011: order history and invoices. Run against a database loaded with the `demo` fixture suite
 * (12 orders: Acme 6, TechParts 3, GlobalMaint 3; one invoice per order except the order waiting for an approval).
 */
final class OrderHistorySmokeTest extends WebTestCase
{
    private const COMPANIES_WITH_HISTORY = ['ACME_IND', 'TECHPARTS', 'GLOBALMAINT'];

    public function testEverySeededCompanyHasOrdersInAtLeastThreeStates(): void
    {
        self::bootKernel();
        $em = $this->entityManager();

        foreach (self::COMPANIES_WITH_HISTORY as $code) {
            $orders = $this->companyOrders($em, $code);
            self::assertNotSame([], $orders, sprintf('The company %s has no order after the fixtures.', $code));

            $states = array_unique(array_map(
                static fn (OrderInterface $order): string => implode('/', [$order->getState(), $order->getPaymentState(), $order->getShippingState()]),
                $orders,
            ));
            self::assertGreaterThanOrEqual(3, count($states), sprintf('The company %s has fewer than 3 order states: %s.', $code, implode(', ', $states)));
        }
    }

    public function testTheHistoryHasItsPlannedOrdersWithTheirPoReference(): void
    {
        self::bootKernel();
        $em = $this->entityManager();

        foreach (DemoOrderPlan::all() as $spec) {
            $order = $em->getRepository(OrderInterface::class)->findOneBy(['poReference' => $spec->poReference]);
            self::assertInstanceOf(OrderInterface::class, $order, sprintf('Demo order %s is missing.', $spec->poReference));
        }
        self::assertCount(count(DemoOrderPlan::all()), $this->placedOrders($em));
    }

    public function testEveryOrderHasItsInvoiceExceptTheOneWaitingForAnApproval(): void
    {
        self::bootKernel();
        $em = $this->entityManager();

        $without = [];
        foreach ($this->placedOrders($em) as $order) {
            if (null === $this->invoiceOf($em, $order) && $order instanceof OrderPoReferenceAwareInterface) {
                $without[] = (string) $order->getPoReference();
            }
        }

        self::assertSame(['ACME-PO-2026-0634'], $without, 'Only the order waiting for its approval has no invoice yet.');
    }

    public function testClaireSeesHerOrdersAndDownloadsAFacturXInvoice(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');

        $client->request('GET', '/fr_FR/account/orders/');
        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        foreach (['ACME-PO-2026-0412', 'ACME-PO-2026-0458', 'ACME-PO-2026-0547', 'ACME-PO-2026-0634'] as $reference) {
            self::assertStringContainsString($this->numberOf($client, $reference), $content, $reference . ' must be in Claire\'s orders.');
        }
        self::assertStringNotContainsString($this->numberOf($client, 'TP-2026-117'), $content);

        $invoice = $this->invoiceOf($this->entityManager($client), $this->orderOf($client, 'ACME-PO-2026-0412'));
        self::assertInstanceOf(InvoiceInterface::class, $invoice);
        $client->request('GET', '/fr_FR/invoices/' . $invoice->id() . '/download');
        self::assertResponseIsSuccessful();
        $pdf = (string) $client->getResponse()->getContent();
        self::assertStringStartsWith('%PDF', $pdf);
        self::assertStringContainsString('factur-x.xml', $pdf, 'The invoice is a Factur-X PDF.');
    }

    public function testTheCompanyAdminSeesTheCompanyOrders(): void
    {
        $client = self::createClient();
        $this->login($client, 'isabelle@acme.example');

        $client->request('GET', '/fr_FR/account/company/orders');
        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString($this->numberOf($client, 'ACME-PO-2026-0634'), $content);
        self::assertStringNotContainsString($this->numberOf($client, 'TP-2026-117'), $content);
    }

    public function testAMemberOfAnotherCompanyDoesNotSeeTheOrdersOfAcme(): void
    {
        $client = self::createClient();
        $this->login($client, 'marc@techparts.example');
        $acmeNumber = $this->numberOf($client, 'ACME-PO-2026-0412');
        $acmeInvoice = $this->invoiceOf($this->entityManager($client), $this->orderOf($client, 'ACME-PO-2026-0412'));
        self::assertInstanceOf(InvoiceInterface::class, $acmeInvoice);

        $client->request('GET', '/fr_FR/account/orders/');
        self::assertResponseIsSuccessful();
        self::assertStringNotContainsString($acmeNumber, (string) $client->getResponse()->getContent());

        $client->request('GET', '/fr_FR/account/orders/' . $acmeNumber);
        self::assertResponseStatusCodeSame(404);

        $client->request('GET', '/fr_FR/invoices/' . $acmeInvoice->id() . '/download');
        self::assertResponseStatusCodeSame(403);
    }

    public function testTheCompanyViewOfAnotherCompanyDoesNotListTheOrdersOfAcme(): void
    {
        $client = self::createClient();
        $this->login($client, 'nathalie@techparts.example');

        $client->request('GET', '/fr_FR/account/company/orders');
        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString($this->numberOf($client, 'TP-2026-117'), $content);
        self::assertStringNotContainsString($this->numberOf($client, 'ACME-PO-2026-0412'), $content);
    }

    private function entityManager(?KernelBrowser $client = null): EntityManagerInterface
    {
        $container = null === $client ? self::getContainer() : $client->getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.orm.entity_manager');

        return $em;
    }

    /**
     * Other smoke tests leave carts behind (PunchOut sessions, wishlist to cart): only placed orders make the history.
     *
     * @return list<OrderInterface>
     */
    private function placedOrders(EntityManagerInterface $em): array
    {
        return array_values(array_filter(
            $em->getRepository(OrderInterface::class)->findAll(),
            // Other smoke tests leave carts and PunchOut orders (PunchOutSimulatorSmokeTest end to end) behind.
            static fn (OrderInterface $order): bool => OrderInterface::STATE_CART !== $order->getState() &&
                !str_starts_with((string) $order->getNotes(), 'PunchOut purchase order '),
        ));
    }

    /** @return list<OrderInterface> */
    private function companyOrders(EntityManagerInterface $em, string $companyCode): array
    {
        $company = $em->getRepository(CompanyInterface::class)->findOneBy(['code' => $companyCode]);
        self::assertInstanceOf(CompanyInterface::class, $company, $companyCode . ' must exist.');

        $orders = [];
        foreach ($em->getRepository(CompanyOrderInterface::class)->findBy(['company' => $company]) as $companyOrder) {
            $order = $companyOrder->getOrder();
            if ($order instanceof OrderInterface) {
                $orders[] = $order;
            }
        }

        return $orders;
    }

    private function orderOf(KernelBrowser $client, string $poReference): OrderInterface
    {
        $order = $this->entityManager($client)->getRepository(OrderInterface::class)->findOneBy(['poReference' => $poReference]);
        self::assertInstanceOf(OrderInterface::class, $order, $poReference . ' must exist.');

        return $order;
    }

    private function numberOf(KernelBrowser $client, string $poReference): string
    {
        return (string) $this->orderOf($client, $poReference)->getNumber();
    }

    private function invoiceOf(EntityManagerInterface $em, OrderInterface $order): ?InvoiceInterface
    {
        $invoice = $em->getRepository(InvoiceInterface::class)->findOneBy(['order' => $order]);

        return $invoice instanceof InvoiceInterface ? $invoice : null;
    }

    private function login(KernelBrowser $client, string $email): void
    {
        $customer = $this->entityManager($client)->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => $email]);
        self::assertInstanceOf(CustomerInterface::class, $customer, $email . ' must exist (demo fixtures).');
        $user = $customer->getUser();
        self::assertNotNull($user);

        $client->loginUser($user, 'shop');
    }
}
