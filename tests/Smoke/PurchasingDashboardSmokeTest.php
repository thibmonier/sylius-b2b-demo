<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-155: Isabelle (administrator of Acme) reads the purchasing dashboard of company-insights on the demo history.
 * Run against a database loaded with the `demo` fixture suite.
 */
final class PurchasingDashboardSmokeTest extends WebTestCase
{
    private const PAGE = '/fr_FR/account/company/insights';

    public function testTwelveMonthsOfPurchasesWithTheAugustTroughAndTheYearBefore(): void
    {
        $client = self::createClient();
        $this->login($client, 'isabelle@acme.example');

        $crawler = $client->request('GET', self::PAGE);
        self::assertResponseIsSuccessful();

        $rows = $crawler->filter('[data-test-tmonier-insights-monthly-table="EUR"] tbody tr');
        self::assertCount(12, $rows);
        $orders = $this->monthlyOrders($rows, 'monthly-orders');
        $complete = array_slice($orders, 0, 11, true);
        $august = array_filter($complete, static fn (int $count, string $month): bool => str_ends_with($month, '-08'), \ARRAY_FILTER_USE_BOTH);

        self::assertNotSame([], $august, 'August is one of the eleven complete months of a 12-month window (the current month apart).');
        self::assertSame(min([\PHP_INT_MAX, ...array_values($complete)]), min([\PHP_INT_MAX, ...array_values($august)]), 'August is the trough of the year.');
        self::assertGreaterThanOrEqual(2, array_sum($complete) - 11 * min([\PHP_INT_MAX, ...array_values($complete)]), 'The months are not all alike.');

        $previous = $this->monthlyOrders($rows, 'monthly-previous-orders');
        self::assertGreaterThanOrEqual(6, count(array_filter($previous)), 'At least six months of the year before to compare with.');
    }

    public function testAtLeastFourBuyersAndTenProductsInTheRankings(): void
    {
        $client = self::createClient();
        $this->login($client, 'isabelle@acme.example');

        $crawler = $client->request('GET', self::PAGE);

        self::assertGreaterThanOrEqual(4, $crawler->filter('[data-test-tmonier-insights-ranking="buyers-EUR"] [data-test-tmonier-insights-ranking-row]')->count());
        self::assertGreaterThanOrEqual(10, $crawler->filter('[data-test-tmonier-insights-ranking="products-EUR"] [data-test-tmonier-insights-ranking-row]')->count());
        self::assertStringContainsString('désactivé', $crawler->filter('[data-test-tmonier-insights-ranking="buyers-EUR"]')->text(), 'The buyer who left stays in the ranking, marked as deactivated.');
    }

    public function testTheOrdersOfTechPartsAreNotInTheRankingsOfAcme(): void
    {
        $client = self::createClient();
        $this->login($client, 'isabelle@acme.example');

        $content = $client->request('GET', self::PAGE)->filter('main')->text();
        foreach (['Marc', 'Lefebvre', 'Nathalie', 'Girard', 'TechParts', 'BuildCo', 'Antoine'] as $foreign) {
            self::assertStringNotContainsString($foreign, $content);
        }
    }

    public function testThePluginBadgeLinksToThePluginsPage(): void
    {
        $client = self::createClient();
        $this->login($client, 'isabelle@acme.example');

        $crawler = $client->request('GET', self::PAGE);

        self::assertCount(1, $crawler->filter('[data-demo-plugin-badge="sylius-company-insights-plugin"] a[href*="/plugins#plugin-sylius-company-insights-plugin"]'));
    }

    public function testAcmeBuyersWithoutTheAdministratorRoleGetNoDashboard(): void
    {
        $client = self::createClient();
        $this->login($client, 'claire@acme.example');

        $client->request('GET', self::PAGE);

        self::assertResponseStatusCodeSame(403);
    }

    public function testPurchasesAreSplitPerDeliverySiteOfTheCompany(): void
    {
        $client = self::createClient();
        $this->login($client, 'isabelle@acme.example');

        $block = $client->request('GET', self::PAGE)->filter('[data-test-tmonier-insights-breakdown="sites-EUR"]');

        self::assertCount(1, $block);
        $labels = $block->filter('[data-test-tmonier-insights-breakdown-row] th')->each(static fn (Crawler $cell): string => trim($cell->text()));
        foreach (['Usine de Lyon', 'Entrepôt de Saint-Priest', 'Atelier de Grenoble'] as $site) {
            self::assertContains($site, $labels);
        }
    }

    public function testPurchasesAreSplitPerCostCentreWithoutTheCostCentresOfOtherCompanies(): void
    {
        $client = self::createClient();
        $this->login($client, 'isabelle@acme.example');

        $block = $client->request('GET', self::PAGE)->filter('[data-test-tmonier-insights-breakdown="cost-center-EUR"]');

        self::assertCount(1, $block);
        self::assertStringContainsString('Centre de coût', $block->text());
        $labels = $block->filter('[data-test-tmonier-insights-breakdown-row] th')->each(static fn (Crawler $cell): string => trim($cell->text()));
        sort($labels);
        self::assertSame(['ATELIER-01', 'MAINT-02'], $labels);
    }

    public function testTheExportCarriesThePurchaseOrderReferenceAndTheCostCentreOfAcmeOnly(): void
    {
        $client = self::createClient();
        $this->login($client, 'isabelle@acme.example');

        $client->request('GET', '/fr_FR/account/company/insights/export?period=last_12_months');

        self::assertResponseIsSuccessful();
        $csv = (string) $client->getInternalResponse()->getContent();
        self::assertStringStartsWith("\xEF\xBB\xBF", $csv);
        self::assertStringContainsString('ACME-PO-H-', $csv, 'The PO reference comes from the order.');
        self::assertStringContainsString('ATELIER-01', $csv);
        self::assertStringContainsString('MAINT-02', $csv);
        foreach (['TP-2026', 'BC-2026', 'GM-8', 'SITE-LYON'] as $foreign) {
            self::assertStringNotContainsString($foreign, $csv);
        }
    }

    public function testTheApprovalsAndCreditWidgetsAreOnTheDashboardOfTheAdministrator(): void
    {
        $client = self::createClient();
        $this->login($client, 'isabelle@acme.example');

        $crawler = $client->request('GET', self::PAGE);

        foreach (['approvals', 'credit'] as $widget) {
            $node = $crawler->filter('[data-test-tmonier-insights-widget="' . $widget . '"]');
            self::assertCount(1, $node, $widget . ' widget');
            self::assertCount(0, $node->filter('[data-test-tmonier-insights-widget-unavailable]'), $widget . ' must have its figures');
        }
        $rows = $crawler->filter('[data-test-tmonier-insights-approvals-pending-row]');
        self::assertCount(1, $rows, 'The order of Claire waiting for approval is listed.');
        self::assertStringContainsString('Claire', $rows->text());
        self::assertMatchesRegularExpression('/[34] jours/', $rows->text(), 'Placed four days ago at 10:00: three full days before 10:00, four after.');
        self::assertCount(1, $crawler->filter('[data-test-tmonier-insights-widget="approvals"] a[href*="/account/company/approvals"]'), 'Isabelle decides: the link to the approvals is shown.');
    }

    public function testTheAcmeWidgetsLeaveOutTheOtherCompanies(): void
    {
        $client = self::createClient();
        $this->login($client, 'isabelle@acme.example');

        $content = $client->request('GET', self::PAGE)->filter('[data-test-tmonier-insights-widget]')->each(static fn (Crawler $node): string => $node->text());

        foreach (['Marc', 'Antoine', 'BuildCo', 'TechParts', 'BC-2026-031'] as $foreign) {
            self::assertStringNotContainsString($foreign, implode(' ', $content));
        }
    }

    /** @return array<string, int> month (Y-m) => count, oldest first */
    private function monthlyOrders(Crawler $rows, string $cell): array
    {
        $counts = [];
        foreach ($rows as $row) {
            $row = new Crawler($row);
            $counts[(string) $row->attr('data-month')] = (int) $row->filter('[data-test-tmonier-insights-' . $cell . ']')->attr('data-value');
        }

        return $counts;
    }

    private function login(KernelBrowser $client, string $email): void
    {
        /** @var EntityManagerInterface $em */
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $customer = $em->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => $email]);
        self::assertInstanceOf(CustomerInterface::class, $customer, $email . ' must exist (demo fixtures).');
        $user = $customer->getUser();
        self::assertNotNull($user);

        $client->loginUser($user, 'shop');
    }
}
