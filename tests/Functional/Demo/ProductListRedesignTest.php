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
 * US-198: the product list of the B2B channel follows the validated mock-up (header with the category, notice of the prices of the company,
 * categories with their counts, search / sort / page size / display, cards with the three levels of stock, pagination) and holds with the real
 * data. The visitor sees no price, the B2C list stays the core one. Relies on the `demo` fixtures (30 products in "visserie", stock tracked).
 */
final class ProductListRedesignTest extends WebTestCase
{
    use DatabaseAccess;
    use IsolatesSharedAccounts;

    private const CATEGORY = '/%s/taxons/visserie';

    /** @return iterable<string, array{string}> */
    public static function locales(): iterable
    {
        yield 'fr' => ['fr_FR'];
        yield 'en' => ['en_US'];
    }

    #[DataProvider('locales')]
    public function testABuyerSeesTheCategoryHeaderTheNoticeAndTheCategories(string $locale): void
    {
        $page = $this->signedIn('claire@acme.example')->request('GET', \sprintf(self::CATEGORY, $locale));

        self::assertResponseIsSuccessful();
        self::assertCount(1, $page->filter('[data-b2b-list]'));
        self::assertCount(1, $page->filter('main h1'));
        self::assertNotSame('', trim($page->filter('[data-b2b-list] h1')->text()));
        self::assertCount(1, $page->filter('[data-list-eyebrow]'), 'the aisle above the title');
        self::assertStringContainsString('Acme', $page->filter('[data-list-notice="buyer"]')->text());
        self::assertCount(0, $page->filter('[data-list-notice="visitor"]'));

        $categories = $page->filter('[data-list-categories] a');
        self::assertGreaterThanOrEqual(2, $categories->count());
        self::assertCount(1, $page->filter('[data-list-categories] a[aria-current="page"]'));
        $categories->each(function (Crawler $link): void {
            self::assertMatchesRegularExpression('/\d+\s*$/', trim($link->text()), 'the count of products after the name');
        });
    }

    public function testTheCountOfTheCurrentCategoryIsTheOneOfTheDatabase(): void
    {
        $page = $this->signedIn('claire@acme.example')->request('GET', \sprintf(self::CATEGORY, 'fr_FR'));

        $expected = $this->intValue("SELECT COUNT(DISTINCT p.id) FROM sylius_product p JOIN sylius_product_taxon pt ON pt.product_id = p.id JOIN sylius_taxon t ON t.id = pt.taxon_id WHERE t.code = 'visserie' AND p.enabled = 1");
        self::assertSame((string) $expected, trim(preg_replace('/\D+/', ' ', $page->filter('[data-list-categories] a[aria-current="page"]')->text()) ?? ''));
        self::assertStringContainsString((string) $expected, $page->filter('[data-list-header]')->text());
    }

    public function testEveryCardOfABuyerCarriesReferenceNameStockPriceAndAnAction(): void
    {
        $page = $this->signedIn('claire@acme.example')->request('GET', \sprintf(self::CATEGORY, 'fr_FR'));

        $cards = $page->filter('.products-grid [data-b2b-card]');
        self::assertCount(9, $cards, 'the first page');
        $cards->each(function (Crawler $card): void {
            self::assertMatchesRegularExpression('/VISS-\d{3}/', $card->filter('.b2b-sku')->text());
            self::assertNotSame('', trim($card->filter('[data-test-product-name], .b2b-card__name')->first()->text()));
            self::assertCount(1, $card->filter('[data-list-stock]'), 'the stock is tracked');
            self::assertContains($card->filter('[data-list-stock]')->attr('data-list-stock'), ['in', 'low', 'out']);
            self::assertCount(1, $card->filter('.b2b-price-note'), 'the "excl. VAT" mention of the price');
            $out = 'out' === $card->filter('[data-list-stock]')->attr('data-list-stock');
            self::assertSame($out ? 0 : 1, $card->filter('form[action$="/cart/add-from-list"] button[type="submit"]')->count());
            self::assertSame($out ? 1 : 0, $card->filter('a[data-list-quote]')->count(), 'a quote instead of the cart when out of stock');
        });
    }

    public function testThePageShowsTheThreeLevelsOfStock(): void
    {
        $client = $this->signedIn('claire@acme.example');
        $levels = [];
        foreach ([1, 2, 3, 4] as $number) {
            $page = $client->request('GET', \sprintf(self::CATEGORY, 'fr_FR'), ['page' => $number]);
            $levels = [...$levels, ...$page->filter('[data-list-stock]')->each(static fn (Crawler $stock): string => (string) $stock->attr('data-list-stock'))];
        }

        self::assertEqualsCanonicalizing(['in', 'low', 'out'], array_values(array_unique($levels)));
    }

    #[DataProvider('locales')]
    public function testAVisitorSeesNoPriceAndAnInvitationToSignIn(string $locale): void
    {
        $page = self::createClient()->request('GET', \sprintf(self::CATEGORY, $locale));

        self::assertResponseIsSuccessful();
        self::assertCount(1, $page->filter('[data-list-notice="visitor"]'));
        self::assertCount(1, $page->filter('[data-list-notice="visitor"] a[href="/demo/accounts"]'), 'where to pick a demo account');
        self::assertCount(0, $page->filter('[data-list-notice="buyer"]'));
        self::assertCount(0, $page->filter('.products-grid .b2b-price-note'));
        self::assertCount(0, $page->filter('.products-grid form[action$="/cart/add-from-list"]'));
        self::assertCount(9, $page->filter('.products-grid [data-list-signin]'), 'one way to sign in per card');
    }

    public function testTheSortIsKeptWhenThePageChangesAndTheCurrentPageIsAnnounced(): void
    {
        $client = $this->signedIn('claire@acme.example');

        $page = $client->request('GET', \sprintf(self::CATEGORY, 'fr_FR'), ['sort' => 'price-asc']);
        $links = $page->filter('nav[data-list-pagination] a');

        self::assertGreaterThanOrEqual(3, $links->count());
        $links->each(function (Crawler $link): void {
            self::assertStringContainsString('sorting%5Bprice%5D=asc', (string) $link->attr('href'));
        });
        self::assertSame('1', trim($page->filter('nav[data-list-pagination] [aria-current="page"]')->text()));

        $second = $client->click($page->filter('nav[data-list-pagination] a')->reduce(static fn (Crawler $link): bool => '2' === trim($link->text()))->link());
        self::assertSame('2', trim($second->filter('nav[data-list-pagination] [aria-current="page"]')->text()));
        self::assertMatchesRegularExpression('/10\D+18/', $second->filter('[data-list-results]')->text());
    }

    public function testSortingByPriceOrdersTheCards(): void
    {
        $page = $this->signedIn('claire@acme.example')->request('GET', \sprintf(self::CATEGORY, 'fr_FR'), ['sort' => 'price-desc']);

        $codes = $page->filter('.products-grid .b2b-sku')->each(static fn (Crawler $sku): string => trim(str_replace(['Réf.', 'Ref.'], '', explode('·', $sku->text())[0])));
        $prices = array_map(fn (string $code): int => $this->intValue("SELECT cp.price FROM sylius_channel_pricing cp JOIN sylius_product_variant v ON v.id = cp.product_variant_id WHERE v.code = ? AND cp.channel_code = 'B2B'", [$code]), $codes);
        $sorted = $prices;
        rsort($sorted);

        self::assertCount(9, $prices);
        self::assertSame($sorted, $prices);
    }

    public function testTheToolbarOffersSearchSortPageSizeAndDisplayWithLabels(): void
    {
        $page = $this->signedIn('claire@acme.example')->request('GET', \sprintf(self::CATEGORY, 'fr_FR'));

        $form = $page->filter('form[data-list-toolbar]');
        self::assertCount(1, $form);
        self::assertSame('get', strtolower((string) $form->attr('method')));
        foreach (['criteria[search][value]', 'sort', 'limit'] as $name) {
            $field = $form->filter('[name="' . $name . '"]');
            self::assertCount(1, $field, $name);
            self::assertCount(1, $page->filter('label[for="' . $field->attr('id') . '"]'), $name . ' has its label');
        }
        self::assertSame(['9', '18', '27'], $form->filter('select[name="limit"] option')->each(static fn (Crawler $option): string => (string) $option->attr('value')));
        self::assertCount(2, $form->filter('[role="group"] button[aria-pressed]'), 'grid and list');
        self::assertSame('true', $form->filter('button[name="view"][value="grid"]')->attr('aria-pressed'));
    }

    public function testTheListViewIsServerSideAndKeepsTheCards(): void
    {
        $page = $this->signedIn('claire@acme.example')->request('GET', \sprintf(self::CATEGORY, 'fr_FR'), ['view' => 'list']);

        self::assertCount(1, $page->filter('.products-grid.b2b-products--list'));
        self::assertCount(9, $page->filter('.products-grid [data-b2b-card]'));
        self::assertSame('true', $page->filter('button[name="view"][value="list"]')->attr('aria-pressed'));
        self::assertSame('false', $page->filter('button[name="view"][value="grid"]')->attr('aria-pressed'));
        self::assertCount(1, $page->filter('nav[data-list-pagination] a[href*="view=list"]')->first(), 'the display is kept on the next page');
    }

    public function testASearchWithoutResultExplainsItAndOffersThreeWaysOut(): void
    {
        $page = $this->signedIn('claire@acme.example')->request('GET', \sprintf(self::CATEGORY, 'fr_FR'), ['criteria' => ['search' => ['value' => 'zzzz-no-such-product']]]);

        self::assertResponseIsSuccessful();
        $empty = $page->filter('[data-list-empty]');
        self::assertCount(1, $empty);
        self::assertStringContainsString('zzzz-no-such-product', $empty->filter('h2')->text());
        self::assertCount(3, $empty->filter('a'));
        self::assertCount(0, $page->filter('.products-grid [data-b2b-card]'));
    }

    public function testTheConsumerChannelKeepsTheCoreList(): void
    {
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2C');

        $page = $client->request('GET', \sprintf(self::CATEGORY, 'fr_FR'));

        self::assertResponseIsSuccessful();
        self::assertCount(0, $page->filter('[data-b2b-list]'));
        self::assertCount(0, $page->filter('[data-b2b-card]'));
        self::assertCount(0, $page->filter('[data-list-toolbar]'));
        self::assertGreaterThan(0, $page->filter('.products-grid')->count());
    }

    private function signedIn(string $email): KernelBrowser
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $this->isolateSharedAccounts($client);
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $em);
        $customer = $em->getRepository(CustomerInterface::class)->findOneBy(['emailCanonical' => $email]);
        self::assertInstanceOf(CustomerInterface::class, $customer, $email . ' must exist (demo fixtures).');
        $user = $customer->getUser();
        self::assertNotNull($user);
        $client->loginUser($user, 'shop');

        return $client;
    }
}
