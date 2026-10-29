<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * US-219: after the demo reset, the predictive analytics table is populated for Acme.
 */
final class PredictiveRhythmsFixtureTest extends KernelTestCase
{
    use DatabaseAccess;

    /**
     * US-219: after the demo reset, the predictive analytics table contains predictions for Acme for each product
     * variant historically ordered, and ONLY for Acme (no other company).
     */
    public function testPredictiveAnalyticsTableIsPopulatedForAcme(): void
    {
        $companyId = $this->intValue('SELECT c.id FROM tmonier_company c WHERE c.name = ?', ['Acme Industries']);

        // 1. At least one prediction exists for Acme
        $predictionCount = $this->intValue(
            'SELECT COUNT(*)
             FROM tmonier_predictive_stored_prediction p
             WHERE p.company_id = ?',
            [$companyId],
        );

        self::assertGreaterThan(
            0,
            $predictionCount,
            'The predictive analytics table must contain at least one prediction for Acme after the demo reset.',
        );

        // 2. The predicted SKUs match exactly the SKUs ordered by Acme (no more, no less)
        $orderedSkus = $this->db()->fetchFirstColumn(
            'SELECT DISTINCT pv.code
             FROM sylius_order o
             JOIN sylius_order_item oi ON oi.order_id = o.id
             JOIN sylius_product_variant pv ON pv.id = oi.variant_id
             JOIN tmonier_company_order co ON co.order_id = o.id
             WHERE co.company_id = ?
               AND o.state IN (?, ?)
               AND o.checkout_completed_at IS NOT NULL
             ORDER BY pv.code',
            [$companyId, 'fulfilled', 'new'],
        );

        $predictedSkus = $this->db()->fetchFirstColumn(
            'SELECT DISTINCT pv.code
             FROM tmonier_predictive_stored_prediction p
             JOIN sylius_product_variant pv ON pv.id = p.variant_id
             WHERE p.company_id = ?
             ORDER BY pv.code',
            [$companyId],
        );

        self::assertSame(
            $orderedSkus,
            $predictedSkus,
            'The predicted SKUs must match exactly the SKUs historically ordered by Acme.',
        );

        // 3. Acme has no prediction for a variant it never ordered
        $acmePredictionsForNeverOrderedVariants = $this->intValue(
            'SELECT COUNT(*)
             FROM tmonier_predictive_stored_prediction p
             WHERE p.company_id = ?
               AND p.variant_id NOT IN (
                   SELECT DISTINCT oi.variant_id
                   FROM sylius_order o
                   JOIN sylius_order_item oi ON oi.order_id = o.id
                   JOIN tmonier_company_order co ON co.order_id = o.id
                   WHERE co.company_id = ?
                     AND o.state IN (?, ?)
                     AND o.checkout_completed_at IS NOT NULL
               )',
            [$companyId, $companyId, 'fulfilled', 'new'],
        );

        self::assertSame(
            0,
            $acmePredictionsForNeverOrderedVariants,
            'Acme must have no prediction for a variant it never ordered.',
        );

        // 4. Each company has predictions only for its own ordered variants
        $companiesWithInvalidPredictions = $this->db()->fetchFirstColumn(
            'SELECT DISTINCT c.name
             FROM tmonier_company c
             JOIN tmonier_predictive_stored_prediction p ON p.company_id = c.id
             WHERE p.variant_id NOT IN (
                 SELECT DISTINCT oi.variant_id
                 FROM sylius_order o
                 JOIN sylius_order_item oi ON oi.order_id = o.id
                 JOIN tmonier_company_order co ON co.order_id = o.id
                 WHERE co.company_id = c.id
                   AND o.state IN (?, ?)
                   AND o.checkout_completed_at IS NOT NULL
             )',
            ['fulfilled', 'new'],
        );

        self::assertSame(
            [],
            $companiesWithInvalidPredictions,
            'Each company must have predictions only for variants it has ordered.',
        );
    }
}
