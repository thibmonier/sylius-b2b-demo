<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Translation\TranslatorBagInterface;

/**
 * US-077: the accessible names of the Sylius shop (aria-label) are read in French on the French pages and keep their
 * English wording on the English pages. A key without a French text fails the test and is named in the message.
 */
final class AccessibleLabelsTest extends WebTestCase
{
    /** Keys of the core read as accessible names; the French text is part of the demo, not of the core. */
    private const CORE_KEYS = [
        'sylius.ui.account_button',
        'sylius.ui.account_navigation_desktop',
        'sylius.ui.account_navigation_mobile',
        'sylius.ui.close',
        'sylius.ui.footer_help_links',
        'sylius.ui.footer_informational_links',
        'sylius.ui.sylius_logo_aria',
        'sylius.ui.taxon_navigation_desktop',
        'sylius.ui.taxon_navigation_mobile',
    ];

    /** Wording that the core hard-codes in English. */
    private const ENGLISH_LABELS = [
        'breadcrumbs', 'cart button', 'clear filters', 'search button', 'wishlist button', 'Toggle navigation', 'Close',
        'Account button', 'Account navigation (desktop)', 'Account navigation (mobile)', 'Footer help links',
        'Footer informational links', 'Sylius logo', 'sylius logo',
    ];

    /** @return iterable<string, array{string}> */
    public static function frenchPages(): iterable
    {
        yield 'home' => ['/fr_FR/'];
        yield 'login' => ['/fr_FR/login'];
        yield 'category' => ['/fr_FR/taxons/quincaillerie'];
        yield 'product' => ['/fr_FR/products/outi-001-fr'];
        yield 'plugins' => ['/fr_FR/plugins'];
    }

    public function testEveryCoreKeyHasAFrenchText(): void
    {
        $translator = self::getContainer()->get('translator');
        self::assertInstanceOf(TranslatorBagInterface::class, $translator);
        $catalogue = $translator->getCatalogue('fr');

        $missing = array_values(array_filter(self::CORE_KEYS, static fn (string $key): bool => !$catalogue->defines($key, 'messages') && !$catalogue->defines($key, 'messages+intl-icu')));

        self::assertSame([], $missing, 'Keys without French translation: ' . implode(', ', $missing));
    }

    #[DataProvider('frenchPages')]
    public function testNoAccessibleNameStaysInEnglishOnAFrenchPage(string $path): void
    {
        $page = self::createClient()->request('GET', $path);

        self::assertResponseIsSuccessful($path);
        $labels = $page->filter('[aria-label]')->each(static fn ($node): string => (string) $node->attr('aria-label'));
        $english = array_values(array_intersect($labels, self::ENGLISH_LABELS));

        self::assertSame([], $english, $path . ': accessible names in English: ' . implode(', ', $english));
    }

    public function testTheEnglishPagesKeepTheirEnglishNames(): void
    {
        $page = self::createClient()->request('GET', '/en_US/taxons/quincaillerie');

        $labels = $page->filter('[aria-label]')->each(static fn ($node): string => (string) $node->attr('aria-label'));
        // US-198: the B2B list has its own toolbar (visible labels, no icon button), so the core icon buttons "search button" and "clear filters" are B2C only.
        foreach (['breadcrumbs', 'Toggle navigation', 'Account button'] as $expected) {
            self::assertContains($expected, $labels, $expected);
        }
    }
}
