<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * US-219: the predictive rhythms fixtures generate realistic purchase rhythms (7 to 120 days intervals).
 * If an interval is absurd, the test fails naming the reference, the company and the interval.
 */
final class PredictiveRhythmsFixtureTest extends KernelTestCase
{
    use DatabaseAccess;

    /**
     * US-219: Claire (Acme) has at least 2 reorder alerts visible just after the reset: products with expected
     * reorder date within 7 days. This guarantees the demo shows the feature without waiting months.
     */
    public function testClaireHasAtLeastTwoAlertsJustAfterReset(): void
    {
        $now = $this->demoResetDate();
        $companyId = $this->intValue('SELECT c.id FROM tmonier_company c WHERE c.name = ?', ['Acme Industries']);

        // Count predictions with expected_reorder_date within 7 days from the reset date
        $alertCount = $this->intValue(
            'SELECT COUNT(*)
             FROM tmonier_predictive_reorder_prediction p
             WHERE p.company_id = ?
               AND p.expected_reorder_date IS NOT NULL
               AND p.expected_reorder_date BETWEEN ? AND ?',
            [$companyId, $now->format('Y-m-d'), $now->modify('+7 days')->format('Y-m-d')],
        );

        self::assertGreaterThanOrEqual(
            2,
            $alertCount,
            'Claire (Acme) must have at least 2 reorder alerts visible just after the demo reset (within 7 days).',
        );
    }

    /**
     * US-219 T-219-02: the purchase rhythms must be realistic (intervals between 7 and 120 days). If a reference
     * has an absurd interval (< 7 or > 120 days), this test fails naming the reference, the company and the interval.
     */
    public function testAllPurchaseRhythmsHaveRealisticIntervals(): void
    {
        $rows = $this->db()->fetchAllAssociative(
            'SELECT
                c.name AS company,
                pv.code AS sku,
                o.checkout_completed_at
             FROM sylius_order o
             JOIN sylius_order_item oi ON oi.order_id = o.id
             JOIN sylius_product_variant pv ON pv.id = oi.variant_id
             JOIN tmonier_company c ON c.id = o.company_id
             WHERE o.state IN (?, ?)
               AND o.checkout_completed_at IS NOT NULL
               AND c.name = ?
             ORDER BY c.id, pv.code, o.checkout_completed_at',
            ['fulfilled', 'new', 'Acme Industries'],
        );

        $purchases = [];
        foreach ($rows as $row) {
            $key = $row['company'] . '::' . $row['sku'];
            $purchases[$key][] = new \DateTimeImmutable($row['checkout_completed_at']);
        }

        $absurdIntervals = [];
        foreach ($purchases as $key => $dates) {
            if (\count($dates) < 2) {
                continue; // Need at least 2 orders to compute an interval
            }

            for ($i = 1; $i < \count($dates); ++$i) {
                $interval = $dates[$i]->diff($dates[$i - 1])->days;
                if ($interval < 7 || $interval > 120) {
                    $absurdIntervals[] = sprintf(
                        '%s: %d days between order %d and %d',
                        $key,
                        $interval,
                        $i,
                        $i + 1,
                    );
                }
            }
        }

        self::assertSame(
            [],
            $absurdIntervals,
            'All purchase rhythms must have realistic intervals (7-120 days). Absurd intervals found:',
        );
    }
}
