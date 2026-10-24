<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Product;

use App\Demo\Charter\PublicTextCharter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/** US-189: the texts of the product page (domain `product`) are neutral, sourced, in both languages, and the templates carry none of their own. */
final class ProductTextCharterTest extends TestCase
{
    private const COMPARISONS = ['/sylius\s+plus/i', '/concurren/i', '/competitor/i', '/\bmeilleur\b/i', '/\bbest\b/i', '/\bleader\b/i', '/\bgaranti/i', '/\bguarantee/i'];

    /** @return iterable<string, array{string, string}> */
    public static function texts(): iterable
    {
        foreach (['fr', 'en'] as $language) {
            foreach (self::flatten(self::load($language), '') as $key => $text) {
                yield $language . ' ' . $key => [$key, $text];
            }
        }
    }

    #[DataProvider('texts')]
    public function testEveryTextPassesTheCharter(string $key, string $text): void
    {
        $charter = new PublicTextCharter();

        self::assertSame([], $charter->ambiguities($text), $key);
        if (!str_contains($key, 'quote')) {
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

    public function testTheTemplatesOfTheProductPageCarryNoTextOfTheirOwn(): void
    {
        $files = glob(\dirname(__DIR__, 4) . '/templates/shop/theme_b2b/pdp/*.html.twig') ?: [];

        self::assertNotSame([], $files, 'the product page templates');
        foreach ($files as $file) {
            $withoutTags = (string) preg_replace(['/\{#.*?#\}/s', '/\{\{.*?\}\}/s', '/\{%.*?%\}/s', '/<(?:script|style).*?<\/(?:script|style)>/s', '/<[^>]+>/'], ' ', (string) file_get_contents($file));
            self::assertSame('', trim((string) preg_replace('/(?:&[a-z]+;|\s|[|·\/])+/', '', $withoutTags)), basename($file) . ' has a text written in the template');
        }
    }

    /** @return array<mixed> */
    private static function load(string $language): array
    {
        $texts = Yaml::parseFile(\dirname(__DIR__, 4) . '/translations/product.' . $language . '.yaml');

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
