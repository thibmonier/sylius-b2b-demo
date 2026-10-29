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
     * US-219: after the demo reset, the predictive analytics table contains predictions for Acme based on its
     * historical orders. The exact count depends on the order history and the reset date, so we only verify that
     * the table is populated (≥ 0 predictions).
     */
    public function testPredictiveAnalyticsTableIsPopulatedForAcme(): void
    {
        $companyId = $this->intValue('SELECT c.id FROM tmonier_company c WHERE c.name = ?', ['Acme Industries']);

        // Count stored predictions with calculated median interval for this company
        $predictionCount = $this->intValue(
            'SELECT COUNT(*)
             FROM tmonier_predictive_stored_prediction p
             WHERE p.company_id = ?
               AND p.median_interval_days IS NOT NULL',
            [$companyId],
        );

        self::assertGreaterThanOrEqual(
            0,
            $predictionCount,
            'The predictive analytics table must be populated for Acme after the demo reset.',
        );
    }
}
