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

    /** A product without review shows the link to give one and nothing else: no "no review" alert, no empty list. */
    public function testWithoutReviewTheSectionIsJustTheLinkToGiveOne(): void
    {
        $page = $this->signedIn('claire@acme.example')->request('GET', '/fr_FR/products/outi-001-fr');

        $section = $page->filter('[data-pdp-section="reviews"]');
        self::assertCount(1, $section->filter('a[href$="/outi-001-fr/reviews/new"]'));
        self::assertCount(0, $section->filter('.alert'));
        self::assertStringNotContainsString('aucun avis', mb_strtolower($section->text()));
    }

    /** The norm pill over the photo comes from the attribute of the product, only when the product has one. */
    public function testTheNormPillComesFromTheAttributeOnly(): void
    {
        $withNorm = $this->signedIn('claire@acme.example')->request('GET', '/fr_FR/products/epi-vest-001-fr');
        $pill = $withNorm->filter('[data-pdp-part="gallery"] [data-pdp-norm]');
        self::assertCount(1, $pill);
        self::assertStringContainsString('EN 340', $pill->text());

        $without = $this->signedIn('claire@acme.example')->request('GET', '/fr_FR/products/outi-001-fr');
        self::assertCount(0, $without->filter('[data-pdp-norm]'));
    }

    /** US-189 (tightening): the "powered by" badges of the plugins of the page are on ONE line under the buy box, not between the actions. */
    public function testThePluginBadgesAreOnOneLineUnderTheBuyBox(): void
    {
        $page = $this->signedIn('claire@acme.example')->request('GET', '/fr_FR/products/outi-001-fr');

        self::assertCount(1, $page->filter('[data-pdp-part="summary"] .b2b-pdp__badges'));
        self::assertCount(0, $page->filter('[data-pdp-part="buybox"] [data-demo-plugin-badge]'), 'none inside the buy box');
        $slugs = $page->filter('[data-pdp-part="summary"] .b2b-pdp__badges [data-demo-plugin-badge]')->each(static fn (Crawler $badge): string => (string) $badge->attr('data-demo-plugin-badge'));
        self::assertEqualsCanonicalizing(['sylius-tier-pricing-plugin', 'sylius-container-fill-plugin', 'sylius-quote-request-plugin'], $slugs);
        self::assertCount(\count($slugs), $page->filter('[data-pdp-part="summary"] [data-demo-plugin-badge]'), 'every badge of the summary is in the row');
    }

    public function testAPageWithoutTiersHasNoTierBadge(): void
    {
        $page = $this->signedIn('claire@acme.example')->request('GET', '/fr_FR/products/fixa-002-fr');

        self::assertCount(0, $page->filter('[data-demo-plugin-badge="sylius-tier-pricing-plugin"]'), 'a badge says what the page really does');
    }

    /** The pallet and truck preview stays complete but folded by default, with a one-line summary. */
    public function testThePalletPreviewIsFoldedByDefault(): void
    {
        $page = $this->signedIn('claire@acme.example')->request('GET', '/fr_FR/products/outi-001-fr');

        $fold = $page->filter('[data-pdp-part="buybox"] details[data-pdp-fill]');
        self::assertCount(1, $fold);
        self::assertNull($fold->attr('open'));
        self::assertStringContainsString('Remplissage palette et camion', $fold->filter('summary')->text());
        self::assertCount(1, $fold->filter('[data-test-tmonier-container-fill-preview]'), 'the content is unchanged');
    }

    /** The quote request is a compact secondary action: the big title is for a visitor only, the button and the quantity stay. */
    public function testTheQuoteRequestIsCompactForABuyerAndTitledForAVisitor(): void
    {
        $buyer = $this->signedIn('claire@acme.example')->request('GET', '/fr_FR/products/outi-001-fr');
        $quote = $buyer->filter('[data-pdp-part="buybox"] [data-test-tmonier-add-to-quote]');
        self::assertCount(1, $quote);
        self::assertCount(1, $quote->filter('[data-test-tmonier-add-to-quote-button]'));
        self::assertCount(1, $quote->filter('input[name="tmonier_quote_request_add[quantity]"]'));
        self::assertStringNotContainsString('Besoin de grandes quantités', $quote->text());

        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2B');
        $visitor = $client->request('GET', '/fr_FR/products/outi-001-fr')->filter('[data-pdp-part="buybox"] [data-test-tmonier-add-to-quote]');
        self::assertCount(1, $visitor->filter('[data-test-tmonier-add-to-quote-button]'));
        self::assertStringContainsString('grandes quantités', mb_strtolower($visitor->text()));
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
