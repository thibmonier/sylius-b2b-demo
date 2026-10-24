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
 * US-189: the product page of the B2B channel follows the validated mock-up (hero with gallery and buy box, assurances, body, behind the
 * scenes, siblings) and holds with the real data: a long name, no tiers, no safety data, no photo of its own. The B2C page stays the core one.
 */
final class ProductPageRedesignTest extends WebTestCase
{
    use IsolatesSharedAccounts;

    /** The three kinds of page of the inventory: short name with tiers, long name with safety data, no tiers. */
    private const SHORT_WITH_TIERS = ['outi-001', 'OUTI-001', false];

    private const LONG_NAME = ['epi-vest-001', 'EPI-VEST-001', true];

    private const WITHOUT_TIERS = ['fixa-002', 'FIXA-002', false];

    /** @return iterable<string, array{string, string, bool, string}> */
    public static function pages(): iterable
    {
        foreach (['fr_FR' => 'fr', 'en_US' => 'en'] as $locale => $suffix) {
            foreach ([self::SHORT_WITH_TIERS, self::LONG_NAME, self::WITHOUT_TIERS] as [$slug, $code, $safety]) {
                yield $locale . ' ' . $code => [$locale, '/' . $locale . '/products/' . $slug . '-' . $suffix, $safety, $code];
            }
        }
    }

    #[DataProvider('pages')]
    public function testASignedInBuyerSeesTheEditorialLayoutInOrder(string $locale, string $path, bool $safety, string $code): void
    {
        $page = $this->signedIn('claire@acme.example')->request('GET', $path);

        self::assertResponseIsSuccessful();
        self::assertCount(1, $page->filter('[data-pdp]'));
        // DOM order: the title and the buy box (summary) come before the gallery, which the grid shows first on a phone and on the left.
        self::assertSame(['summary', 'buybox', 'assurances', 'gallery', 'body', 'backstage', 'related'], $this->parts($page));
        self::assertCount(1, $page->filter('main h1'));
        self::assertNotSame('', trim($page->filter('main h1')->text()));
        self::assertNotSame('', trim($page->filter('[data-pdp-eyebrow]')->text()), 'the category above the title');
        self::assertStringContainsString($code, $page->filter('[data-pdp-part="summary"] .b2b-reference')->text());
        self::assertCount(1, $page->filter('[data-pdp-part="buybox"] button[data-test-button="add-to-cart-button"]'), 'one add-to-cart action');
        self::assertSame($safety, 1 === $page->filter('[data-test-product-safety]')->count(), 'safety data only where there are some');
        self::assertCount(0, $page->filter('[data-pdp] .accordion'), 'no accordion: description, specifications and reviews are sections of the body');
    }

    #[DataProvider('pages')]
    public function testTheBodySectionsAreThereOnlyWhenTheyHaveContent(string $locale, string $path, bool $safety, string $code): void
    {
        $page = $this->signedIn('claire@acme.example')->request('GET', $path);

        $sections = $page->filter('[data-pdp-part="body"] [data-pdp-section]')->each(static fn (Crawler $section): string => (string) $section->attr('data-pdp-section'));
        self::assertSame($safety ? ['description', 'attributes', 'safety', 'reviews'] : ['description', 'attributes', 'reviews'], $sections);
        foreach ($page->filter('[data-pdp-part="body"] [data-pdp-section]') as $node) {
            $section = new Crawler($node);
            self::assertCount(1, $section->filter('h2'), (string) $section->attr('data-pdp-section') . ' has its title');
            self::assertNotSame('', trim($section->text()), 'no empty section');
        }
        self::assertGreaterThanOrEqual(1, $page->filter('[data-pdp-section="attributes"] dt')->count());
    }

    public function testTheTiersAreBeforeTheQuantityAndTheNegotiatedPriceIsSaid(): void
    {
        $client = $this->signedIn('claire@acme.example');
        $page = $client->request('GET', '/fr_FR/products/outi-001-fr');
        $html = (string) $client->getResponse()->getContent();

        self::assertCount(1, $page->filter('[data-pdp-part="buybox"] .b2b-negotiated'));
        self::assertStringContainsString('Acme Industries', $page->filter('[data-pdp-part="buybox"] .b2b-negotiated')->text());
        self::assertGreaterThanOrEqual(4, $page->filter('[data-pdp-part="buybox"] .b2b-price-breaks tbody tr')->count());
        self::assertLessThan(strpos($html, 'sylius_shop_add_to_cart[cartItem][quantity]'), strpos($html, 'b2b-price-breaks__table'), 'tiers, then quantity, then the button');
    }

    public function testAPageWithoutTiersHasNoEmptyTierBlock(): void
    {
        $page = $this->signedIn('claire@acme.example')->request('GET', '/fr_FR/products/fixa-002-fr');

        self::assertCount(0, $page->filter('.b2b-price-breaks'));
        self::assertCount(1, $page->filter('[data-pdp-part="buybox"] button[data-test-button="add-to-cart-button"]'));
    }

    #[DataProvider('pages')]
    public function testAVisitorSeesNoPriceAndIsAskedToSignIn(string $locale, string $path, bool $safety, string $code): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2B');
        $page = $client->request('GET', $path);

        self::assertResponseIsSuccessful();
        $box = $page->filter('[data-pdp-part="buybox"]');
        self::assertCount(1, $box);
        self::assertGreaterThanOrEqual(1, $box->filter('a[href$="/login"]')->count());
        self::assertCount(1, $box->filter('a[href="/demo/accounts"]'));
        self::assertCount(0, $box->filter('.b2b-price-breaks, .b2b-negotiated, button[name*="add_to_cart"]'));
        self::assertDoesNotMatchRegularExpression('/\d+[,.]\d{2}\s?€|€\s?\d/u', $page->filter('main')->text(), 'no price anywhere for a visitor');
    }

    #[DataProvider('pages')]
    public function testTheSiblingsAreOfTheSameAisleAndNeverTheProductItself(string $locale, string $path, bool $safety, string $code): void
    {
        $page = $this->signedIn('claire@acme.example')->request('GET', $path);

        $related = $page->filter('[data-pdp-part="related"] [data-pdp-related]');
        self::assertGreaterThanOrEqual(1, $related->count());
        self::assertLessThanOrEqual(4, $related->count());
        $hrefs = $related->filter('a[href*="/products/"]')->each(static fn (Crawler $link): string => (string) $link->attr('href'));
        self::assertNotContains($path, $hrefs);
        self::assertSame($hrefs, array_values(array_unique($hrefs)), 'a sibling is listed once');
    }

    public function testBehindTheScenesNamesTheCataloguePluginCountAndLinksThePluginsPage(): void
    {
        $page = $this->signedIn('claire@acme.example')->request('GET', '/fr_FR/products/outi-001-fr');

        $aside = $page->filter('[data-pdp-part="backstage"]');
        $count = (int) $page->filter('[data-pdp-plugin-count]')->attr('data-pdp-plugin-count');
        self::assertGreaterThan(0, $count);
        self::assertStringContainsString((string) $count, $aside->filter('a[href="/fr_FR/plugins"]')->text());
        self::assertGreaterThanOrEqual(4, $aside->filter('li')->count());
    }

    public function testTheThreeAssurancesAreTheCommitmentsOfTheShop(): void
    {
        $page = $this->signedIn('claire@acme.example')->request('GET', '/fr_FR/products/outi-001-fr');

        $text = $page->filter('[data-pdp-part="assurances"]')->text();
        foreach (['Facturation mensuelle', 'Paiement à terme sur compte', 'Support pro du lundi au vendredi, 8h-18h'] as $commitment) {
            self::assertStringContainsString($commitment, $text);
        }
    }

    public function testTheConsumerChannelKeepsTheStockProductPage(): void
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2C');
        $page = $client->request('GET', '/fr_FR/products/outi-001-fr');

        self::assertResponseIsSuccessful();
        self::assertCount(0, $page->filter('[data-pdp], [data-pdp-part]'));
        self::assertGreaterThanOrEqual(1, $page->filter('.accordion')->count(), 'the core accordion');
    }

    /** @return list<string> */
    private function parts(Crawler $page): array
    {
        return $page->filter('[data-pdp-part]')->each(static fn (Crawler $part): string => (string) $part->attr('data-pdp-part'));
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
