<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/** US-186: the page of the image credits also names the author, the source page and the licence of each photo of the home. */
final class HomePhotoCreditsPageTest extends WebTestCase
{
    private const SLOTS = ['hero', 'epi', 'quincaillerie', 'industriel', 'parcours', 'acheteur', 'approbatrice', 'administrateur'];

    /** @return iterable<string, array{string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR'];
        yield 'English' => ['en_US'];
    }

    #[DataProvider('locales')]
    public function testEveryPhotoOfTheHomeIsCreditedWithItsSourcePageAndLicence(string $locale): void
    {
        $page = self::createClient()->request('GET', '/' . $locale . '/credits-images');

        self::assertResponseIsSuccessful();
        self::assertSame(self::SLOTS, $page->filter('[data-home-photo-credit]')->each(static fn ($item): string => (string) $item->attr('data-home-photo-credit')));
        foreach (self::SLOTS as $slot) {
            $item = $page->filter('[data-home-photo-credit="' . $slot . '"]');
            self::assertStringStartsWith('https://www.pexels.com/photo/', (string) $item->filter('a[data-credit-origin]')->attr('href'), $slot);
            self::assertSame('https://www.pexels.com/license/', $item->filter('a[data-credit-licence]')->attr('href'), $slot);
            self::assertNotSame('', trim($item->filter('[data-credit-creator]')->text()), $slot);
            self::assertNotSame('', trim((string) $item->filter('img')->attr('alt')), $slot . ': the photo is shown with its alternative');
        }
    }

    /**
     * The photos are 800 px files shown as thumbnails: without a width bound, the page was 1046 px wide on a 360 px phone
     * (production check of 2026-10-27). Bootstrap classes only: the page also shows on the B2C channel.
     */
    public function testThePhotosAreBoundedThumbnails(): void
    {
        $page = self::createClient()->request('GET', '/fr_FR/credits-images');

        foreach ($page->filter('[data-home-photo-credit]') as $item) {
            $thumb = (new \Symfony\Component\DomCrawler\Crawler($item))->filter('[data-credit-thumb]');
            self::assertCount(1, $thumb);
            self::assertStringContainsString('max-width: 100%', (string) $thumb->attr('style'));
            self::assertContains('img-fluid', explode(' ', (string) $thumb->filter('img')->attr('class')));
        }
    }

    public function testThePhotosOfTheHomeDoNotChangeTheCountOfTheProductImages(): void
    {
        $page = self::createClient()->request('GET', '/fr_FR/credits-images');

        self::assertSame(0, $page->filter('[data-image-credit] [data-home-photo-credit], [data-home-photo-credit][data-image-credit]')->count());
    }
}
