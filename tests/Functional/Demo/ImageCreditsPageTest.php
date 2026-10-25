<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Images\ImageManifest;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/** US-061: a public page names the author, the source and the licence of every demo image, straight from the manifest. */
final class ImageCreditsPageTest extends WebTestCase
{
    /** @return iterable<string, array{string, string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR', 'Crédits des images'];
        yield 'English' => ['en_US', 'Image credits'];
    }

    #[DataProvider('locales')]
    public function testEveryImageOfTheManifestIsCreditedWithItsSourceAndLicence(string $locale, string $title): void
    {
        $page = self::createClient()->request('GET', '/' . $locale . '/credits-images');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', $title);
        $manifest = ImageManifest::load(\dirname(__DIR__, 3) . '/fixtures/images/manifest.csv');
        self::assertGreaterThan(0, \count($manifest->files()));
        self::assertCount(\count($manifest->files()), $page->filter('[data-image-credit]'));
        $licences = [];
        foreach ($manifest->rows() as $row) {
            $licences[$row['file']] = $row['license_url'];
        }
        foreach ($manifest->files() as $file) {
            $item = $page->filter('[data-image-credit="' . $file . '"]');
            self::assertCount(1, $item, $file);
            self::assertSame($licences[$file], $item->filter('a[href^="https://"][href*="licen"], a[href*="publicdomain/zero"]')->attr('href'), $file . ': licence link');
            self::assertNotSame('', trim($item->filter('[data-credit-creator]')->text()), $file . ': creator');
            self::assertNotSame('', trim($item->filter('[data-credit-source]')->text()), $file . ': source');
        }
    }

    #[DataProvider('locales')]
    public function testTheFooterLinksTheCreditsPage(string $locale, string $title): void
    {
        $page = self::createClient()->request('GET', '/' . $locale . '/');

        self::assertCount(1, $page->filter('footer [data-demo-footer] a[href="/' . $locale . '/credits-images"]'));
    }
}
