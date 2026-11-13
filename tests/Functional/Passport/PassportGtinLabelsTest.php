<?php

declare(strict_types=1);

namespace App\Tests\Functional\Passport;

use App\Demo\DataFixtures\Passport\Gtin;
use App\Demo\DataFixtures\Passport\PassportSheet;
use App\Tests\Functional\Demo\DemoAdminWebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tmonier\SyliusDigitalProductPassportPlugin\Entity\DigitalProductPassport;

/**
 * US-272: the five demo passports carry a valid fictional GTIN, so the "Labels" tab of the admin shows the GS1 Digital Link
 * and its QR code (it showed "invalid GTIN" while the identifier was the variant code).
 */
final class PassportGtinLabelsTest extends DemoAdminWebTestCase
{
    private const PRODUCTS_WITH_PASSPORT = ['EPI-CASK-001', 'EPI-CASK-002', 'EPI-CASK-003', 'OUTI-026', 'OUTI-028'];

    public function testThePassportsHaveDistinctValidGtinWithTheGs1ExamplePrefix(): void
    {
        $identifiers = $this->passportIdentifiers();

        self::assertCount(6, $identifiers);
        self::assertCount(6, array_unique($identifiers), 'The six GTIN are all different.');
        foreach ($identifiers as $gtin) {
            self::assertTrue(Gtin::isValid($gtin), \sprintf('"%s" has a wrong length or check digit.', $gtin));
            self::assertStringStartsWith(PassportSheet::GTIN_PREFIX, $gtin);
        }
    }

    /** @return iterable<string, array{string, string, string}> */
    public static function productsAndLocales(): iterable
    {
        foreach (self::PRODUCTS_WITH_PASSPORT as $code) {
            yield $code . ' fr' => [$code, 'fr_FR', 'Lien GS1 Digital Link'];
            yield $code . ' en' => [$code, 'en_US', 'GS1 Digital Link'];
        }
    }

    #[DataProvider('productsAndLocales')]
    public function testTheLabelsTabShowsTheGs1LinkAndTheQrCode(string $productCode, string $locale, string $linkLabel): void
    {
        $client = $this->sophieClient($locale);
        $gtin = $this->gtinOf($productCode);

        $crawler = $client->request('GET', '/admin/products/' . $this->productId($productCode) . '/edit');

        self::assertResponseIsSuccessful();
        $tab = $crawler->filter('#labels');
        self::assertCount(1, $tab, 'The Labels tab exists.');
        self::assertStringContainsString($linkLabel, $tab->text());
        self::assertSame('https://id.gs1.org/01/' . $gtin, $tab->filter('a[href^="https://id.gs1.org/01/"]')->attr('href'));
        self::assertCount(1, $tab->filter('svg'), 'The QR code is an SVG.');
        self::assertCount(0, $tab->filter('.alert-warning'), 'No "invalid GTIN" warning.');
    }

    /** @return iterable<string, array{string, string}> */
    public static function noPassportMessages(): iterable
    {
        yield 'fr' => ['fr_FR', 'Aucun passeport produit trouvé'];
        yield 'en' => ['en_US', 'No product passport found'];
    }

    #[DataProvider('noPassportMessages')]
    public function testAProductWithoutPassportShowsThePluginMessageAndNoError(string $locale, string $message): void
    {
        $client = $this->sophieClient($locale);

        $crawler = $client->request('GET', '/admin/products/' . $this->productId('OUTI-001') . '/edit');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString($message, $crawler->filter('#labels')->text());
        self::assertCount(0, $crawler->filter('#labels svg'));
    }

    public function testThePublicPageOfAPassportStillOpensAndShowsItsGtin(): void
    {
        $client = self::createClient();
        $gtin = $this->gtinOf('EPI-CASK-001');
        $passportId = $this->intValue(
            'SELECT p.id FROM tmonier_digital_product_passport p JOIN sylius_product_variant v ON v.id = p.product_variant_id WHERE v.code = ?',
            ['EPI-CASK-001'],
        );

        $crawler = $client->request('GET', '/fr_FR/product-passport/' . $passportId);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Polyéthylène', $crawler->text());
        self::assertStringContainsString($gtin, $crawler->text());
        self::assertStringStartsWith(PassportSheet::GTIN_PREFIX, $gtin);
    }

    /** @return list<string> */
    private function passportIdentifiers(): array
    {
        $manager = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $manager);
        $passports = $manager->getRepository(DigitalProductPassport::class)->findAll();

        return array_map(static fn (DigitalProductPassport $passport): string => (string) $passport->getProductIdentifier(), $passports);
    }

    private function gtinOf(string $productCode): string
    {
        return $this->stringValue(
            'SELECT p.product_identifier FROM tmonier_digital_product_passport p JOIN sylius_product_variant v ON v.id = p.product_variant_id JOIN sylius_product pr ON pr.id = v.product_id WHERE pr.code = ?',
            [$productCode],
        );
    }

    private function productId(string $productCode): int
    {
        return $this->intValue('SELECT id FROM sylius_product WHERE code = ?', [$productCode]);
    }
}
