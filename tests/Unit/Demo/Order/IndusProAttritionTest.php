<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Order;

use App\Demo\Order\DemoOrderPlan;
use App\Demo\Order\DemoOrderSpec;
use App\Demo\Order\OrderScenario;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Tmonier\SyliusPredictiveAnalyticsPlugin\Attrition\AttritionScore;
use Tmonier\SyliusPredictiveAnalyticsPlugin\Attrition\AttritionScoreCalculator;
use Tmonier\SyliusPredictiveAnalyticsPlugin\Attrition\AttritionStatus;

/**
 * US-220 (T-220-04): IndusPro is "at risk" whatever the day the demo is reset, because its orders are dated relative to
 * the reset day (never fixed dates) and the plugin's own rule is applied to them with the date injected.
 */
final class IndusProAttritionTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function resetDays(): iterable
    {
        foreach (['2026-11-05 08:00:00', '2026-12-31 23:30:00', '2027-02-28 00:10:00', '2028-02-29 12:00:00', '2029-07-15 09:00:00'] as $day) {
            yield $day => [$day];
        }
    }

    #[DataProvider('resetDays')]
    public function testIndusProIsAtRiskWhateverTheResetDay(string $day): void
    {
        $score = $this->scoreOf('@induspro.example', new \DateTimeImmutable($day));

        self::assertSame(AttritionStatus::AtRisk, $score->status);
    }

    public function testTheRhythmAndTheDelaySupportTheReasonShownInTheAdmin(): void
    {
        $score = $this->scoreOf('@induspro.example', new \DateTimeImmutable('2026-11-05 08:00:00'));

        self::assertSame(30.0, $score->medianIntervalDays);
        self::assertGreaterThan(60, $score->daysSinceLastOrder);
        self::assertLessThanOrEqual(90, $score->daysSinceLastOrder);
        self::assertGreaterThanOrEqual(4, $score->orderCount);
    }

    #[DataProvider('resetDays')]
    public function testTheBusyCompaniesOfTheDemoStayActive(string $day): void
    {
        $now = new \DateTimeImmutable($day);

        self::assertSame(AttritionStatus::Active, $this->scoreOf('@acme.example', $now)->status);
    }

    private function scoreOf(string $emailSuffix, \DateTimeImmutable $now): AttritionScore
    {
        $specs = array_filter(
            DemoOrderPlan::all($now),
            static fn (DemoOrderSpec $spec): bool => str_ends_with($spec->customerEmail, $emailSuffix) &&
                !\in_array($spec->scenario, [OrderScenario::Cancelled, OrderScenario::PendingApproval], true),
        );
        $dates = array_map(static fn (DemoOrderSpec $spec): \DateTimeImmutable => DemoOrderPlan::completedAt($now, $spec->daysAgo), $specs);
        sort($dates);

        return (new AttritionScoreCalculator(new MockClock($now)))->calculate($dates);
    }
}
