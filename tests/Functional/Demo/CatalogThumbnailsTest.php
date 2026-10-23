<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/** US-167: the first thumbnails of the catalogue (the LCP) load at once, the first one with a high priority; the others stay lazy. */
final class CatalogThumbnailsTest extends WebTestCase
{
    private const THUMBNAILS = '.products-grid img';

    /** @return iterable<string, array{string}> */
    public static function locales(): iterable
    {
        yield 'fr' => ['fr_FR'];
        yield 'en' => ['en_US'];
    }

    #[DataProvider('locales')]
    public function testTheFirstThumbnailsAreEagerAndTheFirstOneHasAHighPriority(string $locale): void
    {
        $client = self::createClient();

        $page = $client->request('GET', '/' . $locale . '/taxons/visserie');

        self::assertResponseIsSuccessful();
        $images = $page->filter(self::THUMBNAILS);
        self::assertGreaterThan(4, $images->count(), 'The category must have more thumbnails than the eager ones.');
        self::assertSame('high', $images->eq(0)->attr('fetchpriority'));
        foreach ([0, 1, 2, 3] as $position) {
            self::assertSame('eager', $images->eq($position)->attr('loading'), 'Thumbnail ' . $position);
        }
        foreach ([1, 2, 3] as $position) {
            self::assertNull($images->eq($position)->attr('fetchpriority'), 'Thumbnail ' . $position);
        }
    }

    public function testThumbnailsBelowTheFoldStayLazy(): void
    {
        $client = self::createClient();

        $page = $client->request('GET', '/fr_FR/taxons/visserie');

        $images = $page->filter(self::THUMBNAILS);
        self::assertGreaterThan(4, $images->count());
        foreach (range(4, $images->count() - 1) as $position) {
            self::assertSame('lazy', $images->eq($position)->attr('loading'), 'Thumbnail ' . $position);
            self::assertNull($images->eq($position)->attr('fetchpriority'), 'Thumbnail ' . $position);
        }
    }

    public function testACategoryWithoutResultRendersWithoutThumbnail(): void
    {
        $client = self::createClient();

        $page = $client->request('GET', '/fr_FR/taxons/visserie', ['criteria' => ['search' => ['value' => 'zzzz-no-such-product']]]);

        self::assertResponseIsSuccessful();
        self::assertCount(0, $page->filter(self::THUMBNAILS));
    }
}
