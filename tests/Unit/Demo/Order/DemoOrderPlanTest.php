<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Order;

use App\Demo\Order\DemoOrderPlan;
use App\Demo\Order\OrderScenario;
use App\Order\OrderPoReferenceAwareInterface;
use PHPUnit\Framework\TestCase;

final class DemoOrderPlanTest extends TestCase
{
    public function testThePlanHasBetweenEightAndFifteenOrders(): void
    {
        self::assertGreaterThanOrEqual(8, count(DemoOrderPlan::all()));
        self::assertLessThanOrEqual(15, count(DemoOrderPlan::all()));
    }

    /** US-096: Claire's company shows 6 to 8 orders over six months. */
    public function testAcmeHasSixToEightOrdersSpreadOverSixMonths(): void
    {
        $days = [];
        foreach (DemoOrderPlan::all() as $spec) {
            if (str_ends_with($spec->customerEmail, '@acme.example')) {
                $days[] = $spec->daysAgo;
            }
        }

        self::assertGreaterThanOrEqual(6, count($days));
        self::assertLessThanOrEqual(8, count($days));
        self::assertGreaterThanOrEqual(150, max([0, ...$days]));
    }

    /** US-097: open items on the credit account, one of them overdue (net 30), one company above its limit. */
    public function testOpenItemsArePaidOnAccountAndOneIsOverdue(): void
    {
        $onAccount = array_filter(DemoOrderPlan::all(), static fn ($spec): bool => DemoOrderPlan::ON_ACCOUNT === $spec->paymentMethod);

        self::assertGreaterThanOrEqual(3, count($onAccount));
        self::assertNotSame([], array_filter($onAccount, static fn ($spec): bool => $spec->daysAgo > 30));
        foreach ($onAccount as $spec) {
            self::assertSame(OrderScenario::AwaitingPayment, $spec->scenario, $spec->poReference);
        }
    }

    public function testPoReferencesAreUniqueAndValid(): void
    {
        $references = array_map(static fn ($spec): string => $spec->poReference, DemoOrderPlan::all());

        self::assertSame($references, array_values(array_unique($references)));
        foreach ($references as $reference) {
            self::assertMatchesRegularExpression(OrderPoReferenceAwareInterface::PO_REFERENCE_PATTERN, $reference);
            self::assertLessThanOrEqual(OrderPoReferenceAwareInterface::PO_REFERENCE_MAX_LENGTH, strlen($reference));
        }
    }

    public function testCostCentersAreValid(): void
    {
        foreach (DemoOrderPlan::all() as $spec) {
            if (null === $spec->costCenter) {
                continue;
            }
            self::assertMatchesRegularExpression(OrderPoReferenceAwareInterface::COST_CENTER_PATTERN, $spec->costCenter);
            self::assertLessThanOrEqual(OrderPoReferenceAwareInterface::COST_CENTER_MAX_LENGTH, strlen($spec->costCenter));
        }
    }

    public function testEveryLineHasAKnownVariantCodeAndAPositiveQuantity(): void
    {
        foreach (DemoOrderPlan::all() as $spec) {
            self::assertNotSame([], $spec->items, $spec->poReference);
            foreach ($spec->items as $code => $quantity) {
                self::assertMatchesRegularExpression('/^[A-Z]+(-[A-Z]+)?-\d{3}$/', (string) $code);
                self::assertGreaterThan(0, $quantity);
            }
        }
    }

    public function testOrdersSpanSixMonthsAndNeverLieInTheFuture(): void
    {
        /** @var non-empty-list<int> $days */
        $days = array_map(static fn ($spec): int => $spec->daysAgo, DemoOrderPlan::all());

        self::assertGreaterThanOrEqual(0, min($days));
        self::assertGreaterThanOrEqual(150, max($days));
        self::assertLessThanOrEqual(190, max($days));
    }

    public function testEveryCompanyShowsAtLeastThreeScenarios(): void
    {
        $byCompany = [];
        foreach (DemoOrderPlan::all() as $spec) {
            $byCompany[explode('@', $spec->customerEmail)[1]][$spec->scenario->value] = true;
        }

        foreach (['acme.example', 'techparts.example', 'globalmaint.example'] as $domain) {
            self::assertGreaterThanOrEqual(3, count($byCompany[$domain] ?? []), $domain);
        }
    }

    public function testTheHistoryCoversTheMainStates(): void
    {
        $scenarios = array_map(static fn ($spec): OrderScenario => $spec->scenario, DemoOrderPlan::all());

        foreach ([OrderScenario::Shipped, OrderScenario::Paid, OrderScenario::PendingApproval, OrderScenario::Cancelled, OrderScenario::Refunded] as $expected) {
            self::assertContains($expected, $scenarios);
        }
    }

    public function testAnApprovedOrderNamesItsApprover(): void
    {
        foreach (DemoOrderPlan::all() as $spec) {
            if (OrderScenario::ApprovedShipped === $spec->scenario) {
                self::assertNotNull($spec->approverEmail, $spec->poReference);
            }
        }
    }

    public function testCompletedAtIsTenInTheMorningDaysBeforeNow(): void
    {
        $now = new \DateTimeImmutable('2026-10-16 15:42:10');

        self::assertSame('2026-10-12 10:00:00', DemoOrderPlan::completedAt($now, 4)->format('Y-m-d H:i:s'));
        self::assertSame('2026-10-16 10:00:00', DemoOrderPlan::completedAt($now, 0)->format('Y-m-d H:i:s'));
    }

    public function testCompletedAtIsDeterministicForTheSameDay(): void
    {
        $morning = new \DateTimeImmutable('2026-10-16 08:00:00');
        $evening = new \DateTimeImmutable('2026-10-16 23:00:00');

        self::assertEquals(DemoOrderPlan::completedAt($morning, 90), DemoOrderPlan::completedAt($evening, 90));
    }
}
