<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\AccountPlugins;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * US-203: the account pages of the quote, trade-credit and MCP plugins are overridden in `templates/bundles/` (plugins untouched).
 * A copy drifts when its plugin changes, so the fingerprint (sha256) of every original template of `vendor/` is versioned here:
 * the test fails on a plugin update and names the template whose override must be read again. The `data-test` attributes of the
 * original are also required in the override (source level; the rendered pages are checked in tests/Functional/Demo).
 */
final class PluginTemplateDriftTest extends TestCase
{
    /**
     * Original template of the plugin => [sha256 of the original, override of the demo].
     *
     * @return iterable<string, array{string, string, string}>
     */
    public static function templates(): iterable
    {
        yield 'devis, liste' => [
            'vendor/tmonier/sylius-quote-request-plugin/templates/shop/account/index.html.twig',
            '0112bce3fed77f079ab4787289ce298c94510bfea2e8bf8546d5db73372df3a0',
            'templates/bundles/TmonierSyliusQuoteRequestPlugin/shop/account/index.html.twig',
        ];
        yield 'devis, détail' => [
            'vendor/tmonier/sylius-quote-request-plugin/templates/shop/quote_request/show.html.twig',
            '576b29c0962dc4aafc2586bed308cb8b681dbe0f977d8818b215d523623fd4b7',
            'templates/bundles/TmonierSyliusQuoteRequestPlugin/shop/quote_request/show.html.twig',
        ];
        yield 'paiements à terme, relevé' => [
            'vendor/tmonier/sylius-trade-credit-plugin/templates/shop/statement.html.twig',
            'b9c78ccdeffae359ac2cf87a3b8252b6a3c591329d76b72b391e1d59b61fcbcf',
            'templates/bundles/TmonierSyliusTradeCreditPlugin/shop/statement.html.twig',
        ];
        yield 'assistants IA, connexions' => [
            'vendor/tmonier/sylius-b2b-mcp-plugin/templates/shop/account/connections.html.twig',
            '68706b288514c2801a18023f34b550c0d09c95d3cb9dab608084b3200c8eb6da',
            'templates/bundles/TmonierSyliusB2bMcpPlugin/shop/account/connections.html.twig',
        ];
        yield 'assistants IA, consentement' => [
            'vendor/tmonier/sylius-b2b-mcp-plugin/templates/shop/account/authorize.html.twig',
            '6fe3d05567580020d3c91095140f314ef319813138ae649c7c05bb08e2134c67',
            'templates/bundles/TmonierSyliusB2bMcpPlugin/shop/account/authorize.html.twig',
        ];
    }

    #[DataProvider('templates')]
    public function testTheOriginalTemplateOfThePluginIsTheOneTheOverrideWasWrittenFrom(string $original, string $expected, string $override): void
    {
        $content = self::read($original);

        self::assertNull(self::drift($original, $override, $content, $expected), (string) self::drift($original, $override, $content, $expected));
    }

    #[DataProvider('templates')]
    public function testTheOverrideKeepsEveryDataTestAttributeOfTheOriginal(string $original, string $expected, string $override): void
    {
        $missing = array_values(array_diff(self::dataTests(self::read($original)), self::dataTests(self::read($override))));

        self::assertSame([], $missing, sprintf('La surcharge %s a perdu des attributs data-test de %s : %s.', $override, $original, implode(', ', $missing)));
    }

    public function testAModifiedOriginalIsReportedByNamingTheTemplateToReadAgain(): void
    {
        $original = 'vendor/tmonier/sylius-trade-credit-plugin/templates/shop/statement.html.twig';
        $override = 'templates/bundles/TmonierSyliusTradeCreditPlugin/shop/statement.html.twig';
        $simulated = self::read($original) . "\n{# modification simulée #}\n";

        $message = self::drift($original, $override, $simulated, 'b9c78ccdeffae359ac2cf87a3b8252b6a3c591329d76b72b391e1d59b61fcbcf');

        self::assertNotNull($message);
        self::assertStringContainsString($original, $message);
        self::assertStringContainsString($override, $message);
    }

    private static function drift(string $original, string $override, string $content, string $expected): ?string
    {
        $actual = hash('sha256', $content);
        if ($actual === $expected) {
            return null;
        }

        return sprintf(
            "Le template %s du plugin a changé (sha256 %s, attendu %s).\nRelire la surcharge %s, la mettre à jour si besoin, puis versionner la nouvelle empreinte dans ce test.",
            $original,
            $actual,
            $expected,
            $override,
        );
    }

    /** @return list<string> */
    private static function dataTests(string $twig): array
    {
        preg_match_all('/sylius_test_html_attribute\(\s*\'([^\']+)\'/', $twig, $matches);

        return array_values(array_unique($matches[1]));
    }

    private static function read(string $relative): string
    {
        $path = \dirname(__DIR__, 4) . '/' . $relative;
        $content = is_file($path) ? file_get_contents($path) : false;
        self::assertIsString($content, sprintf('Le fichier %s est introuvable.', $relative));

        return $content;
    }
}
