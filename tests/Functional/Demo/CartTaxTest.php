<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Tests\Support\BuildsDemoCarts;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * US-202: the products of the demo carry the tax category "standard" (VAT 20 %, zone FR, not included in the price): the B2B prices stay
 * excluding tax, the cart shows the VAT line and the total including tax. Run against a database loaded with the `demo` suite; the
 * carts are created inside a transaction that is rolled back.
 */
final class CartTaxTest extends WebTestCase
{
    use BuildsDemoCarts;
    use DatabaseAccess;

    private const CLAIRE = 'claire@acme.example';

    private const THREE_LINES = ['EPI-CASK-001' => 10, 'EPI-CHAU-001' => 6, 'EPI-GANT-001' => 24];

    public function testEveryVariantOfTheCatalogueCarriesTheStandardTaxCategory(): void
    {
        self::bootKernel();

        self::assertGreaterThan(0, $this->intValue('SELECT COUNT(*) FROM sylius_product_variant'));
        self::assertSame(0, $this->intValue('SELECT COUNT(*) FROM sylius_product_variant v LEFT JOIN sylius_tax_category c ON c.id = v.tax_category_id WHERE c.code IS NULL OR c.code <> ?', ['standard']), 'No variant without the standard category.');
    }

    public function testTheVariantsTouchedByTheErpImportCarryTheCategoryAndTheTaxIsComputedOnThem(): void
    {
        $this->clientOf(self::CLAIRE);
        $directory = self::getContainer()->getParameter('tmonier_erp_flatfile.exchange_directory');
        self::assertIsString($directory);
        $codes = [];
        foreach (glob($directory . '/processed/*.csv') ?: [] as $file) {
            foreach (array_slice(file($file, \FILE_IGNORE_NEW_LINES) ?: [], 1) as $line) {
                $codes[] = explode(';', $line)[0];
            }
        }
        $codes = array_values(array_unique(array_filter($codes, static fn (string $code): bool => '' !== $code)));
        self::assertNotSame([], $codes, 'The simulated ERP imported variants of the catalogue.');

        $order = $this->cartOf(self::CLAIRE, [$codes[0] => 1]);

        self::assertGreaterThan(0, $order->getTaxTotal(), 'The imported variant is taxed.');
    }

    public function testTheCartShowsTheSubtotalExcludingTaxTheShippingTheVatAndTheTotalIncludingTax(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $order = $this->cartOf(self::CLAIRE, self::THREE_LINES);

        $crawler = new Crawler($this->get($client, '/fr_FR/cart/'));
        $summary = $this->plain($crawler->filter('[data-test-b2b-cart-summary]')->html());

        $beforeTax = $order->getTotal() - $order->getTaxTotal() - $order->getShippingTotal();
        self::assertEqualsWithDelta((int) round($beforeTax * 0.2), $order->getTaxTotal(), 40, 'VAT 20 % of the items after reductions (cent rounding per unit).');
        self::assertSame(1500, $order->getShippingTotal(), 'The shipping is not taxed.');
        self::assertStringContainsString('Sous-total HT', $summary);
        self::assertStringContainsString('797,20', $summary, 'The prices excluding tax did not move.');
        self::assertStringContainsString('15,00', $summary, 'The shipping.');
        self::assertCount(1, $crawler->filter('[data-test-b2b-cart-vat]'), 'The VAT line is back.');
        self::assertStringContainsString(number_format($order->getTaxTotal() / 100, 2, ',', ''), $this->plain($crawler->filter('[data-test-b2b-cart-vat]')->html()));
        self::assertStringContainsString('Total TTC', $summary);
        self::assertStringContainsString(number_format($order->getTotal() / 100, 2, ',', ''), $this->plain($crawler->filter('[data-test-b2b-cart-total]')->html()));
    }

    public function testTheEnglishCartLabelsTheVatAndTheTotalIncludingTax(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $this->cartOf(self::CLAIRE, self::THREE_LINES);

        $crawler = new Crawler($this->get($client, '/en_US/cart/'));

        self::assertCount(1, $crawler->filter('[data-test-b2b-cart-vat]'));
        self::assertStringContainsString('Total incl. tax', $this->plain($crawler->filter('[data-test-b2b-cart-summary]')->html()));
    }

    public function testThePricesExcludingTaxOfTheListAndTheProductPageDidNotMove(): void
    {
        $client = $this->clientOf(self::CLAIRE);
        $product = $this->get($client, '/fr_FR/products/epi-cask-001-fr');

        self::assertStringContainsString('18,90', $this->plain($product), 'The list price of the helmet, excluding tax.');
        self::assertSame(1890, $this->intValue("SELECT cp.price FROM sylius_channel_pricing cp JOIN sylius_product_variant v ON v.id = cp.product_variant_id WHERE v.code = 'EPI-CASK-001' AND cp.channel_code = 'B2B'"));
    }

    public function testThePlacedOrderKeepsItsVatAndItsTotalIncludingTax(): void
    {
        $this->clientOf(self::CLAIRE);
        $order = $this->cartOf(self::CLAIRE, ['EPI-GANT-001' => 24]);

        self::assertSame($order->getItemsTotal() + $order->getAdjustmentsTotal(), $order->getTotal());
        self::assertGreaterThan(0, $order->getTaxTotal());
        self::assertGreaterThan(0, $this->intValue('SELECT COUNT(*) FROM sylius_adjustment WHERE type = ? AND order_id = ? OR order_item_id IN (SELECT id FROM sylius_order_item WHERE order_id = ?) OR order_item_unit_id IN (SELECT u.id FROM sylius_order_item_unit u JOIN sylius_order_item i ON i.id = u.order_item_id WHERE i.order_id = ?)', ['tax', $order->getId(), $order->getId(), $order->getId()]), 'The tax adjustments are persisted with the order.');
    }
}
