<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * US-194: the logo of the author beside "Réalisé par Thibaut Monier" in the footer. A decorative image (the name is in the text),
 * 56 px wide on a screen and 48 px on a phone, a file twice that size (112 px), under 8 kB, with its size declared (no layout shift).
 */
final class FooterAuthorLogoTest extends WebTestCase
{
    private const FILE = __DIR__ . '/../../../assets/shop/images/brand/author-logo-112.webp';

    /** @return iterable<string, array{string}> */
    public static function locales(): iterable
    {
        yield 'French' => ['fr_FR'];
        yield 'English' => ['en_US'];
    }

    #[DataProvider('locales')]
    public function testTheLogoIsDecorativeAndItsSizeIsDeclared(string $locale): void
    {
        $logo = self::createClient()->request('GET', '/' . $locale . '/')->filter('[data-demo-footer] img[data-author-logo]');

        self::assertCount(1, $logo);
        self::assertSame('', $logo->attr('alt'), 'decorative: the name is already in the text');
        self::assertSame('56', $logo->attr('width'));
        self::assertSame('56', $logo->attr('height'));
        self::assertSame('lazy', $logo->attr('loading'));
        self::assertSame('async', $logo->attr('decoding'));
        self::assertMatchesRegularExpression('~/build/app/shop/images/brand/author-logo-112\.[0-9a-f]{8}\.webp$~', (string) $logo->attr('src'));
    }

    #[DataProvider('locales')]
    public function testTheLogoIsBesideTheMadeByLineAndLinksNowhere(string $locale): void
    {
        $logo = self::createClient()->request('GET', '/' . $locale . '/')->filter('[data-demo-footer] img[data-author-logo]');

        self::assertCount(0, $logo->filterXPath('ancestor::a'), 'no extra link');
        $row = $logo->ancestors()->first();
        self::assertStringContainsString('d-flex', (string) $row->attr('class'));
        self::assertStringContainsString('align-items-center', (string) $row->attr('class'));
        self::assertStringContainsString('Thibaut Monier', $row->filter('p')->text());
        self::assertSame($logo->getNode(0), $row->children()->first()->getNode(0), 'the logo comes first, on the left');
    }

    public function testTheFileIsTwiceTheDisplayedSizeAndLight(): void
    {
        self::assertFileExists(self::FILE);
        self::assertLessThan(8 * 1024, (int) filesize(self::FILE));
        $size = getimagesize(self::FILE);
        self::assertIsArray($size);
        self::assertSame([112, 112], [$size[0], $size[1]]);
    }

    public function testTheMobileSizeIs48PxAndTheScreenSize56Px(): void
    {
        $style = implode("\n", self::createClient()->request('GET', '/fr_FR/')->filter('[data-demo-footer] style')->each(static fn ($node): string => $node->text(null, false)));

        self::assertMatchesRegularExpression('/\[data-author-logo\]\s*\{[^}]*width:\s*48px/', $style);
        self::assertMatchesRegularExpression('/@media\s*\(min-width:\s*768px\)\s*\{[^@]*\[data-author-logo\]\s*\{[^}]*width:\s*56px/', $style);
    }

    public function testTheCreditsPageDoesNotListTheLogoOfTheAuthor(): void
    {
        $text = self::createClient()->request('GET', '/fr_FR/credits-images')->filter('main')->html();

        self::assertStringNotContainsString('author-logo', $text);
    }
}
