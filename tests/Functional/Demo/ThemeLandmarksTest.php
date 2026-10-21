<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * US-072: the pages of the shop are served with page landmarks, a skip link as first focusable element and a polite
 * live region for the flash messages, in French and English. A second `main` or a second `h1` fails the audit.
 */
final class ThemeLandmarksTest extends WebTestCase
{
    /** @return iterable<string, array{string}> */
    public static function pages(): iterable
    {
        foreach (['fr_FR', 'en_US'] as $locale) {
            yield $locale . ' home' => ['/' . $locale . '/'];
            yield $locale . ' plugins' => ['/' . $locale . '/plugins'];
            yield $locale . ' login' => ['/' . $locale . '/login'];
            yield $locale . ' about' => ['/' . $locale . '/a-propos'];
            yield $locale . ' integrate' => ['/' . $locale . '/integrer'];
            yield $locale . ' category' => ['/' . $locale . '/taxons/quincaillerie'];
            yield $locale . ' product' => ['/' . $locale . '/products/outi-001-' . substr($locale, 0, 2)];
        }
    }

    #[DataProvider('pages')]
    public function testEachLandmarkAppearsOnce(string $path): void
    {
        $client = self::createClient();

        $page = $client->request('GET', $path);

        self::assertResponseIsSuccessful($path);
        self::assertCount(1, $page->filter('header'), 'one header');
        self::assertCount(1, $page->filter('main'), 'one main');
        self::assertCount(1, $page->filter('footer'), 'one footer');
        self::assertCount(1, $page->filter('h1'), 'one h1');
        self::assertCount(1, $page->filter('main#main-content[tabindex="-1"]'));
        self::assertCount(0, $page->filter('footer footer, main main, header header, main footer'), 'no nested landmark');
    }

    #[DataProvider('pages')]
    public function testTheSkipLinkIsTheFirstFocusableElementAndReachesTheContent(string $path): void
    {
        $client = self::createClient();

        $page = $client->request('GET', $path);

        $first = $page->filter('body a[href], body button, body input, body select, body textarea, body summary')->first();
        self::assertSame('#main-content', $first->attr('href'), 'first focusable is the skip link');
        self::assertCount(1, $page->filter('a[data-skip-link]'));
        self::assertNotSame('', trim($first->text()));
    }

    public function testTheSkipLinkIsLabelledInEachLanguage(): void
    {
        $client = self::createClient();

        self::assertSame('Aller au contenu', trim($client->request('GET', '/fr_FR/login')->filter('a[data-skip-link]')->text()));
        self::assertSame('Skip to content', trim($client->request('GET', '/en_US/login')->filter('a[data-skip-link]')->text()));
    }

    #[DataProvider('pages')]
    public function testTheFlashMessagesHaveAPoliteLiveRegionAlwaysInThePage(string $path): void
    {
        $client = self::createClient();

        $page = $client->request('GET', $path);

        $region = $page->filter('.sylius-messages[aria-live="polite"]');
        self::assertCount(1, $region);
        self::assertSame('true', $region->attr('aria-atomic'));
        self::assertCount(0, $page->filter('.sylius-messages [role="alert"]'), 'one announcement: the region, not each message');
    }

    public function testTheConsumerChannelHasTheSameLandmarksAndKeepsItsStockFooter(): void
    {
        $client = self::createClient();
        $client->request('GET', '/demo/channel/B2C');

        $page = $client->request('GET', '/fr_FR/');

        self::assertResponseIsSuccessful();
        self::assertCount(1, $page->filter('header'));
        self::assertCount(1, $page->filter('main#main-content'));
        self::assertCount(1, $page->filter('footer'));
        self::assertCount(1, $page->filter('h1'), 'the cart panel title is not a second h1');
        self::assertCount(0, $page->filter('footer footer'));
        self::assertCount(0, $page->filter('[data-demo-footer]'));
    }
}
