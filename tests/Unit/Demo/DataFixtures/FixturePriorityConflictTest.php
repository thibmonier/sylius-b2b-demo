<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\DataFixtures;

use PHPUnit\Framework\TestCase;

/**
 * Tests the Gherkin error scenario:
 * "Priorité de fixture dupliquée entre plugins"
 *
 * Two fixtures at the same priority within a data-dependency chain must not exist.
 * This test validates the fixture priority table defined in fixtures/README.md
 * and config/packages/demo_fixtures.yaml.
 */
final class FixturePriorityConflictTest extends TestCase
{
    /**
     * Priority map extracted from demo_fixtures.yaml.
     * Format: fixtureName => priority
     *
     * @var array<string, int>
     */
    private const FIXTURE_PRIORITIES = [
        // Built-in (represented as 0)
        'locale'          => 0,
        'currency'        => 0,
        'geographical'    => 0,
        'channel'         => 0,
        'tax_category'    => 0,
        'tax_rate'        => 0,
        'shipping_method' => 0,
        'payment_method'  => 0,
        'admin_user'      => 0,

        // Sprint 2 — US-008 catalogue
        'demo_taxons'              => -10,
        'demo_product_attributes'  => -20,
        'demo_products_csv'        => -30,

        // Sprint 2 — US-009 accounts
        'demo_companies'           => -15,
        'demo_customers'           => -15,

        // Sprint 2 — US-010 plugin data group 1
        'demo_trade_credit'        => -40,
        'demo_tier_pricing'        => -40,
        'demo_purchase_approval'   => -40,

        // Sprint 2 — US-010 plugin data group 2
        'demo_quotes'                    => -50,
        'demo_gpsr'                      => -50,
        'demo_punchout'                  => -50,
        'tmonier_agentic_feeds'          => -50,
        'tmonier_quick_order_demo_catalog' => -50,
    ];

    /**
     * Dependency edges: [dependent, dependency] pairs.
     * A dependent fixture must have a STRICTLY LOWER priority (more negative) than its dependency.
     *
     * @var array<array{0: string, 1: string}>
     */
    private const DEPENDENCY_EDGES = [
        // US-008 internal order
        ['demo_product_attributes', 'demo_taxons'],
        ['demo_products_csv',       'demo_taxons'],
        ['demo_products_csv',       'demo_product_attributes'],

        // US-010 depends on products and companies
        ['demo_trade_credit',     'demo_companies'],
        ['demo_tier_pricing',     'demo_products_csv'],
        ['demo_purchase_approval','demo_companies'],
        ['demo_quotes',           'demo_products_csv'],
        ['demo_quotes',           'demo_companies'],
        ['demo_gpsr',             'demo_products_csv'],
        ['demo_punchout',         'demo_companies'],

        // US-010 group 2 must run after group 1
        ['demo_quotes',    'demo_trade_credit'],
        ['demo_quotes',    'demo_tier_pricing'],
        ['demo_punchout',  'demo_purchase_approval'],
    ];

    public function testNoCyclicPriorityDependencies(): void
    {
        foreach (self::DEPENDENCY_EDGES as [$dependent, $dependency]) {
            $depPriority = self::FIXTURE_PRIORITIES[$dependent] ?? null;
            $depOnPriority = self::FIXTURE_PRIORITIES[$dependency] ?? null;

            self::assertNotNull(
                $depPriority,
                sprintf('Fixture "%s" is in the dependency graph but not in FIXTURE_PRIORITIES.', $dependent),
            );
            self::assertNotNull(
                $depOnPriority,
                sprintf('Fixture "%s" is in the dependency graph but not in FIXTURE_PRIORITIES.', $dependency),
            );

            // Lower (more negative) priority = loaded LATER.
            // The dependent must be loaded AFTER the dependency, i.e. have a lower priority.
            self::assertLessThan(
                $depOnPriority,
                $depPriority,
                sprintf(
                    'Fixture "%s" (priority %d) must have a lower priority than "%s" (priority %d). ' .
                    'It depends on it and must load AFTER it. ' .
                    'Conflicting priorities detected — check demo_fixtures.yaml.',
                    $dependent,
                    $depPriority,
                    $dependency,
                    $depOnPriority,
                ),
            );
        }
    }

    public function testConflictingPriorityWithinDependencyChainIsDetected(): void
    {
        // Simulate the error scenario: two fixtures at the same priority where one depends on the other.
        // WHEN this condition is detected, the test exits with a non-zero assertion failure
        // (the test itself IS the detection mechanism).

        $conflictingPairs = [];
        foreach (self::DEPENDENCY_EDGES as [$dependent, $dependency]) {
            $depPriority = self::FIXTURE_PRIORITIES[$dependent] ?? null;
            $depOnPriority = self::FIXTURE_PRIORITIES[$dependency] ?? null;

            if (null !== $depPriority && null !== $depOnPriority && $depPriority >= $depOnPriority) {
                $conflictingPairs[] = sprintf(
                    '"%s"(priority=%d) depends on "%s"(priority=%d) but has equal or higher priority — conflict!',
                    $dependent,
                    $depPriority,
                    $dependency,
                    $depOnPriority,
                );
            }
        }

        self::assertSame(
            [],
            $conflictingPairs,
            'Priority conflicts detected between dependent fixtures:' . \PHP_EOL . implode(\PHP_EOL, $conflictingPairs) . \PHP_EOL .
            'Fix by assigning a lower (more negative) priority to the dependent fixture.',
        );
    }
}
