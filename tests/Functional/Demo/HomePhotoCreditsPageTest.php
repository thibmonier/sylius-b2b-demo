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

    public function testThePhotosOfTheHomeDoNotChangeTheCountOfTheProductImages(): void
    {
        $page = self::createClient()->request('GET', '/fr_FR/credits-images');

        self::assertSame(0, $page->filter('[data-image-credit] [data-home-photo-credit], [data-home-photo-credit][data-image-credit]')->count());
    }
}
