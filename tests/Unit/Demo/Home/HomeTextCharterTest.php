<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Home;

use App\Demo\Charter\PublicTextCharter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/** US-186: the texts of the home (domain `home`) are neutral, sourced, in both languages and never compare with an offer. */
final class HomeTextCharterTest extends TestCase
{
    /** Names of shop features, not offers: the quote request is a plugin of the demo (the charter flags the word "devis"). */
    private const FEATURE_NAMES = ['services.quote.title', 'nav.quote'];

    private const COMPARISONS = ['/sylius\s+plus/i', '/concurren/i', '/competitor/i', '/\bmeilleur\b/i', '/\bbest\b/i', '/\bleader\b/i', '/\bgaranti/i', '/\bguarantee/i'];

    /** @return iterable<string, array{string, string, string}> */
    public static function texts(): iterable
    {
        foreach (['fr', 'en'] as $language) {
            foreach (self::flatten(self::load($language), '') as $key => $text) {
                yield $language . ' ' . $key => [$key, $text, $language];
            }
        }
    }

    #[DataProvider('texts')]
    public function testEveryTextPassesTheCharter(string $key, string $text, string $language): void
    {
        $charter = new PublicTextCharter();

        self::assertSame([], $charter->ambiguities($text), $key);
        if (!\in_array($key, self::FEATURE_NAMES, true)) {
            self::assertSame([], $charter->commercialWording($text), $key);
        }
        self::assertSame([], $charter->unsourcedFigures($text), $key);
        foreach (self::COMPARISONS as $pattern) {
            self::assertSame(0, preg_match($pattern, $text), $key . ' compares or promises: ' . $text);
        }
    }

    public function testBothLanguagesCarryTheSameKeys(): void
    {
        $french = array_keys(self::flatten(self::load('fr'), ''));
        $english = array_keys(self::flatten(self::load('en'), ''));

        self::assertNotSame([], $french);
        self::assertSame([], array_values(array_diff($french, $english)), 'missing in English');
        self::assertSame([], array_values(array_diff($english, $french)), 'missing in French');
    }

    public function testTheTemplatesOfTheHomeCarryNoTextOfTheirOwn(): void
    {
        $root = \dirname(__DIR__, 4);
        foreach (glob($root . '/templates/shop/theme_b2b/home_*.html.twig') ?: [] as $file) {
            $withoutTags = (string) preg_replace(['/\{#.*?#\}/s', '/\{\{.*?\}\}/s', '/\{%.*?%\}/s', '/<(?:script|style).*?<\/(?:script|style)>/s', '/<[^>]+>/'], ' ', (string) file_get_contents($file));
            self::assertSame('', trim((string) preg_replace('/(?:&[a-z]+;|\s|[|·])+/', '', $withoutTags)), basename($file) . ' has a text written in the template');
        }
    }

    /** @return array<mixed> */
    private static function load(string $language): array
    {
        $texts = Yaml::parseFile(\dirname(__DIR__, 4) . '/translations/home.' . $language . '.yaml');

        return \is_array($texts) ? $texts : [];
    }

    /**
     * @param array<mixed> $tree
     *
     * @return array<string, string>
     */
    private static function flatten(array $tree, string $prefix): array
    {
        $flat = [];
        foreach ($tree as $key => $value) {
            if (\is_array($value)) {
                $flat += self::flatten($value, $prefix . $key . '.');

                continue;
            }
            $flat[$prefix . $key] = \is_scalar($value) ? (string) $value : '';
        }

        return $flat;
    }
}
