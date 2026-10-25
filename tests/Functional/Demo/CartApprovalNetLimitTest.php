<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Tests\Support\BuildsDemoCarts;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-202 (T-202-05): the order limit of Claire (500 €) is compared to the total excluding tax (decision of the registry, 2026-10-25).
 * Needs tmonier/sylius-purchase-approval-plugin >= 1.4.0 with `amount_basis: net`. 40 pairs of gloves: about 451 € excluding tax,
 * about 539 € including tax.
 */
final class CartApprovalNetLimitTest extends WebTestCase
{
    use BuildsDemoCarts;

    private const CLAIRE = 'claire@acme.example';

    public function testACartUnderTheLimitExcludingTaxButAboveItIncludingTaxNeedsNoApproval(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $order = $this->cartOf(self::CLAIRE, ['EPI-GANT-001' => 40]);

        $crawler = new Crawler($this->get($client, '/fr_FR/cart/'));

        self::assertLessThan(50000, $order->getTotal() - $order->getTaxTotal());
        self::assertGreaterThan(50000, $order->getTotal());
        self::assertCount(0, $crawler->filter('[data-test-b2b-cart-approval]'), 'The limit is compared to the total excluding tax.');
    }

    public function testACartAboveTheLimitExcludingTaxAnnouncesTheAmountExcludingTax(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $order = $this->cartOf(self::CLAIRE, ['EPI-CASK-001' => 10, 'EPI-CHAU-001' => 6, 'EPI-GANT-001' => 24]);

        $notice = $this->plain((new Crawler($this->get($client, '/fr_FR/cart/')))->filter('[data-test-b2b-cart-approval]')->html());

        self::assertStringContainsString(number_format(($order->getTotal() - $order->getTaxTotal()) / 100, 2, ',', ''), $notice, 'The message announces the amount that is compared.');
        self::assertStringNotContainsString(number_format($order->getTotal() / 100, 2, ',', ''), $notice);
    }
}
