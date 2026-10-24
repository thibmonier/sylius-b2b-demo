<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

/** 2026-10-21: the demo proves a skill, it does not sell a service: no commercial wording in the demo texts and templates. */
final class PositioningWordingTest extends TestCase
{
    private const FORBIDDEN = ['votre projet', 'your project', 'parlons-en', "let's talk", 'let us talk', 'calendly', 'prestation', 'rendez-vous', 'book a meeting', 'sous contrat'];

    /** @return iterable<string, array{string}> */
    public static function files(): iterable
    {
        $root = \dirname(__DIR__, 3);
        $names = ['theme_demo', 'journeys', 'integrate', 'shop_layout', 'seo', 'home', 'product'];
        foreach (['fr', 'en'] as $language) {
            foreach ($names as $name) {
                yield $name . '.' . $language => [$root . '/translations/' . $name . '.' . $language . '.yaml'];
            }
            yield 'messages.' . $language => [$root . '/translations/messages.' . $language . '.yaml'];
        }
        foreach (['templates/demo', 'templates/shared', 'templates/shop/theme_b2b', 'templates/shop/plugins'] as $directory) {
            foreach ((new Finder())->files()->in($root . '/' . $directory) as $file) {
                yield substr($file->getPathname(), \strlen($root) + 1) => [$file->getPathname()];
            }
        }
    }

    #[DataProvider('files')]
    public function testNoCommercialWording(string $file): void
    {
        $text = mb_strtolower((string) file_get_contents($file));

        foreach (self::FORBIDDEN as $term) {
            self::assertStringNotContainsString($term, $text, $file);
        }
    }
}
