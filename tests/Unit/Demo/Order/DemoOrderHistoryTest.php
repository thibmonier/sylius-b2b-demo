<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Order;

use App\Demo\Order\DemoOrderHistory;
use App\Demo\Order\DemoOrderPlan;
use App\Demo\Order\DemoOrderSpec;
use App\Demo\Order\OrderScenario;
use App\Order\OrderPoReferenceAwareInterface;
use PHPUnit\Framework\TestCase;

/** US-155: the 18-month history of the purchasing dashboard. */
final class DemoOrderHistoryTest extends TestCase
{
    private \DateTimeImmutable $now;

    protected function setUp(): void
    {
        $this->now = new \DateTimeImmutable('2026-10-21 08:00:00');
    }

    public function testAcmeHasAboutFortyFiveOrdersOverEighteenMonths(): void
    {
        $acme = $this->acme(DemoOrderPlan::all($this->now));
        $days = array_map(static fn (DemoOrderSpec $spec): int => $spec->daysAgo, $acme);

        self::assertGreaterThanOrEqual(40, count($acme));
        self::assertLessThanOrEqual(55, count($acme));
        self::assertGreaterThanOrEqual(500, max($days));
        self::assertLessThanOrEqual(560, max($days));
        self::assertGreaterThanOrEqual(0, min($days));
    }

    public function testAugustIsTheTroughAndSeptemberThePeakOfTheLastTwelveMonths(): void
    {
        $perMonth = $this->placedPerMonth($this->acme(DemoOrderPlan::all($this->now)), $this->now, '2025-11', '2026-10');

        self::assertCount(12, $perMonth);
        self::assertSame(min($perMonth), $perMonth['2026-08']);
        self::assertSame(max($perMonth), $perMonth['2026-09']);
        self::assertLessThan($perMonth['2026-07'], $perMonth['2026-08']);
    }

    public function testThePreviousYearHasAtLeastSixMonthsToCompareWith(): void
    {
        $perMonth = $this->placedPerMonth($this->acme(DemoOrderPlan::all($this->now)), $this->now, '2025-05', '2025-10');

        self::assertCount(6, $perMonth);
        self::assertSame(min($perMonth), $perMonth['2025-08']);
        self::assertNotContains(0, $perMonth);
    }

    public function testTheTroughStaysInAugustWhateverTheDayOfTheReset(): void
    {
        $now = new \DateTimeImmutable('2027-02-03 09:30:00');
        $perMonth = $this->placedPerMonth($this->acme(DemoOrderPlan::history($now)), $now, '2026-03', '2027-02');

        self::assertSame(min($perMonth), $perMonth['2026-08']);
        self::assertSame(max($perMonth), $perMonth['2026-09']);
    }

    public function testFourBuyersOfAcmeIncludingOneDisabledTwoCostCentersAndTwoSites(): void
    {
        $acme = $this->acme(DemoOrderPlan::all($this->now));

        self::assertCount(4, array_unique(array_map(static fn (DemoOrderSpec $spec): string => $spec->customerEmail, $acme)));
        self::assertContains('sebastien@acme.example', array_map(static fn (DemoOrderSpec $spec): string => $spec->customerEmail, $acme));
        self::assertEqualsCanonicalizing(['ATELIER-01', 'MAINT-02'], array_values(array_unique(array_filter(array_map(static fn (DemoOrderSpec $spec): ?string => $spec->costCenter, $acme)))));
        self::assertSame([null, DemoOrderSpec::SITE_SAINT_PRIEST], $this->sorted(array_unique(array_map(static fn (DemoOrderSpec $spec): ?string => $spec->deliverySite, DemoOrderPlan::history($this->now)))));
    }

    public function testTheDisabledBuyerOrdersNothingAfterHeLeft(): void
    {
        foreach ($this->acme(DemoOrderPlan::all($this->now)) as $spec) {
            if ('sebastien@acme.example' === $spec->customerEmail) {
                self::assertGreaterThan(120, $spec->daysAgo, $spec->poReference);
            }
        }
    }

    public function testAtLeastTenProductsAreBoughtSeveralTimesByAcme(): void
    {
        $times = [];
        foreach ($this->acme(DemoOrderPlan::all($this->now)) as $spec) {
            foreach (array_keys($spec->items) as $code) {
                $times[$code] = ($times[$code] ?? 0) + 1;
            }
        }

        self::assertGreaterThanOrEqual(10, count(array_filter($times, static fn (int $count): bool => $count >= 3)));
    }

    public function testOtherCompaniesBuyTheSameProductsAsAcme(): void
    {
        $plan = DemoOrderPlan::all($this->now);
        $others = array_filter($plan, static fn (DemoOrderSpec $spec): bool => str_ends_with($spec->customerEmail, '@techparts.example') || str_ends_with($spec->customerEmail, '@buildco.example'));
        $acmeCodes = array_merge(...array_map(static fn (DemoOrderSpec $spec): array => array_keys($spec->items), $this->acme($plan)));
        $otherCodes = array_merge(...array_map(static fn (DemoOrderSpec $spec): array => array_keys($spec->items), $others));

        self::assertGreaterThanOrEqual(10, count($others));
        self::assertGreaterThanOrEqual(5, count(array_intersect($acmeCodes, $otherCodes)));
    }

    public function testAnOrderOfABuyerWithALimitStaysSmall(): void
    {
        $prices = ['EPI-GANT-001' => 12.4, 'VISS-002' => 6.5, 'EPI-CASK-001' => 18.9, 'FIXA-001' => 3.9, 'OUTI-001' => 18.5, 'EMBA-001' => 18.9, 'EPI-VEST-001' => 12.9, 'SIGN-001' => 9.9, 'VISS-001' => 8.9, 'PAPE-001' => 18.9];

        foreach (DemoOrderHistory::specs($this->now) as $spec) {
            if (!in_array($spec->customerEmail, ['claire@acme.example', 'sebastien@acme.example'], true)) {
                continue;
            }
            $total = 0.0;
            foreach ($spec->items as $code => $quantity) {
                $total += $prices[$code] * $quantity;
            }
            // 500 EUR limit, tax included (20 %): stay well under it.
            self::assertLessThanOrEqual(380.0, $total, $spec->poReference);
        }
    }

    public function testTheHistoryIsTheSameAtEveryResetOfTheSameDay(): void
    {
        self::assertEquals(DemoOrderPlan::all($this->now), DemoOrderPlan::all($this->now->setTime(23, 15)));
    }

    public function testTheHistoryIsTheSameOrdersOnTheSameDatesOfTheMonthAtResetsOfDifferentDays(): void
    {
        $first = $this->now;
        $later = new \DateTimeImmutable('2026-10-29 18:00:00');

        self::assertSame($this->signature($first), $this->signature($later));
    }

    public function testEveryHistoryOrderIsValidAndUnique(): void
    {
        $plan = DemoOrderPlan::all($this->now);
        $references = array_map(static fn (DemoOrderSpec $spec): string => $spec->poReference, $plan);

        self::assertSame($references, array_values(array_unique($references)));
        foreach (DemoOrderPlan::history($this->now) as $spec) {
            self::assertMatchesRegularExpression(OrderPoReferenceAwareInterface::PO_REFERENCE_PATTERN, $spec->poReference);
            self::assertGreaterThanOrEqual(0, $spec->daysAgo, $spec->poReference);
            self::assertContains($spec->scenario, [OrderScenario::Shipped, OrderScenario::Paid], $spec->poReference);
            self::assertNull($spec->paymentMethod, $spec->poReference);
            self::assertNull($spec->approverEmail, $spec->poReference);
        }
    }

    public function testRecentOrdersAreNotShippedYet(): void
    {
        foreach (DemoOrderPlan::history($this->now) as $spec) {
            $shipped = OrderScenario::Shipped === $spec->scenario;
            self::assertSame($spec->daysAgo > 14, $shipped, $spec->poReference);
        }
    }

    /** @return list<string> */
    private function signature(\DateTimeImmutable $now): array
    {
        return array_map(
            static fn (DemoOrderSpec $spec): string => implode('|', [
                $spec->poReference,
                $spec->customerEmail,
                json_encode($spec->items),
                DemoOrderPlan::completedAt($now, $spec->daysAgo)->format('d'),
                $spec->scenario->value,
            ]),
            DemoOrderPlan::history($now),
        );
    }

    /**
     * @param list<DemoOrderSpec> $plan
     *
     * @return list<DemoOrderSpec>
     */
    private function acme(array $plan): array
    {
        return array_values(array_filter($plan, static fn (DemoOrderSpec $spec): bool => str_ends_with($spec->customerEmail, '@acme.example')));
    }

    /**
     * Orders that count in a dashboard (not cancelled, not waiting for an approval), by month `Y-m` of the period.
     *
     * @param list<DemoOrderSpec> $acme
     *
     * @return array<string, int>
     */
    private function placedPerMonth(array $acme, \DateTimeImmutable $now, string $from, string $to): array
    {
        $months = [];
        for ($month = new \DateTimeImmutable($from . '-01'); $month <= new \DateTimeImmutable($to . '-01'); $month = $month->modify('+1 month')) {
            $months[$month->format('Y-m')] = 0;
        }
        foreach ($acme as $spec) {
            if (in_array($spec->scenario, [OrderScenario::Cancelled, OrderScenario::PendingApproval], true)) {
                continue;
            }
            $key = DemoOrderPlan::completedAt($now, $spec->daysAgo)->format('Y-m');
            if (isset($months[$key])) {
                ++$months[$key];
            }
        }

        return $months;
    }

    /**
     * @param array<int|string, mixed> $values
     *
     * @return list<mixed>
     */
    private function sorted(array $values): array
    {
        $values = array_values($values);
        usort($values, static fn (mixed $a, mixed $b): int => strcmp((string) $a, (string) $b));

        return $values;
    }
}
