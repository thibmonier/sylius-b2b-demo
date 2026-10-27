<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Plugin;

use App\Demo\Charter\PublicTextCharter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/** US-204: the texts of the "Plugins" page (domain "plugins") follow the public text charter, in both languages. */
final class PluginsTextCharterTest extends TestCase
{
    private const COMPARISONS = ['/sylius\s+plus/i', '/concurren/i', '/competitor/i', '/\bmeilleur\b/i', '/\bbest\b/i', '/\bleader\b/i', '/\bgaranti/i', '/\bguarantee/i', '/gratuit/i', '/\bfree\b/i'];

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
        self::assertSame([], $charter->commercialWording($text), $key);
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

    public function testTheTemplateCarriesNoTextOfItsOwn(): void
    {
        $file = \dirname(__DIR__, 4) . '/templates/shop/plugins/index.html.twig';
        $withoutTags = (string) preg_replace(['/\{#.*?#\}/s', '/\{\{.*?\}\}/s', '/\{%.*?%\}/s', '/<(?:script|style).*?<\/(?:script|style)>/s', '/<[^>]+>/'], ' ', (string) file_get_contents($file));

        self::assertSame('', trim((string) preg_replace('/(?:&[a-z]+;|\s|[|·])+/', '', $withoutTags)));
    }

    private static function load(string $language): array
    {
        $texts = Yaml::parseFile(\dirname(__DIR__, 4) . '/translations/plugins.' . $language . '.yaml');

        return \is_array($texts) ? $texts : [];
    }

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
