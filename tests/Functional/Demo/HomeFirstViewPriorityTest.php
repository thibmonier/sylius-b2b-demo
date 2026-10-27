<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * T-TECH-31-11: the LCP of the home on a phone is the h1, which waits for the three stylesheets. On Slow 4G (1.6 Mbit/s)
 * a high-priority font or photo fetched in the same round delays them: the mobile LCP went from 1324 to 1588 ms when the
 * hero photo and the fonts arrived (US-186). Measured on production with the protocol of w186: 1696 ms before,
 * 1332 ms with the three rules below.
 */
final class HomeFirstViewPriorityTest extends WebTestCase
{
    use EnvironmentSwitch;

    /** @return iterable<string, array{string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR'];
        yield 'English' => ['en_US'];
    }

    #[DataProvider('locales')]
    public function testTheHeroPhotoIsPreloadedForLargeScreensOnly(string $locale): void
    {
        $preload = $this->anonymousHome($locale)->filter('link[rel="preload"][as="image"][imagesrcset]');

        self::assertCount(1, $preload);
        self::assertStringContainsString('min-width', (string) $preload->attr('media'), 'on a phone the photo is under the text');
    }

    #[DataProvider('locales')]
    public function testTheHeroPhotoDoesNotAskForAHighPriorityOnItsOwn(string $locale): void
    {
        $photo = $this->anonymousHome($locale)->filter('img[data-home-photo="hero"]');

        self::assertCount(1, $photo);
        self::assertNull($photo->attr('fetchpriority'));
        self::assertNull($photo->attr('loading'), 'the photo of the first view is never lazy');
    }

    #[DataProvider('locales')]
    public function testNoFontIsPreloadedBeforeTheStylesheets(string $locale): void
    {
        self::assertCount(0, $this->anonymousHome($locale)->filter('link[rel="preload"][as="font"]'));
    }

    private function anonymousHome(string $locale): Crawler
    {
        self::ensureKernelShutdown();
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2B');

        return $client->request('GET', '/' . $locale . '/');
    }
}
