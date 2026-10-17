<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/** US-046: the Sylius Plus notice of the Plugins page, as served (French and English). */
final class PluginsPageSyliusPlusTest extends WebTestCase
{
    private const FORBIDDEN = ['gratuit', 'free', 'alternative', 'compatible avec', 'compatible with', 'remplace', 'replace'];

    /** @return iterable<string, array{string, string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR', 'édition commerciale'];
        yield 'English' => ['en_US', 'commercial edition'];
    }

    #[DataProvider('locales')]
    public function testTheNoticeIntroducesSyliusPlusWithTheOfficialLink(string $locale, string $wording): void
    {
        $client = self::createClient();

        $page = $client->request('GET', '/' . $locale . '/plugins');

        self::assertResponseIsSuccessful();
        $notice = $page->filter('[data-sylius-plus-notice]');
        self::assertCount(1, $notice);
        self::assertStringContainsString('Sylius Plus', $notice->text());
        self::assertStringContainsString($wording, $notice->text());
        self::assertStringContainsString('B2B', $notice->text());
        $link = $notice->filter('a[data-sylius-plus-link]');
        self::assertSame('https://sylius.com/plus/', $link->attr('href'));
    }

    #[DataProvider('locales')]
    public function testTheLinkIsSafeAndTheNoticeLoadsNothingExternal(string $locale): void
    {
        $client = self::createClient();

        $notice = $client->request('GET', '/' . $locale . '/plugins')->filter('[data-sylius-plus-notice]');

        $link = $notice->filter('a');
        self::assertCount(1, $link);
        self::assertSame('noopener noreferrer', $link->attr('rel'));
        self::assertSame('_blank', $link->attr('target'));
        self::assertCount(0, $notice->filter('img, script, iframe, link, [src], [style]'), 'no logo, no external resource');
    }

    #[DataProvider('locales')]
    public function testTheWordingMakesNoCompatibilityPromiseNorComparison(string $locale): void
    {
        $client = self::createClient();

        $text = mb_strtolower($client->request('GET', '/' . $locale . '/plugins')->filter('[data-sylius-plus-notice]')->text());

        foreach (self::FORBIDDEN as $term) {
            self::assertStringNotContainsString($term, $text, $term);
        }
        self::assertMatchesRegularExpression('/vérifier|checked/u', $text, 'compatibility is said to be left to check');
    }
}
