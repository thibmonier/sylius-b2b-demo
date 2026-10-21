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
