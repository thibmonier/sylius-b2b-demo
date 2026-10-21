<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use App\Demo\Check\QuoteNumberingCheck;
use App\Demo\DataFixtures\CompanyWishlistDemoFixture;
use App\Demo\Order\DemoOrderPlan;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tmonier\SyliusAdminOrderCreationPlugin\Entity\AdminOrderCreationInterface;
use Tmonier\SyliusAiCatalogCopilotPlugin\Entity\AiSuggestionInterface;
use Tmonier\SyliusAuditLogPlugin\Entity\AuditLogEntryInterface;
use Tmonier\SyliusCompanyAccountsPlugin\Entity\CompanyInterface;
use Tmonier\SyliusQuoteRequestPlugin\Entity\QuoteRequestInterface;
use Tmonier\SyliusTradeCreditPlugin\Entity\OrderPaymentTermsInterface;
use Tmonier\SyliusTradeCreditPlugin\Entity\TradeCreditAccountInterface;

/**
 * US-096 / US-097: every key screen of the demo shows a living state after the reset (audit D3). Run against a
 * database loaded with the `demo` fixture suite. Each check names its screen, so a screen left empty by a fixture is
 * reported by name (see {@see self::testNoKeyScreenIsLeftEmpty()}).
 */
final class LiveDemoDataSmokeTest extends WebTestCase
{
    public function testClaireStartsWithTheAtelierListInHerCart(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');

        $client->request('GET', '/fr_FR/cart/');
        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Casque de chantier MSA V-Gard blanc', $content);
        self::assertStringContainsString('Gants Ansell HyFlex', $content);

        $client->request('GET', '/en_US/cart/');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('MSA V-Gard Hard Hat White', (string) $client->getResponse()->getContent());
    }

    public function testTheCartOfClaireHoldsExactlyTheAtelierQuantities(): void
    {
        self::bootKernel();
        // The tests that change her cart (PunchOut, wishlist to cart…) run in a rolled back transaction: exact quantities.
        $carts = $this->carts('claire@acme.example');
        self::assertCount(1, $carts, 'Claire has one cart.');
        $quantities = [];
        foreach ($carts[0]->getItems() as $item) {
            $quantities[(string) $item->getVariant()?->getCode()] = $item->getQuantity();
        }

        self::assertEquals(CompanyWishlistDemoFixture::ITEMS, $quantities, 'Her cart holds the Atelier list, nothing more.');
        self::assertFalse($carts[0]->isCreatedByGuest(), 'The shop resumes the cart of a customer only when it was not created by a guest.');
    }

    public function testPaulHasAnOrderToApprove(): void
    {
        $client = self::createClient();
        $this->login($client, 'paul@acme.example');

        $client->request('GET', '/fr_FR/account/company/approvals');
        self::assertResponseIsSuccessful();
        $order = $this->entityManager($client)->getRepository(OrderInterface::class)->findOneBy(['poReference' => 'ACME-PO-2026-0634']);
        self::assertInstanceOf(OrderInterface::class, $order);
        self::assertStringContainsString((string) $order->getNumber(), (string) $client->getResponse()->getContent());
    }

    public function testClaireSeesANegotiatedQuoteNumberedWithTheCurrentYear(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');

        $client->request('GET', '/fr_FR/account/quotes');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString(sprintf('Q-%s-001', date('Y')), (string) $client->getResponse()->getContent());

        $quote = $this->quote(sprintf('Q-%s-001', date('Y')));
        self::assertSame('proposed', $quote->getStatus());
        self::assertGreaterThanOrEqual(4, $quote->getMessages()->count(), 'A negotiation: request, proposal, counter-proposal and answer.');
        $authors = array_unique(array_map(static fn ($message): string => $message->getAuthorType(), $quote->getMessages()->toArray()));
        sort($authors);
        self::assertSame(['admin', 'customer'], $authors);
    }

    public function testQuoteNumbersCarryTheYearOfTheirDate(): void
    {
        self::bootKernel();
        $quotes = array_map(
            static fn (QuoteRequestInterface $quote): array => ['number' => (string) $quote->getNumber(), 'createdAt' => $quote->getCreatedAt() ?? new \DateTimeImmutable()],
            $this->entityManager()->getRepository(QuoteRequestInterface::class)->findAll(),
        );

        self::assertGreaterThanOrEqual(3, count($quotes));
        self::assertSame([], (new QuoteNumberingCheck())->violations($quotes, new \DateTimeImmutable()));
    }

    public function testTheCreditAccountOfAcmeShowsOpenItemsAndAnOverdueOne(): void
    {
        self::bootKernel();
        $account = $this->creditAccount('ACME_IND');
        $open = $this->openItems($account);

        self::assertGreaterThanOrEqual(2, count($open));
        self::assertLessThanOrEqual(3, count($open));
        $overdue = array_filter($open, static fn (OrderPaymentTermsInterface $terms): bool => $terms->isOverdue(new \DateTimeImmutable()));
        self::assertCount(1, $overdue, 'Exactly one open item is overdue.');
        $ratio = $account->getCommittedAmount() / $account->getCreditLimit();
        self::assertGreaterThan(0.4, $ratio, 'The gauge is visibly filled.');
        self::assertLessThan(1.0, $ratio, 'Acme stays below its limit.');
    }

    public function testBuildCoIsAboveItsCreditLimit(): void
    {
        self::bootKernel();
        $account = $this->creditAccount('BUILDCO');

        self::assertTrue($account->isAllowExceed());
        self::assertGreaterThan($account->getCreditLimit(), $account->getCommittedAmount());
    }

    public function testIsabelleOpensTheStatementOfAcmeWithItsOverdueItem(): void
    {
        $client = self::createClient();
        $this->login($client, 'isabelle@acme.example');
        $overdue = $this->entityManager($client)->getRepository(OrderInterface::class)->findOneBy(['poReference' => 'ACME-PO-2026-0568']);
        self::assertInstanceOf(OrderInterface::class, $overdue);

        $client->request('GET', '/fr_FR/account/trade-credit');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString((string) $overdue->getNumber(), (string) $client->getResponse()->getContent());
    }

    public function testNoKeyScreenIsLeftEmpty(): void
    {
        self::bootKernel();
        $em = $this->entityManager();
        $empty = [];

        if ([] === $this->carts('claire@acme.example')) {
            $empty[] = 'Panier de Claire';
        }
        if (null === $em->getRepository(OrderInterface::class)->findOneBy(['poReference' => 'ACME-PO-2026-0634'])) {
            $empty[] = 'Approbations de Paul';
        }
        if ([] === $em->getRepository(QuoteRequestInterface::class)->findAll()) {
            $empty[] = 'Mes devis';
        }
        if ([] === $this->openItems($this->creditAccount('ACME_IND'))) {
            $empty[] = 'Encours d\'Acme';
        }
        if ([] === $this->drafts()) {
            $empty[] = 'Commandes en préparation (admin)';
        }
        if (count($this->demoSuggestions()) < 5) {
            $empty[] = 'Suggestions IA (admin)';
        }
        if ([] === $em->getRepository(AuditLogEntryInterface::class)->findAll()) {
            $empty[] = 'Journal d\'audit (admin)';
        }

        self::assertSame([], $empty, 'Screens left empty after the reset: ' . implode(', ', $empty));
    }

    public function testTheAdminHasDraftOrdersInPreparation(): void
    {
        self::bootKernel();
        $drafts = $this->drafts();

        self::assertGreaterThanOrEqual(2, count($drafts));
        foreach ($drafts as $draft) {
            self::assertNotSame(0, $draft->getOrder()?->getItems()->count(), 'A draft holds items.');
            self::assertNotNull($draft->getAdminUser());
        }
    }

    public function testTheCopilotHasAtLeastFiveSuggestionsMarkedAsDemonstration(): void
    {
        self::bootKernel();
        $suggestions = $this->demoSuggestions();

        self::assertGreaterThanOrEqual(5, count($suggestions));
        $statuses = array_unique(array_map(static fn (AiSuggestionInterface $suggestion): string => $suggestion->getStatus(), $suggestions));
        self::assertContains(AiSuggestionInterface::STATUS_PENDING, $statuses);
    }

    public function testTheAuditLogShowsBusinessEventsOfTheTeam(): void
    {
        self::bootKernel();
        // Other tests of the suite run the reset command, which is not "ignored" outside the console: only the entries of
        // the team (admin actor and source) are the business events of the fixtures.
        /** @var list<AuditLogEntryInterface> $entries */
        $entries = $this->entityManager()->getRepository(AuditLogEntryInterface::class)->findBy(['actorType' => AuditLogEntryInterface::ACTOR_ADMIN, 'source' => AuditLogEntryInterface::SOURCE_ADMIN]);

        self::assertGreaterThanOrEqual(5, count($entries));
        self::assertLessThanOrEqual(15, count($entries), 'A handful of events, not a flood.');
        $resources = [];
        foreach ($entries as $entry) {
            self::assertNotSame('app:demo:reset', $entry->getRoute());
            self::assertLessThanOrEqual(new \DateTimeImmutable(), $entry->getCreatedAt());
            $resources[(string) $entry->getResourceType()] = true;
        }
        self::assertGreaterThanOrEqual(3, count($resources), 'Several kinds of resources: prices, orders, customers…');
    }

    public function testTheCoreHistoryOfAcmeIsStillSpreadOverSixMonths(): void
    {
        self::bootKernel();
        $dates = [];
        foreach (DemoOrderPlan::core() as $spec) {
            if (str_ends_with($spec->customerEmail, '@acme.example')) {
                $order = $this->entityManager()->getRepository(OrderInterface::class)->findOneBy(['poReference' => $spec->poReference]);
                self::assertInstanceOf(OrderInterface::class, $order, $spec->poReference);
                $dates[] = $order->getCheckoutCompletedAt() ?? new \DateTimeImmutable();
            }
        }

        self::assertGreaterThanOrEqual(6, count($dates));
        self::assertLessThanOrEqual(8, count($dates));
        self::assertGreaterThan(150, (new \DateTimeImmutable())->diff(min([new \DateTimeImmutable(), ...$dates]))->days);
    }

    /** @return list<OrderInterface> */
    private function carts(string $email): array
    {
        /** @var list<OrderInterface> $carts */
        $carts = $this->entityManager()->createQuery(
            'SELECT o FROM ' . OrderInterface::class . ' o JOIN o.customer c WHERE c.emailCanonical = :email AND o.state = :state',
        )->setParameter('email', $email)->setParameter('state', OrderInterface::STATE_CART)->getResult();

        return $carts;
    }

    private function quote(string $number): QuoteRequestInterface
    {
        $quote = $this->entityManager()->getRepository(QuoteRequestInterface::class)->findOneBy(['number' => $number]);
        self::assertInstanceOf(QuoteRequestInterface::class, $quote, $number . ' must exist.');

        return $quote;
    }

    private function creditAccount(string $companyCode): TradeCreditAccountInterface
    {
        $company = $this->entityManager()->getRepository(CompanyInterface::class)->findOneBy(['code' => $companyCode]);
        self::assertInstanceOf(CompanyInterface::class, $company, $companyCode . ' must exist.');
        $account = $this->entityManager()->getRepository(TradeCreditAccountInterface::class)->findOneBy(['ownerType' => 'company', 'ownerIdentifier' => (string) $company->getId()]);
        self::assertInstanceOf(TradeCreditAccountInterface::class, $account, $companyCode . ' must have a credit account.');

        return $account;
    }

    /** @return list<OrderPaymentTermsInterface> */
    private function openItems(TradeCreditAccountInterface $account): array
    {
        /** @var list<OrderPaymentTermsInterface> $open */
        $open = $this->entityManager()->getRepository(OrderPaymentTermsInterface::class)->findBy(['account' => $account, 'state' => OrderPaymentTermsInterface::STATE_RESERVED]);

        return $open;
    }

    /** @return list<AdminOrderCreationInterface> */
    private function drafts(): array
    {
        /** @var list<AdminOrderCreationInterface> $drafts */
        $drafts = $this->entityManager()->getRepository(AdminOrderCreationInterface::class)->findBy(['completedAt' => null]);

        return $drafts;
    }

    /** @return list<AiSuggestionInterface> */
    private function demoSuggestions(): array
    {
        /** @var list<AiSuggestionInterface> $suggestions */
        $suggestions = $this->entityManager()->getRepository(AiSuggestionInterface::class)->findBy(['generator' => 'demo']);

        return $suggestions;
    }

    private function entityManager(?KernelBrowser $client = null): EntityManagerInterface
    {
        $container = null === $client ? self::getContainer() : $client->getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine.orm.entity_manager');

        return $em;
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
