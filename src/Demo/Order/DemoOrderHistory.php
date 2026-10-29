<?php

declare(strict_types=1);

namespace App\Demo\Order;

/**
 * US-155: the 18-month purchase history that fills the purchasing dashboard of the demo (company-insights).
 *
 * No random part: every order comes from the tables below, so a reset gives the same orders. Dates follow the
 * calendar of the day of the reset (the month a few slots back, a fixed day of that month), which keeps the August
 * trough and the September peak where a visitor expects them whatever the day the demo is reset.
 */
final class DemoOrderHistory
{
    /** Months before the current one that the history covers (the current month adds one order). */
    private const MONTHS_BACK = 17;

    /** Acme orders per calendar month (1 = January): quiet summer, peak after the holidays. */
    private const ACME_ORDERS_PER_MONTH = [1 => 2, 2 => 2, 3 => 2, 4 => 2, 5 => 2, 6 => 2, 7 => 2, 8 => 1, 9 => 4, 10 => 3, 11 => 2, 12 => 2];

    /** Quantity percentage of the baskets of the buyers who have no order limit, by calendar month. */
    private const SEASON_PERCENT = [8 => 60, 9 => 140, 10 => 120];

    /** Day of the month of the successive orders of one month. */
    private const DAYS = [4, 12, 19, 26];

    /** Below this age an order is still to be shipped. */
    private const SHIPPED_AFTER_DAYS = 14;

    /** The buyer left the company this many months ago: no order after that. */
    private const LEFT_MONTHS_AGO = 4;

    private const ACME_BUYERS = ['claire', 'paul', 'isabelle', 'claire', 'sebastien', 'paul', 'claire', 'isabelle'];

    private const EMAILS = [
        'claire' => 'claire@acme.example',
        'paul' => 'paul@acme.example',
        'isabelle' => 'isabelle@acme.example',
        'sebastien' => 'sebastien@acme.example',
    ];

    /** Baskets of the buyers under the approval limit of Acme (500 EUR): they never wait for an approver. */
    private const SMALL_BASKETS = [
        ['EPI-GANT-001' => 20, 'VISS-002' => 6],
        ['EPI-CASK-001' => 12, 'FIXA-001' => 20],
        ['OUTI-001' => 8, 'EMBA-001' => 6],
        ['EPI-VEST-001' => 15, 'SIGN-001' => 8],
        ['VISS-001' => 30, 'PAPE-001' => 4],
        ['EPI-GANT-001' => 16, 'EPI-CASK-001' => 6],
        ['EMBA-001' => 10, 'FIXA-001' => 25],
        ['OUTI-001' => 6, 'VISS-002' => 20, 'SIGN-001' => 4],
    ];

    private const LARGE_BASKETS = [
        ['EPI-CHAU-001' => 8, 'EPI-GANT-001' => 60, 'EPI-CASK-001' => 30],
        ['EPI-CHAU-001' => 12, 'EMBA-001' => 24],
        ['OUTI-001' => 20, 'VISS-001' => 100, 'FIXA-001' => 80],
        ['EPI-CASK-001' => 50, 'EPI-VEST-001' => 40, 'SIGN-001' => 20],
        ['EPI-GANT-001' => 120, 'VISS-002' => 60, 'PAPE-001' => 12],
        ['EMBA-001' => 30, 'OUTI-001' => 15, 'EPI-CHAU-001' => 6],
    ];

    /** Other companies buy the same products as Acme: the dashboard of Acme must not show them. */
    private const OTHER_COMPANIES = [
        ['email' => 'marc@techparts.example', 'prefix' => 'TP-H', 'slots' => [16, 13, 10, 7, 4, 2]],
        ['email' => 'antoine@buildco.example', 'prefix' => 'BC-H', 'slots' => [15, 9, 3]],
    ];

    /** @return list<DemoOrderSpec> */
    public static function specs(\DateTimeImmutable $now): array
    {
        $specs = self::acme($now);
        $specs = [...$specs, ...self::predictiveRhythms($now)];
        foreach (self::OTHER_COMPANIES as $company) {
            foreach ($company['slots'] as $index => $slot) {
                $specs[] = self::otherCompanyOrder($now, $company['email'], $company['prefix'], $index + 1, $slot);
            }
        }

        return $specs;
    }

    /** @return list<DemoOrderSpec> */
    private static function acme(\DateTimeImmutable $now): array
    {
        $specs = [];
        $sequence = 0;
        for ($slot = self::MONTHS_BACK; $slot >= 0; --$slot) {
            $month = (int) self::firstOfMonth($now, $slot)->format('n');
            $count = 0 === $slot ? 1 : self::ACME_ORDERS_PER_MONTH[$month];
            for ($rank = 0; $rank < $count; ++$rank) {
                $specs[] = self::acmeOrder($now, $slot, $month, $rank, ++$sequence);
            }
        }

        return $specs;
    }

    private static function acmeOrder(\DateTimeImmutable $now, int $slot, int $month, int $rank, int $sequence): DemoOrderSpec
    {
        $buyer = self::ACME_BUYERS[$sequence % \count(self::ACME_BUYERS)];
        if ('sebastien' === $buyer && $slot < self::LEFT_MONTHS_AGO) {
            $buyer = 'claire';
        }
        $limited = \in_array($buyer, ['claire', 'sebastien'], true);
        $basket = $limited
            ? self::SMALL_BASKETS[$sequence % \count(self::SMALL_BASKETS)]
            : self::scale(self::LARGE_BASKETS[$sequence % \count(self::LARGE_BASKETS)], self::SEASON_PERCENT[$month] ?? 100);
        $daysAgo = self::daysAgo($now, $slot, 0 === $slot ? 1 : self::DAYS[$rank]);

        return new DemoOrderSpec(
            self::EMAILS[$buyer],
            $daysAgo,
            $basket,
            sprintf('ACME-PO-H-%03d', $sequence),
            $daysAgo > self::SHIPPED_AFTER_DAYS ? OrderScenario::Shipped : OrderScenario::Paid,
            'paul' === $buyer || ('isabelle' === $buyer && 0 === $sequence % 2) ? 'MAINT-02' : 'ATELIER-01',
            deliverySite: self::deliverySite($sequence),
        );
    }

    /** Lyon by default, one order in three at Saint-Priest, one of the others in five at Grenoble. */
    private static function deliverySite(int $sequence): ?string
    {
        if (0 === $sequence % 3) {
            return DemoOrderSpec::SITE_SAINT_PRIEST;
        }

        return 1 === $sequence % 5 ? DemoOrderSpec::SITE_GRENOBLE : null;
    }

    private static function otherCompanyOrder(\DateTimeImmutable $now, string $email, string $prefix, int $number, int $slot): DemoOrderSpec
    {
        $daysAgo = self::daysAgo($now, $slot, self::DAYS[$number % \count(self::DAYS)]);

        return new DemoOrderSpec(
            $email,
            $daysAgo,
            self::LARGE_BASKETS[($number + 1) % \count(self::LARGE_BASKETS)],
            sprintf('%s-%03d', $prefix, $number),
            $daysAgo > self::SHIPPED_AFTER_DAYS ? OrderScenario::Shipped : OrderScenario::Paid,
        );
    }

    /**
     * @param array<string, positive-int> $basket
     *
     * @return array<string, positive-int>
     */
    private static function scale(array $basket, int $percent): array
    {
        return array_map(static fn (int $quantity): int => max(1, intdiv($quantity * $percent + 50, 100)), $basket);
    }

    private static function firstOfMonth(\DateTimeImmutable $now, int $monthsBack): \DateTimeImmutable
    {
        return $now->modify('first day of this month')->setTime(0, 0)->modify(sprintf('-%d months', $monthsBack));
    }

    /** Days between the day `$dayOfMonth` of the month `$slot` months back and the day of `$now`. */
    private static function daysAgo(\DateTimeImmutable $now, int $slot, int $dayOfMonth): int
    {
        $day = self::firstOfMonth($now, $slot)->modify(sprintf('+%d days', $dayOfMonth - 1));

        return max(0, (int) $now->setTime(0, 0)->diff($day)->days);
    }

    /**
     * US-219: predictive purchase rhythms for Claire (Acme) on specific products to guarantee reorder alerts
     * just after the demo reset. Dates are relative to $now so alerts stay consistent after nightly reset.
     *
     * - Gloves (EPI-GANT-001): every ~14 days, last order 11 days ago → alert in 3 days
     * - Vests (EPI-VEST-001): every ~60 days, last order 58 days ago → alert in 2 days
     * - Hardware (VISS-002): irregular 15-25 days
     *
     * @return list<DemoOrderSpec>
     */
    private static function predictiveRhythms(\DateTimeImmutable $now): array
    {
        $specs = [];
        $poCounter = 1000; // High numbers to avoid conflicts with core orders

        // Gloves: every 14 days over 12 months (26 orders), last order 11 days ago
        // Array sized to stay within 365 days: 11 + 25*14 = 361 days
        $gloveIntervals = array_fill(0, 25, 14);
        $gloveDaysAgo = 11; // Last order 11 days ago → alert in 3 days (14 - 11 = 3)
        foreach ($gloveIntervals as $interval) {
            $specs[] = new DemoOrderSpec(
                self::EMAILS['claire'],
                $gloveDaysAgo,
                ['EPI-GANT-001' => 20],
                sprintf('ACME-PO-PRED-%03d', ++$poCounter),
                OrderScenario::Shipped,
                'ATELIER-01',
            );
            $gloveDaysAgo += $interval;
        }

        // Vests: every 60 days over 12 months (6 orders), last order 58 days ago
        // Array sized to stay within 365 days: 58 + 5*60 = 358 days
        $vestIntervals = array_fill(0, 5, 60);
        $vestDaysAgo = 58; // Last order 58 days ago → alert in 2 days (60 - 58 = 2)
        foreach ($vestIntervals as $interval) {
            $specs[] = new DemoOrderSpec(
                self::EMAILS['claire'],
                $vestDaysAgo,
                ['EPI-VEST-001' => 10],
                sprintf('ACME-PO-PRED-%03d', ++$poCounter),
                OrderScenario::Shipped,
                'ATELIER-01',
            );
            $vestDaysAgo += $interval;
        }

        // Hardware: irregular 15-25 days over first 12 orders to stay within 365 days
        $hardwareIntervals = [18, 22, 15, 25, 19, 21, 17, 24, 16, 23, 20, 18];
        $hardwareDaysAgo = 20; // Last order 20 days ago
        foreach ($hardwareIntervals as $interval) {
            $specs[] = new DemoOrderSpec(
                self::EMAILS['claire'],
                $hardwareDaysAgo,
                ['VISS-002' => 50],
                sprintf('ACME-PO-PRED-%03d', ++$poCounter),
                OrderScenario::Shipped,
                'ATELIER-01',
            );
            $hardwareDaysAgo += $interval;
        }

        return $specs;
    }
}
