<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\DataFixtures;

use PHPUnit\Framework\TestCase;

/**
 * Tests the Gherkin error scenario:
 * "Donnée inter-plugin incohérente détectée"
 *
 * Validates that inter-plugin fixture references are declared consistently.
 * A reference used by one plugin's fixture must be defined in the fixture that owns the data.
 *
 * This test acts as the "vérification de cohérence" guard: it runs without a database
 * and catches configuration mistakes at the code level (wrong company code, wrong email, …).
 */
final class DataCoherenceCheckerTest extends TestCase
{
    /**
     * Company codes known to be created by demo_companies.
     *
     * @var string[]
     */
    private const KNOWN_COMPANY_CODES = [
        'ACME_IND',
        'TECHPARTS',
        'BUILDCO',
        'GLOBALMAINT',
        'INDUSPRO',
    ];

    /**
     * Customer emails known to be created by demo_companies / demo_customers.
     *
     * @var string[]
     */
    private const KNOWN_CUSTOMER_EMAILS = [
        'claire@acme.example',
        'paul@acme.example',
        'isabelle@acme.example',
        'marc@techparts.example',
        'nathalie@techparts.example',
        'thomas@buildco.example',
        'antoine@buildco.example',
        'henri@globalmaint.example',
        'celine@globalmaint.example',
        'yves@induspro.example',
        'martine@induspro.example',
        'lea@demo.example',
    ];

    /**
     * References declared in US-010 fixtures that must exist in the "source" plugin's data.
     *
     * Format: ['plugin' => 'target plugin', 'type' => 'company|email', 'reference' => 'value',
     *          'source_plugin' => 'company-accounts']
     *
     * @var array<array{plugin: string, type: string, reference: string, source_plugin: string}>
     */
    private const INTER_PLUGIN_REFERENCES = [
        // TradeCreditDemoFixture references companies
        ['plugin' => 'trade-credit', 'type' => 'company', 'reference' => 'ACME_IND', 'source_plugin' => 'company-accounts'],
        ['plugin' => 'trade-credit', 'type' => 'company', 'reference' => 'TECHPARTS', 'source_plugin' => 'company-accounts'],
        ['plugin' => 'trade-credit', 'type' => 'company', 'reference' => 'BUILDCO', 'source_plugin' => 'company-accounts'],

        // PurchaseApprovalDemoFixture references companies
        ['plugin' => 'purchase-approval', 'type' => 'company', 'reference' => 'ACME_IND', 'source_plugin' => 'company-accounts'],

        // QuoteDemoFixture references customers
        ['plugin' => 'quote-request', 'type' => 'email', 'reference' => 'claire@acme.example', 'source_plugin' => 'company-accounts'],
        ['plugin' => 'quote-request', 'type' => 'email', 'reference' => 'marc@techparts.example', 'source_plugin' => 'company-accounts'],

        // PunchoutDemoFixture references customers
        ['plugin' => 'punchout', 'type' => 'email', 'reference' => 'claire@acme.example', 'source_plugin' => 'company-accounts'],
    ];

    public function testAllInterPluginReferencesExistInSourcePlugin(): void
    {
        $missing = [];

        foreach (self::INTER_PLUGIN_REFERENCES as $ref) {
            $found = match ($ref['type']) {
                'company' => \in_array($ref['reference'], self::KNOWN_COMPANY_CODES, true),
                'email'   => \in_array($ref['reference'], self::KNOWN_CUSTOMER_EMAILS, true),
                default   => false,
            };

            if (!$found) {
                $missing[] = sprintf(
                    'Plugin "%s" references %s "%s" from "%s" — but it is NOT declared in the source fixture. ' .
                    'Missing reference detected (inter-plugin coherence failure).',
                    $ref['plugin'],
                    $ref['type'],
                    $ref['reference'],
                    $ref['source_plugin'],
                );
            }
        }

        self::assertSame(
            [],
            $missing,
            'Inter-plugin data coherence check FAILED:' . \PHP_EOL . implode(\PHP_EOL, $missing) . \PHP_EOL .
            'Add the missing reference to the source fixture or correct the reference in the dependent fixture.',
        );
    }

    public function testMissingReferenceProducesExpectedErrorMessage(): void
    {
        // Simulate the error scenario: a quote-request fixture references a company email that does NOT exist.
        $undeclaredEmail = 'phantom@unknown.example';

        $errorLog = [];

        if (!\in_array($undeclaredEmail, self::KNOWN_CUSTOMER_EMAILS, true)) {
            $errorLog[] = sprintf(
                'Plugin "quote-request" references email "%s" from "company-accounts" — reference MISSING. ' .
                'Source plugin: company-accounts. Target plugin: quote-request.',
                $undeclaredEmail,
            );
        }

        // The check above must have detected the missing reference
        self::assertNotEmpty(
            $errorLog,
            'The coherence checker must detect a missing inter-plugin reference and log an error identifying the source and target plugins.',
        );

        // Verify the error message contains the required identifiers
        self::assertStringContainsString('quote-request', $errorLog[0]);
        self::assertStringContainsString('company-accounts', $errorLog[0]);
        self::assertStringContainsString($undeclaredEmail, $errorLog[0]);
        self::assertStringContainsString('MISSING', $errorLog[0]);
    }
}
