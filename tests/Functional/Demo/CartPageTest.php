<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Channel\DemoChannelContext;
use App\Tests\Support\BuildsDemoCarts;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-200: the cart page of the B2B channel, in the continuation of the home. Run against a database loaded with the `demo`
 * suite; the carts are created inside a transaction that is rolled back. The assertions are on the served HTML.
 *
 * Lines of the mock-up: EPI-CASK-001 (list price 18,90 €), EPI-CHAU-001 (69,90 €), EPI-GANT-001 (12,40 €), each with a
 * 12 % volume price from 2 pieces. Claire has an order limit of 500 €; Paul has none.
 */
final class CartPageTest extends WebTestCase
{
    use BuildsDemoCarts;

    private const CLAIRE = 'claire@acme.example';

    private const PAUL = 'paul@acme.example';

    private const LEA = 'lea@demo.example';

    private const THREE_LINES = ['EPI-CASK-001' => 10, 'EPI-CHAU-001' => 6, 'EPI-GANT-001' => 24];

    public function testClaireReadsEveryLineWithItsReferenceQuantityUnitPriceAndTotal(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $this->cartOf(self::CLAIRE, self::THREE_LINES);

        $html = $this->get($client, '/fr_FR/cart/');
        $crawler = new Crawler($html);

        self::assertCount(3, $crawler->filter('[data-test-b2b-cart-line]'), 'One row per line.');
        $casque = $this->plain($crawler->filter('[data-test-b2b-cart-line]')->eq(0)->html());
        self::assertStringContainsString('Réf. EPI-CASK-001', $casque);
        self::assertStringContainsString('16,63', $casque, 'Unit price excluding tax after the volume price.');
        self::assertStringContainsString('18,90', $casque, 'The catalogue price, struck through.');
        self::assertStringContainsString('166,30', $casque, 'Total of the line.');
        self::assertSame('10', $crawler->filter('[data-test-b2b-cart-line]')->eq(0)->filter('input[name$="[quantity]"]')->attr('value'));
    }

    public function testTheSummaryAddsUpWhatTheShopComputes(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $order = $this->cartOf(self::CLAIRE, self::THREE_LINES);

        $crawler = new Crawler($this->get($client, '/fr_FR/cart/'));
        $summary = $this->plain($crawler->filter('[data-test-b2b-cart-summary]')->html());

        self::assertStringContainsString('Sous-total HT', $summary);
        self::assertStringContainsString('797,20', $summary);
        self::assertStringContainsString('dont remise de prix négocié', $summary, 'The reduction of Acme comes from the price of her company.');
        self::assertStringContainsString('108,80', $summary);
        self::assertStringContainsString('Valider la commande', $this->plain($crawler->filter('[data-test-b2b-cart-aside]')->html()), 'The validation sits under the summary.');
        self::assertStringContainsString(number_format($order->getTotal() / 100, 2, ',', ''), $this->plain($crawler->filter('[data-test-b2b-cart-total]')->html()), 'The total is the one of the order.');
        self::assertSame(0, $order->getTaxTotal(), 'The products of the demo carry no tax category: the shop computes no tax.');
        self::assertCount(0, $crawler->filter('[data-test-b2b-cart-vat]'), 'A tax that the shop does not compute is not shown.');
    }

    public function testAVolumePriceShowsOnlyOnTheLinesThatReachTheTierAndKeepsTheOthers(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $this->cartOf(self::CLAIRE, ['EPI-CASK-001' => 1, 'EPI-GANT-001' => 24]);

        $crawler = new Crawler($this->get($client, '/fr_FR/cart/'));

        self::assertCount(2, $crawler->filter('[data-test-b2b-cart-line]'), 'Both lines stay.');
        self::assertCount(1, $crawler->filter('[data-test-b2b-cart-tier]'), 'Only the line that reaches 2 pieces carries the tier.');
        self::assertStringContainsString('Prix négocié Acme Industries dès 2 pièces', $this->plain($crawler->filter('[data-test-b2b-cart-tier]')->text()));
        self::assertStringContainsString('−12', $this->plain($crawler->filter('[data-test-b2b-cart-tier]')->text()));
        self::assertStringContainsString('dont −35,76', $this->plain($crawler->filter('[data-test-b2b-cart-line-discount]')->text()));

        $this->cartOf(self::CLAIRE, ['EPI-CASK-001' => 2, 'EPI-GANT-001' => 24]);
        $crawler = new Crawler($this->get($client, '/fr_FR/cart/'));

        self::assertCount(2, $crawler->filter('[data-test-b2b-cart-tier]'), 'Two pieces reach the tier.');
    }

    public function testAnOrderAboveTheLimitOfTheBuyerIsAnnouncedBeforeTheValidation(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $this->cartOf(self::CLAIRE, self::THREE_LINES);

        $html = $this->get($client, '/fr_FR/cart/');
        $notice = $this->plain((new Crawler($html))->filter('[data-test-b2b-cart-approval]')->html());

        self::assertStringContainsString('Commande soumise à approbation', $notice);
        self::assertStringContainsString('500,00', $notice, 'Her order limit.');
        self::assertStringContainsString('Acme Industries', $notice);
        self::assertStringContainsString('Paul', $notice, 'An approver of the company.');

        $english = $this->plain((new Crawler($this->get($client, '/en_US/cart/')))->filter('[data-test-b2b-cart-approval]')->html());
        self::assertStringContainsString('Order submitted for approval', $english);
    }

    public function testNoApprovalNoticeUnderTheLimit(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $this->cartOf(self::CLAIRE, ['EPI-GANT-001' => 1]);

        $crawler = new Crawler($this->get($client, '/fr_FR/cart/'));

        self::assertCount(1, $crawler->filter('[data-test-b2b-cart-line]'), 'The new cart is the one shown.');
        self::assertCount(0, $crawler->filter('[data-test-b2b-cart-approval]'), 'Under her limit.');
    }

    public function testNoApprovalNoticeForABuyerWithoutAnOrderLimit(): void
    {
        $client = $this->clientOf(self::PAUL);
        $this->cartOf(self::PAUL, self::THREE_LINES);

        $crawler = new Crawler($this->get($client, '/fr_FR/cart/'));

        self::assertCount(3, $crawler->filter('[data-test-b2b-cart-line]'));
        self::assertCount(0, $crawler->filter('[data-test-b2b-cart-approval]'), 'Paul has no order limit.');
    }

    public function testTheQuoteAndTheQuickOrderStayWithinReachAndTheQuoteSucceeds(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $this->cartOf(self::CLAIRE, self::THREE_LINES);

        $crawler = $client->request('GET', '/fr_FR/cart/');
        self::assertResponseIsSuccessful();
        self::assertCount(1, $crawler->filter('[data-test-b2b-cart-quick-order] form[action$="/quick-order/preview"]'));
        self::assertCount(1, $crawler->filter('[data-test-tmonier-request-quote-from-cart-button]'));
        self::assertCount(0, $crawler->filter('[data-test-b2b-cart-save-list]'), 'No "save to a list" action in the cart.');

        $client->submit($crawler->filter('[data-test-tmonier-request-quote-from-cart-button]')->form());
        self::assertResponseRedirects();
    }

    public function testAnEmptyCartOffersTheCatalogueAndTheQuickOrder(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $this->emptyTheCartsOf(self::CLAIRE);

        $crawler = new Crawler($this->get($client, '/fr_FR/cart/'));

        self::assertCount(1, $crawler->filter('[data-test-b2b-cart-empty]'));
        self::assertStringContainsString('Votre panier est vide', $crawler->filter('[data-test-b2b-cart-empty]')->text());
        self::assertCount(1, $crawler->filter('[data-test-b2b-cart-empty-catalogue]'));
        self::assertStringContainsString('/quick-order', (string) $crawler->filter('[data-test-b2b-cart-empty-quick-order]')->attr('href'));
        self::assertCount(0, $crawler->filter('[data-test-b2b-cart-summary]'));
    }

    public function testTheCartPageGivesTheTotalAndTheMainActionLandmarks(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $this->cartOf(self::CLAIRE, self::THREE_LINES);

        $crawler = new Crawler($this->get($client, '/fr_FR/cart/'));

        self::assertCount(1, $crawler->filter('nav[aria-label="Étapes de la commande"] [aria-current="step"]'));
        self::assertCount(1, $crawler->filter('aside[aria-labelledby] [data-test-b2b-cart-total]'), 'The total sits in the labelled summary that the stylesheet keeps in view (sticky, or at the foot of a phone).');
        self::assertCount(1, $crawler->filter('aside [data-test-cart-checkout-button]'));
        foreach ($crawler->filter('[data-test-b2b-cart-line] button')->extract(['aria-label']) as $name) {
            self::assertNotSame('', trim((string) $name), 'Every button of a line has an accessible name.');
        }
    }

    public function testTheB2cCartIsUnchanged(): void
    {
        $client = $this->clientOf(self::LEA);
        $client->getCookieJar()->set(new Cookie(DemoChannelContext::COOKIE, DemoChannelContext::B2C));
        $this->cartOf(self::LEA, ['EMBA-001' => 10], 'B2C');

        $html = $this->get($client, '/fr_FR/cart/');

        self::assertStringContainsString('data-test-cart-item-product-row', $html, 'The core table of the lines.');
        self::assertStringNotContainsString('data-test-b2b-cart', $html);
        self::assertStringNotContainsString('b2b-cart', $html);
    }
}
