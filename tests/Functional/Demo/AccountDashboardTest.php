<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Tests\Support\IsolatesSharedAccounts;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-199: the page "My account" of the B2B channel, in the continuation of the home (mock-up validated by Thibaut, see the
 * decisions register of sprint 30). Run against a database loaded with the `demo` suite. Claire is a buyer with an order
 * limit, Paul an approver with an order waiting for him, Isabelle the administrator, Nathalie the administrator of a company
 * without any order. Léa shops on the consumer channel and keeps the theme of the shop.
 */
final class AccountDashboardTest extends WebTestCase
{
    use IsolatesSharedAccounts;

    private const CLAIRE = 'claire@acme.example';

    private const PAUL = 'paul@acme.example';

    private const ISABELLE = 'isabelle@acme.example';

    private const NATHALIE = 'nathalie@techparts.example';

    private const LEA = 'lea@demo.example';

    private const DASHBOARD = '/fr_FR/account/dashboard';

    public function testClaireIsGreetedWithHerRoleAndHerCompany(): void
    {
        $page = $this->page(self::CLAIRE);

        self::assertSame('Bonjour Claire', $this->text($page->filter('h1')));
        self::assertSame('Acheteur', $this->text($page->filter('[data-test-b2b-account-role]')));
        $identity = $this->text($page->filter('[data-test-b2b-account-identity]'));
        self::assertStringContainsString('Acme Industries', $identity);
        self::assertStringContainsString(self::CLAIRE, $identity);
        self::assertStringContainsString('compte vérifié', $identity);
        self::assertCount(1, $page->filter('main a[href$="/account/profile/edit"]'));
        self::assertCount(1, $page->filter('main a[href$="/account/change-password"]'));
    }

    public function testClaireSeesFirstWhatIsWaitingForHer(): void
    {
        $page = $this->page(self::CLAIRE);

        self::assertSame('À suivre', $this->text($page->filter('[data-test-b2b-account-todo] h2')));
        $items = $page->filter('[data-test-b2b-account-todo-item]');
        self::assertGreaterThanOrEqual(2, $items->count());
        self::assertStringContainsString('en attente d\'approbation', $this->text($items->eq(0)), 'her order above the limit comes first');
        self::assertStringContainsString('limite de commande', $this->text($items->eq(0)));
        self::assertStringContainsString('Paul', $this->text($items->eq(0)), 'the approver who can decide it is named');
        self::assertStringContainsString('devis', $this->text($items->eq(1)));
        self::assertStringContainsString('Q-', $this->text($items->eq(1)));
    }

    public function testClaireReadsHerLatestOrdersWithTheStateInPlainWords(): void
    {
        $page = $this->page(self::CLAIRE);

        $rows = $page->filter('[data-test-b2b-account-orders] [data-test-b2b-account-order]');
        self::assertGreaterThanOrEqual(1, $rows->count());
        self::assertLessThanOrEqual(5, $rows->count(), 'five orders at most');
        self::assertSame('En attente d\'approbation', $this->text($rows->eq(0)->filter('[data-test-b2b-account-order-state]')));
        $states = $rows->filter('[data-test-b2b-account-order-state]')->each(fn (Crawler $state): string => $this->text($state));
        foreach ($states as $state) {
            self::assertNotContains($state, ['new', 'fulfilled', 'cart', 'awaiting_payment', 'Nouveau', 'Complétée'], 'a state of the shop is turned into a sentence of the buyer');
        }
        self::assertMatchesRegularExpression('/Toutes mes commandes \(\d+\)/', $this->text($page->filter('[data-test-b2b-account-orders-all]')));
        self::assertStringContainsString('/account/orders', (string) $page->filter('[data-test-b2b-account-orders-all]')->attr('href'));
    }

    public function testEachOrderCanBeOrderedAgainWithAPostedForm(): void
    {
        $page = $this->page(self::CLAIRE);

        $rows = $page->filter('[data-test-b2b-account-order]');
        $forms = $page->filter('[data-test-b2b-account-order] form[data-test-b2b-account-reorder]');
        self::assertSame($rows->count(), $forms->count(), 'one form per order');
        self::assertSame('post', strtolower((string) $forms->eq(0)->attr('method')));
        self::assertStringContainsString('/reorder', (string) $forms->eq(0)->attr('action'));
        self::assertNotSame('', (string) $forms->eq(0)->filter('input[name="_csrf_token"]')->attr('value'));
        self::assertStringContainsString('Commander à nouveau', $this->text($forms->eq(0)));
    }

    public function testTheOrdersTableScrollsInsideItsOwnRegionOnAPhone(): void
    {
        $page = $this->page(self::CLAIRE);

        $region = $page->filter('[data-test-b2b-account-orders] .b2b-account__scroll');
        self::assertCount(1, $region);
        self::assertSame('0', $region->attr('tabindex'));
        self::assertSame('region', $region->attr('role'));
        self::assertNotSame('', (string) $region->attr('aria-label'));
        self::assertGreaterThanOrEqual(4, $region->filter('thead th[scope="col"]')->count());
    }

    public function testThereAreExactlyFourShortcutsAndNoneForAPageClaireCannotOpen(): void
    {
        $page = $this->page(self::CLAIRE);

        $shortcuts = $page->filter('[data-test-b2b-account-quick] [data-test-b2b-account-quick-item]');
        self::assertCount(4, $shortcuts);
        self::assertStringContainsString('/quick-order', (string) $shortcuts->eq(0)->attr('href'));
        self::assertStringContainsString('company-wishlists', (string) $shortcuts->eq(1)->attr('href'));
        self::assertStringContainsString('Atelier', $this->text($shortcuts->eq(1)));
        self::assertStringContainsString('/quote-request', (string) $shortcuts->eq(2)->attr('href'));
        self::assertStringContainsString('/account/equipment', (string) $shortcuts->eq(3)->attr('href'), 'a buyer gets her equipment where an administrator gets the purchasing dashboard');
        $links = implode(' ', $page->filter('main a, [data-test-b2b-account-nav] a')->each(static fn (Crawler $a): string => (string) $a->attr('href')));
        foreach (['/account/company/approvals', '/account/company/limits', '/account/company/insights'] as $denied) {
            self::assertStringNotContainsString($denied, $links, 'no link to a page that would answer a refusal');
        }
    }

    public function testClaireSeesCompanyCreditListsAndQuotesBesideHerOrders(): void
    {
        $page = $this->page(self::CLAIRE);

        $company = $this->text($page->filter('[data-test-b2b-account-company]'));
        self::assertStringContainsString('Acme Industries', $company);
        self::assertStringContainsString('Acheteur', $company);
        self::assertStringContainsString('Limite de commande', $company);
        self::assertStringContainsString('500,00', $company);
        $credit = $this->text($page->filter('[data-test-b2b-account-credit]'));
        self::assertMatchesRegularExpression('/disponibles sur 10 000,00 €/u', $credit);
        self::assertCount(1, $page->filter('[data-test-b2b-account-credit] a[href$="/account/trade-credit"]'));
        $lists = $this->text($page->filter('[data-test-b2b-account-lists]'));
        self::assertStringContainsString('Liste « Atelier »', $lists);
        self::assertStringContainsString('5 articles', $lists);
        self::assertStringContainsString('Q-', $lists);
        self::assertStringContainsString('proposition envoyée', $lists);
    }

    public function testThePaymentNoteTellsWhatIsOverdueOnTheCompanyAccount(): void
    {
        $page = $this->page(self::CLAIRE);

        $note = $this->text($page->filter('[data-test-b2b-account-credit-note]'));
        self::assertMatchesRegularExpression('/\d[\d  ]*,\d{2} €\s+échus/u', $note);
    }

    public function testThePendingApprovalOfPaulIsFirstAndCountedInTheMenu(): void
    {
        $page = $this->page(self::PAUL);

        self::assertSame('Approbateur', $this->text($page->filter('[data-test-b2b-account-role]')));
        self::assertSame('À faire', $this->text($page->filter('[data-test-b2b-account-todo] h2')));
        $first = $page->filter('[data-test-b2b-account-todo-item]')->eq(0);
        self::assertMatchesRegularExpression('/^1 commande à examiner/u', $this->text($first));
        self::assertStringContainsString('Claire Dupont', $this->text($first));
        self::assertStringContainsString('approvals', (string) $first->filter('a')->attr('href'));
        $entry = $page->filter('[data-test-b2b-account-nav] [data-test-b2b-account-nav-item="tmonier_approvals"]');
        self::assertCount(1, $entry);
        self::assertSame('1', $this->text($entry->filter('[data-test-b2b-account-nav-count]')));
        self::assertStringContainsString('Plafond d\'approbation', $this->text($page->filter('[data-test-b2b-account-company]')));
    }

    public function testPaulIsWarnedOfHisOwnOverdueOrder(): void
    {
        $page = $this->page(self::PAUL);

        $todo = $this->text($page->filter('[data-test-b2b-account-todo]'));
        self::assertStringContainsString('en retard de paiement', $todo);
        $states = $page->filter('[data-test-b2b-account-order-state]')->each(fn (Crawler $state): string => $this->text($state));
        self::assertContains('Paiement en retard', $states);
    }

    public function testIsabelleGetsTheShortcutsAndEntriesOfAnAdministrator(): void
    {
        $page = $this->page(self::ISABELLE);

        self::assertSame('Administrateur', $this->text($page->filter('[data-test-b2b-account-role]')));
        $shortcuts = $page->filter('[data-test-b2b-account-quick-item]');
        self::assertCount(4, $shortcuts);
        self::assertStringContainsString('/account/company/insights', (string) $shortcuts->eq(3)->attr('href'));
        $names = $page->filter('[data-test-b2b-account-nav-item]')->each(static fn (Crawler $item): string => (string) $item->attr('data-test-b2b-account-nav-item'));
        foreach (['tmonier_approvals', 'tmonier_purchase_limits', 'app_insights', 'tmonier_company'] as $entry) {
            self::assertContains($entry, $names);
        }
        self::assertStringContainsString('Dépense HT sur 12 mois', $this->text($page->filter('[data-test-b2b-account-company]')));
        self::assertStringContainsString('à régler', $this->text($page->filter('[data-test-b2b-account-todo]')), 'her order paid on account, with its due date');
    }

    public function testTheMenuIsGroupedInFourBlocksAndStaysOnOtherAccountPages(): void
    {
        foreach ([self::DASHBOARD, '/fr_FR/account/orders/', '/fr_FR/account/address-book/'] as $path) {
            $page = $this->page(self::ISABELLE, $path);

            $groups = $page->filter('[data-test-b2b-account-nav] [data-test-b2b-account-nav-group]')->each(static fn (Crawler $group): string => (string) $group->attr('data-test-b2b-account-nav-group'));
            self::assertSame(['purchases', 'company', 'profile', 'tools'], $groups, $path);
            self::assertCount(1, $page->filter('[data-test-b2b-account-nav] [aria-current="page"]'), $path);
        }
    }

    public function testNathalieHasAnAccountWithoutOrderAndSeesHowToStart(): void
    {
        $page = $this->page(self::NATHALIE);

        self::assertSame('Pour démarrer', $this->text($page->filter('[data-test-b2b-account-todo] h2')));
        self::assertStringContainsString('Vous n\'avez pas encore passé de commande', $this->text($page->filter('[data-test-b2b-account-orders]')));
        self::assertCount(0, $page->filter('[data-test-b2b-account-order]'));
        self::assertCount(0, $page->filter('[data-test-b2b-account-orders] table'));
        self::assertCount(1, $page->filter('[data-test-b2b-account-orders] a[href$="/quick-order"]'));
        self::assertStringContainsString('Aucune pièce ouverte', $this->text($page->filter('[data-test-b2b-account-credit-note]')));
        self::assertStringContainsString('Aucune liste d\'achat partagée', $this->text($page->filter('[data-test-b2b-account-lists]')));
        self::assertStringContainsString('aucun devis', $this->text($page->filter('[data-test-b2b-account-lists]')));
        self::assertStringContainsString('TechParts SAS', $this->text($page->filter('[data-test-b2b-account-company]')));
    }

    public function testTheEnglishPageUsesTheEnglishTexts(): void
    {
        $page = $this->page(self::CLAIRE, '/en_US/account/dashboard');

        self::assertSame('Hello Claire', $this->text($page->filter('h1')));
        self::assertSame('Buyer', $this->text($page->filter('[data-test-b2b-account-role]')));
        self::assertStringContainsString('My latest orders', $this->text($page->filter('[data-test-b2b-account-orders]')));
        self::assertStringContainsString('Pending approval', $this->text($page->filter('[data-test-b2b-account-order-state]')->eq(0)));
        self::assertStringContainsString('Order again', $this->text($page->filter('[data-test-b2b-account-reorder]')->eq(0)));
    }

    public function testTheConsumerChannelKeepsTheThemeOfTheShop(): void
    {
        $page = $this->page(self::LEA, self::DASHBOARD, 'B2C');

        self::assertCount(1, $page->filter('[data-test-customer-information]'), 'the core card of the dashboard');
        self::assertCount(0, $page->filter('[data-test-b2b-account-todo], [data-test-b2b-account-orders], [data-test-b2b-account-quick], [data-test-b2b-account-nav]'));
        self::assertStringContainsString('Mon compte', $this->text($page->filter('body')));
    }

    /** @return iterable<string, array{string}> */
    public static function otherAccountPages(): iterable
    {
        yield 'orders' => ['/fr_FR/account/orders/'];
        yield 'address book' => ['/fr_FR/account/address-book/'];
        yield 'profile' => ['/fr_FR/account/profile/edit'];
    }

    #[DataProvider('otherAccountPages')]
    public function testTheOtherAccountPagesCarryTheSameGroupedMenu(string $path): void
    {
        $page = $this->page(self::CLAIRE, $path);

        self::assertCount(1, $page->filter('[data-test-b2b-account-nav]'));
        self::assertGreaterThanOrEqual(3, $page->filter('[data-test-b2b-account-nav-group]')->count());
    }

    private function page(string $email, string $path = self::DASHBOARD, string $channel = 'B2B'): Crawler
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $this->isolateSharedAccounts($client);
        $this->login($client, $email);
        $client->request('GET', '/demo/channel/' . $channel);
        $page = $client->request('GET', $path);
        self::assertResponseIsSuccessful();

        return $page;
    }

    private function login(KernelBrowser $client, string $email): void
    {
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $em);
        $customer = $em->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => $email]);
        self::assertInstanceOf(CustomerInterface::class, $customer, $email . ' must exist (demo fixtures).');
        $user = $customer->getUser();
        self::assertNotNull($user);
        $client->loginUser($user, 'shop');
    }

    private function text(Crawler $node): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $node->text()));
    }
}
