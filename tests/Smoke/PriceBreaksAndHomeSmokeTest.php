<?php

declare(strict_types=1);

namespace App\Tests\Smoke;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-090 / US-091 / US-089 (decision D2): price breaks and company price on the product page and the cards, and the
 * home page of a signed-in buyer without the "latest deals / latest products" duplicate.
 */
final class PriceBreaksAndHomeSmokeTest extends WebTestCase
{
    private const WITH_TIERS = '/fr_FR/products/outi-001-fr';

    private const WITHOUT_TIERS = '/fr_FR/products/fixa-002-fr';

    public function testAcmeSeesItsNegotiatedPriceAndAtLeastThreeTiersOnTheProductPage(): void
    {
        $crawler = $this->signedIn('claire@acme.example')->request('GET', self::WITH_TIERS);

        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('.b2b-negotiated'));
        self::assertStringContainsString('Acme Industries', $crawler->filter('.b2b-negotiated')->text());
        self::assertStringContainsString('-12', $crawler->filter('.b2b-negotiated')->text());
        self::assertGreaterThanOrEqual(4, $crawler->filter('.b2b-price-breaks tbody tr')->count(), 'catalogue price + at least three tiers');
        self::assertGreaterThanOrEqual(3, $crawler->filter('.b2b-price-breaks .b2b-badge-negotiated')->count());
        self::assertCount(1, $crawler->filter('.b2b-price-breaks caption'));
        self::assertGreaterThanOrEqual(3, $crawler->filter('.b2b-price-breaks th[scope="col"]')->count());
    }

    public function testAnotherCompanySeesThePublicTiersWithoutNegotiatedPrice(): void
    {
        $crawler = $this->signedIn('marc@techparts.example')->request('GET', self::WITH_TIERS);

        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('.b2b-negotiated'));
        self::assertCount(0, $crawler->filter('.b2b-badge-negotiated'));
        self::assertSame(4, $crawler->filter('.b2b-price-breaks tbody tr')->count());
    }

    public function testNoTierMeansNoTable(): void
    {
        $crawler = $this->signedIn('claire@acme.example')->request('GET', self::WITHOUT_TIERS);

        self::assertResponseIsSuccessful();
        self::assertCount(0, $crawler->filter('.b2b-price-breaks'));
        self::assertCount(0, $crawler->filter('table.b2b-price-breaks__table'));
    }

    public function testAnAnonymousVisitorSeesNoPriceBreak(): void
    {
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2B');
        $client->request('GET', self::WITH_TIERS);

        self::assertStringNotContainsString('b2b-price-breaks', (string) $client->getResponse()->getContent());
    }

    public function testTheListShowsTheCompanyPriceOnTheCards(): void
    {
        $crawler = $this->signedIn('claire@acme.example')->request('GET', '/fr_FR/taxons/quincaillerie');

        self::assertResponseIsSuccessful();
        self::assertGreaterThanOrEqual(1, $crawler->filter('.b2b-negotiated-line')->count());
        self::assertStringContainsString('Acme Industries', $crawler->filter('.b2b-negotiated-line')->first()->text());
    }

    public function testTheCardsOfAnotherCompanyHaveNoCompanyPriceAndAMeaningfulFromMention(): void
    {
        $crawler = $this->signedIn('marc@techparts.example')->request('GET', '/fr_FR/taxons/quincaillerie');

        self::assertCount(0, $crawler->filter('.b2b-negotiated-line'));
        self::assertGreaterThanOrEqual(1, $crawler->filter('.b2b-from-price')->count(), 'public tiers go down to -15 %');
    }

    public function testTheSignedInHomeHasThreeBlocksWithoutTheDuplicate(): void
    {
        $client = $this->signedIn('claire@acme.example');
        $crawler = $client->request('GET', '/fr_FR/');
        $html = (string) $client->getResponse()->getContent();

        self::assertResponseIsSuccessful();
        self::assertStringNotContainsString('Dernières offres', $html);
        self::assertStringNotContainsString('Derniers produits', $html);
        $seen = [];
        foreach (['usual', 'restock', 'negotiated'] as $block) {
            $products = $this->productsOf($crawler, $block);
            self::assertNotEmpty($products, $block);
            self::assertSame([], array_intersect($seen, $products), $block . ' repeats a product of another block');
            $seen = [...$seen, ...$products];
        }
        self::assertStringContainsString('Vos références habituelles', $html);
        self::assertStringContainsString('Réassort rapide', $html);
        self::assertStringContainsString('Promotions négociées', $html);
    }

    public function testTheHomeIsLocalised(): void
    {
        $client = $this->signedIn('claire@acme.example');
        $client->request('GET', '/en_US/');
        $html = (string) $client->getResponse()->getContent();

        self::assertStringContainsString('Your usual references', $html);
        self::assertStringContainsString('Quick restock', $html);
        self::assertStringContainsString('Negotiated promotions', $html);
    }

    public function testAnAccountWithoutOrderGetsSuggestionsNotEmptyBlocks(): void
    {
        $crawler = $this->signedIn('antoine@buildco.example')->request('GET', '/fr_FR/');

        self::assertResponseIsSuccessful();
        self::assertNotEmpty($this->productsOf($crawler, 'usual'));
        self::assertNotEmpty($this->productsOf($crawler, 'restock'));
        self::assertCount(0, $crawler->filter('[data-b2b-home="negotiated"]'), 'no company price, no empty block');
    }

    public function testAnAnonymousVisitorGetsTheRolesPresentationWithoutProductNorPrice(): void
    {
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2B');
        $crawler = $client->request('GET', '/fr_FR/');

        self::assertCount(1, $crawler->filter('[data-b2b-home="roles"]'));
        self::assertCount(1, $crawler->filter('[data-b2b-home="roles"] a[href="/demo/accounts"]'));
        self::assertCount(0, $crawler->filter('[data-b2b-home="usual"], [data-b2b-home="restock"], [data-b2b-home="negotiated"]'));
        self::assertDoesNotMatchRegularExpression('/\d+,\d{2}\s?€/u', (string) $client->getResponse()->getContent());
    }

    public function testTheConsumerChannelKeepsTheStockHome(): void
    {
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2C');
        $crawler = $client->request('GET', '/fr_FR/');

        self::assertCount(0, $crawler->filter('[data-b2b-home]'));
        self::assertGreaterThanOrEqual(1, $crawler->filter('.products-grid')->count());
    }

    /** @return list<string> hrefs of the products of a home block */
    private function productsOf(Crawler $crawler, string $block): array
    {
        return array_values(array_unique($crawler->filter('[data-b2b-home="' . $block . '"] a[href*="/products/"]')->each(
            static fn (Crawler $link): string => (string) $link->attr('href'),
        )));
    }

    private function signedIn(string $email): KernelBrowser
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
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
