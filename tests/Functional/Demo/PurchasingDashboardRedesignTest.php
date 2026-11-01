<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Tests\Support\BuildsDemoCarts;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-201: the purchasing dashboard of company-insights, redrawn in the demo (hooks and templates of the theme; the plugin is not
 * touched). Run against a database loaded with the `demo` suite: the orders of Acme follow the calendar of the day of the reset, so
 * the figures are compared with the ones of the same page (the monthly table adds up to the spend of the twelve months) and never
 * with fixed amounts.
 */
final class PurchasingDashboardRedesignTest extends WebTestCase
{
    use BuildsDemoCarts;

    private const ISABELLE = 'isabelle@acme.example';

    private const CLAIRE = 'claire@acme.example';

    private const PAGE = '/fr_FR/account/company/insights';

    private ?KernelBrowser $isabelle = null;

    public function testTheEssentialComesFirstWithTheComparisonPeriodStatedInOneSentence(): void
    {
        $crawler = $this->page(self::PAGE);

        $summary = $this->plain($crawler->filter('[data-test-b2b-insights-summary]')->html());
        self::assertStringContainsString('12 derniers mois', $summary);
        self::assertStringContainsString('comparé à la période du', $summary, 'The period of comparison is named.');
        self::assertStringContainsString('hors taxes', $summary);

        $kpis = $crawler->filter('[data-test-b2b-insights-kpi]');
        self::assertCount(4, $kpis, 'Spend, orders, average basket, active buyers.');
        $spend = $this->plain($kpis->eq(0)->html());
        self::assertStringContainsString('Dépense HT', $spend);
        self::assertStringContainsString($this->euros($this->twelveMonthsSpend($crawler)), $spend, 'The first figure is the spend of the period, the one the monthly table adds up to.');
        self::assertMatchesRegularExpression('/[+−-]\d+,\d %/u', $spend, 'The change is a percentage.');
        self::assertMatchesRegularExpression('/Hausse|Baisse|Stable/u', $kpis->eq(0)->filter('.visually-hidden, [data-test-b2b-insights-trend-word]')->text(), 'The trend is a word, not only a colour or an arrow.');
        self::assertCount(1, $kpis->eq(0)->filter('[aria-hidden="true"]'), 'The arrow is decoration.');
    }

    public function testTheFiguresComeBeforeTheChartAndTheThingsToHandleBeforeTheFigures(): void
    {
        $html = $this->html(self::PAGE);

        $todo = strpos($html, 'data-test-b2b-insights-todo');
        $kpi = strpos($html, 'data-test-b2b-insights-kpi');
        $chart = strpos($html, 'data-test-b2b-insights-chart');
        self::assertIsInt($todo);
        self::assertIsInt($kpi);
        self::assertIsInt($chart);
        self::assertLessThan($kpi, $todo);
        self::assertLessThan($chart, $kpi);
    }

    public function testThePendingApprovalAndTheOverdueInvoicesAreToBeHandledFirst(): void
    {
        $crawler = $this->page(self::PAGE);

        $items = $crawler->filter('[data-test-b2b-insights-todo-item]');
        self::assertCount(2, $items, 'One order waits for an approval, one invoice is overdue.');
        $approval = $this->plain($items->eq(0)->html());
        self::assertStringContainsString('attend votre approbation', $approval);
        self::assertStringContainsString('Claire', $approval);
        self::assertMatchesRegularExpression('/[34] jours/', $approval);
        self::assertCount(1, $items->eq(0)->filter('a[href*="/account/company/approvals"]'));
        self::assertStringContainsString('échus', $this->plain($items->eq(1)->html()));
        self::assertCount(1, $items->eq(1)->filter('a[href*="/account/company/trade-credit"], a[href*="/account/credit"], a[href*="/account/"]'));
    }

    public function testTheMonthlyChartAnnouncesEveryMonthAndTheYearBeforeWithoutRelyingOnColour(): void
    {
        $crawler = $this->page(self::PAGE);

        $chart = $crawler->filter('svg[data-test-b2b-insights-chart]');
        self::assertCount(1, $chart);
        self::assertSame('img', $chart->attr('role'));
        self::assertStringContainsString('tableau', (string) $chart->attr('aria-label'), 'The label sends to the table that carries the values.');
        self::assertCount(12, $chart->filter('[data-test-b2b-insights-bar]'));
        $previous = $chart->filter('[data-test-b2b-insights-bar-previous]');
        self::assertCount(12, $previous);
        self::assertStringStartsWith('url(#', (string) $previous->first()->attr('fill'), 'The year before is hatched: a pattern, not another colour only.');
        self::assertCount(1, $chart->filter('[data-in-progress="true"]'), 'The month in progress is marked apart.');
        self::assertStringContainsString('en cours', $this->plain($crawler->filter('[data-test-b2b-insights-chart-note]')->html()));

        $details = $crawler->filter('details[data-test-b2b-insights-table]');
        self::assertCount(1, $details, 'The values are a table folded under the chart.');
        $rows = $details->filter('tbody tr');
        self::assertCount(12, $rows);
        self::assertCount(4, $rows->first()->filter('td'), 'Per month: spend, orders, spend and orders of the year before.');
    }

    public function testTheTablesAreInRegionsThatScrollSoThatAPhoneNeverScrollsSideways(): void
    {
        $crawler = $this->page(self::PAGE);

        $tables = $crawler->filter('main table');
        self::assertGreaterThanOrEqual(2, $tables->count());
        foreach ($tables as $table) {
            $region = (new Crawler($table))->ancestors()->filter('[role="region"][tabindex="0"][aria-label]');
            self::assertGreaterThan(0, $region->count(), 'Every table is in a focusable, labelled region.');
        }
    }

    public function testChangingThePeriodUpdatesTheFiguresAndNamesTheChosenPeriod(): void
    {
        $default = $this->page(self::PAGE);
        $month = $this->page(self::PAGE . '?period=last_30_days');

        $periods = $month->filter('[data-test-b2b-insights-periods] a');
        self::assertGreaterThanOrEqual(4, $periods->count());
        $current = $month->filter('[data-test-b2b-insights-periods] a[aria-current="page"]');
        self::assertCount(1, $current);
        self::assertStringContainsString('30 derniers jours', $current->text());
        self::assertStringContainsString('30 derniers jours', $this->plain($month->filter('[data-test-b2b-insights-summary]')->html()));
        self::assertNotSame($this->plain($default->filter('[data-test-b2b-insights-kpi]')->first()->html()), $this->plain($month->filter('[data-test-b2b-insights-kpi]')->first()->html()));
        self::assertCount(12, $month->filter('svg[data-test-b2b-insights-chart] [data-test-b2b-insights-bar]'), 'The chart always shows twelve months.');
    }

    public function testTheMetricOfTheChartIsChosenWithoutScript(): void
    {
        $crawler = $this->page(self::PAGE . '?metric=orders');

        $current = $crawler->filter('[data-test-b2b-insights-metrics] a[aria-current="true"]');
        self::assertCount(1, $current);
        self::assertStringContainsString('commandes', $current->text());
        self::assertStringContainsString('commandes', (string) $crawler->filter('svg[data-test-b2b-insights-chart]')->attr('aria-label'));
    }

    public function testThePeriodWithoutAnyOrderShowsAnExplainedEmptyStateInsteadOfFiguresAtZero(): void
    {
        $crawler = $this->page(self::PAGE . '?from=2020-01-01&to=2020-01-31');

        $empty = $crawler->filter('[data-test-b2b-insights-empty]');
        self::assertCount(1, $empty);
        self::assertStringContainsString('Aucune commande sur cette période', $this->plain($empty->html()));
        self::assertCount(1, $empty->filter('a[href$="/account/company/insights"]'), 'A way back to the default period.');
        self::assertCount(0, $crawler->filter('[data-test-b2b-insights-kpi]'));
        self::assertCount(0, $crawler->filter('svg[data-test-b2b-insights-chart]'));
        self::assertCount(0, $crawler->filter('[data-test-b2b-insights-todo]'));
        self::assertCount(1, $crawler->filter('[data-test-b2b-insights-periods]'), 'The period and the filters stay to change the choice.');
    }

    public function testTheTopsShowTheBuyersAndTheCategoriesWithTheirSharesAndTheFiveFirstProducts(): void
    {
        $crawler = $this->page(self::PAGE);

        $buyers = $crawler->filter('[data-test-tmonier-insights-ranking="buyers-EUR"] [data-test-tmonier-insights-ranking-row]');
        self::assertGreaterThanOrEqual(4, $buyers->count());
        self::assertMatchesRegularExpression('/\d+ %/u', $this->plain($buyers->first()->html()), 'The share of the spend.');
        self::assertStringNotContainsString('@', $this->plain($crawler->filter('[data-test-tmonier-insights-ranking="buyers-EUR"]')->html()), 'Never an e-mail address.');
        self::assertCount(1, $crawler->filter('[data-test-tmonier-insights-ranking="buyers-EUR"] [data-test-tmonier-insights-deactivated]'));

        $categories = $crawler->filter('[data-test-tmonier-insights-ranking="categories-EUR"]');
        self::assertStringContainsString('Autres catégories', $this->plain($categories->html()), 'The shares add up to a hundred.');
        self::assertCount(5, $categories->filter('[data-test-tmonier-insights-ranking-row]'), 'Four categories and the others.');

        $products = $crawler->filter('[data-test-tmonier-insights-ranking="products-EUR"] [data-test-tmonier-insights-ranking-row]');
        self::assertCount(5, $products);
        self::assertContains($this->plain($products->first()->filter('th')->html()), ['Chaussure basse S1P SRC noire', 'Casque de chantier blanc HDPE'], 'The top product is one of the two highest sellers (tie-breaking is non-deterministic).');
        self::assertCount(1, $crawler->filter('[data-test-tmonier-insights-see-all][href$="/account/company/insights/products"], a[href*="/account/company/insights/products"]'), 'A link to every product.');
    }

    public function testTheBarsOfTheSharesAreDecorationBecauseTheFiguresAreInText(): void
    {
        $crawler = $this->page(self::PAGE);

        $bars = $crawler->filter('[data-test-b2b-insights-share-bar]');
        self::assertGreaterThanOrEqual(10, $bars->count());
        foreach ($bars as $bar) {
            self::assertSame('true', (new Crawler($bar))->attr('aria-hidden'));
        }
    }

    public function testThePurchasesAreSplitPerSiteAndPerCostCentreInCards(): void
    {
        $crawler = $this->page(self::PAGE);

        $sites = $this->plain($crawler->filter('[data-test-tmonier-insights-breakdown="sites-EUR"]')->html());
        foreach (['Usine de Lyon', 'Entrepôt de Saint-Priest', 'Atelier de Grenoble'] as $site) {
            self::assertStringContainsString($site, $sites);
        }
        $centres = $this->plain($crawler->filter('[data-test-tmonier-insights-breakdown="cost-center-EUR"]')->html());
        self::assertStringContainsString('MAINT-02', $centres);
        self::assertStringContainsString('ATELIER-01', $centres);
    }

    public function testTheApprovalsAndTheCreditCardsKeepTheirFigures(): void
    {
        $crawler = $this->page(self::PAGE);

        $approvals = $this->plain($crawler->filter('[data-test-tmonier-insights-widget="approvals"]')->html());
        self::assertStringContainsString('Délai médian de décision', $approvals);
        self::assertStringContainsString('Taux de refus', $approvals);
        $credit = $this->plain($crawler->filter('[data-test-tmonier-insights-widget="credit"]')->html());
        self::assertStringContainsString('Disponible', $credit);
        self::assertStringContainsString('Prochaine échéance', $credit);
    }

    public function testThePeriodsAndTheFiltersAreInOneBlockWithTheExportAndTheFiltersFoldAway(): void
    {
        $crawler = $this->page(self::PAGE);

        $toolbar = $crawler->filter('[data-test-b2b-insights-toolbar]');
        self::assertCount(1, $toolbar);
        self::assertCount(1, $toolbar->filter('[data-test-b2b-insights-periods]'));
        self::assertCount(1, $toolbar->filter('details[data-test-b2b-insights-filters]'), 'The filters fold away.');
        self::assertNull($toolbar->filter('details[data-test-b2b-insights-filters]')->attr('open'), 'Closed while no filter is applied.');
        self::assertCount(1, $toolbar->filter('[data-test-tmonier-insights-export-link]'));
        self::assertCount(1, $toolbar->filter('select[name="member"]'));
        self::assertCount(1, $toolbar->filter('select[name="site"]'));
    }

    public function testAFilterKeepsTheFiltersOpenAndTheOrdersOfThatMemberOnly(): void
    {
        $crawler = $this->page(self::PAGE);
        $member = (string) $crawler->filter('select[name="member"] option:not([value=""])')->first()->attr('value');

        $filtered = $this->page(self::PAGE . '?member=' . $member);

        self::assertNotNull($filtered->filter('details[data-test-b2b-insights-filters]')->attr('open'), 'The filter in use is visible.');
        self::assertNotSame($this->plain($crawler->filter('[data-test-b2b-insights-kpi]')->first()->html()), $this->plain($filtered->filter('[data-test-b2b-insights-kpi]')->first()->html()));
    }

    public function testClaireWhoIsNotAnAdministratorIsRefusedWithAnExplanationAsBefore(): void
    {
        $client = $this->clientOf(self::CLAIRE);

        $client->request('GET', self::PAGE);

        self::assertResponseStatusCodeSame(403);
        self::assertStringContainsString('réservée aux administrateurs', $this->plain((string) $client->getResponse()->getContent()));
        $client->request('GET', self::PAGE . '/export');
        self::assertResponseStatusCodeSame(403);
    }

    public function testTheExportAndTheFullRankingsKeepWorking(): void
    {
        $client = $this->clientOf(self::ISABELLE);

        $client->request('GET', self::PAGE . '/export?period=last_12_months');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('text/csv', (string) $client->getResponse()->headers->get('Content-Type'));
        foreach (['buyers', 'products', 'categories'] as $ranking) {
            $client->request('GET', self::PAGE . '/' . $ranking);
            self::assertResponseIsSuccessful($ranking);
        }
    }

    public function testTheEnglishPageHasNoFrenchLeftInTheBlocksOfTheDemo(): void
    {
        $crawler = $this->page('/en_US/account/company/insights');

        $text = $this->plain($crawler->filter('[data-test-b2b-insights]')->html());
        self::assertStringContainsString('Purchases per month', $text);
        self::assertStringContainsString('To handle', $text);
        foreach (['À traiter', 'Achats par mois', 'Qui achète', 'Voir les valeurs', 'comparé à'] as $french) {
            self::assertStringNotContainsString($french, $text);
        }
    }

    public function testThePluginStaysAtItsTaggedVersionBecauseTheRedesignIsAnOverrideOfTheDemo(): void
    {
        $lock = json_decode((string) file_get_contents(\dirname(__DIR__, 3) . '/composer.lock'), true, 512, \JSON_THROW_ON_ERROR);
        self::assertIsArray($lock);
        $packages = $lock['packages'] ?? [];
        self::assertIsArray($packages);

        $found = [];
        foreach ($packages as $package) {
            if (\is_array($package) && 'tmonier/sylius-company-insights-plugin' === ($package['name'] ?? null)) {
                $found[] = $package;
            }
        }

        self::assertCount(1, $found);
        $version = $found[0]['version'] ?? null;
        $source = $found[0]['source'] ?? null;
        self::assertIsString($version);
        self::assertMatchesRegularExpression('/^v\d+\.\d+\.\d+$/', $version, 'A released version, not a branch or a fork.');
        self::assertIsArray($source);
        self::assertIsString($source['url'] ?? null);
        self::assertStringContainsString('thibmonier/sylius-company-insights-plugin', $source['url']);
    }

    private function page(string $url): Crawler
    {
        return new Crawler($this->html($url));
    }

    private function html(string $url): string
    {
        $this->isabelle ??= $this->clientOf(self::ISABELLE);

        return $this->get($this->isabelle, $url);
    }

    /** The sum of the spend column of the monthly table, in cents. */
    private function twelveMonthsSpend(Crawler $crawler): int
    {
        return (int) array_sum($crawler->filter('[data-test-tmonier-insights-monthly-table="EUR"] [data-test-tmonier-insights-monthly-spend]')->each(static fn (Crawler $cell): int => (int) $cell->attr('data-value')));
    }

    private function euros(int $cents): string
    {
        $formatter = new \NumberFormatter('fr_FR', \NumberFormatter::CURRENCY);

        return $this->plain((string) $formatter->formatCurrency($cents / 100, 'EUR'));
    }
}
