<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo\PredictiveAlerts;

use App\Demo\Order\DemoOrderPlan;
use App\Demo\Order\DemoOrderSpec;
use App\Demo\Order\OrderScenario;
use PHPUnit\Framework\TestCase;
use Tmonier\SyliusPredictiveAnalyticsPlugin\Calculator\AlertCalculator;
use Tmonier\SyliusPredictiveAnalyticsPlugin\Calculator\IntervalCalculator;

/**
 * US-240: the demo must show at least 2 reorder alerts on Claire's account whatever the day of the reset.
 *
 * This test sweeps 365 days and verifies that the fixture history always produces
 * at least 2 alerts when the predictive analytics rules (IntervalCalculator, AlertCalculator) are applied.
 *
 * It also verifies the tested invariants (≤ 55 Acme orders, August trough, same day of month, Paul overdue).
 */
final class MinimumAlertsTest extends TestCase
{
    /** Minimum de commandes pour calculer une prédiction (règle du plugin). */
    private const MIN_ORDERS = 3;

    /**
     * US-240: au moins 2 alertes quelle que soit la date de remise à zéro (balayage sur 365 jours, chaque jour).
     */
    public function testTheDemoShowsAtLeastTwoAlertsOnAnyResetDayOverFullYear(): void
    {
        $failures = [];
        $start = new \DateTimeImmutable('2026-01-01 10:00:00');

        // Balayage de 365 jours, chaque jour (365 dates testées)
        for ($offset = 0; $offset < 365; ++$offset) {
            $now = $start->modify(sprintf('+%d days', $offset));
            $alertCount = $this->countAlertsForDate($now);

            if ($alertCount < 2) {
                $failures[] = sprintf(
                    '%s: %d alert%s (expected at least 2)',
                    $now->format('Y-m-d'),
                    $alertCount,
                    1 === $alertCount ? '' : 's',
                );
            }
        }

        self::assertSame(
            [],
            $failures,
            sprintf(
                "The demo must show at least 2 alerts on any reset day. Failures:\n%s",
                implode("\n", $failures),
            ),
        );
    }

    /**
     * US-240: invariants testés (≤ 55 commandes Acme, creux d'août, même jour du mois, Paul en retard).
     */
    public function testTheFixturesPreserveTheTestedInvariants(): void
    {
        $now = new \DateTimeImmutable('2026-10-21 08:00:00');
        $allOrders = DemoOrderPlan::all($now);
        $acmeOrders = $this->acmeOrders($allOrders);

        // Invariant 1 : ≤ 55 commandes Acme sur 18 mois
        self::assertLessThanOrEqual(55, count($acmeOrders), 'Acme orders must be ≤ 55');

        // Invariant 2 : août est le creux de l'année
        $perMonth = $this->ordersPerMonth($acmeOrders, $now, '2025-11', '2026-10');
        $complete = array_slice($perMonth, 0, 11, true);
        $august = array_filter($complete, static fn (int $count, string $month): bool => str_ends_with($month, '-08'), \ARRAY_FILTER_USE_BOTH);

        self::assertNotSame([], $august, 'August must be in the 12-month window');
        self::assertSame(
            min([\PHP_INT_MAX, ...array_values($complete)]),
            min([\PHP_INT_MAX, ...array_values($august)]),
            'August must be the trough of the year',
        );

        // Invariant 3 : mêmes jours du mois quel que soit le jour de remise à zéro
        $later = new \DateTimeImmutable('2026-10-29 18:00:00');
        self::assertSame($this->signature($now), $this->signature($later), 'Orders must fall on the same days of the month');

        // Invariant 4 : Paul en retard (commande ACME-PO-2026-0568, 48 jours, attente de paiement, ON_ACCOUNT)
        $paulOrder = $this->findPaulLateOrder($allOrders);
        self::assertNotNull($paulOrder, 'Paul late order (ACME-PO-2026-0568) must exist');
        self::assertSame('ACME-PO-2026-0568', $paulOrder->poReference, 'Paul order must be ACME-PO-2026-0568');
        self::assertSame(48, $paulOrder->daysAgo, 'Paul order must be 48 days old');
        self::assertSame(OrderScenario::AwaitingPayment, $paulOrder->scenario, 'Paul order must be awaiting payment');
    }

    /**
     * Compte les alertes pour une date donnée en appliquant les règles du plugin prédictif.
     */
    private function countAlertsForDate(\DateTimeImmutable $now): int
    {
        $allOrders = DemoOrderPlan::all($now);
        $acmeOrders = $this->acmeOrders($allOrders);

        // Groupe les commandes par référence produit
        $ordersByVariant = $this->groupByVariant($acmeOrders, $now);

        $alerts = [];

        foreach ($ordersByVariant as $variantCode => $dates) {
            // Minimum 3 commandes pour calculer une prédiction
            if (count($dates) < self::MIN_ORDERS) {
                continue;
            }

            // Utilisation des classes du plugin (vendor)
            $intervals = IntervalCalculator::calculateIntervals($dates);
            $medianIntervalDays = IntervalCalculator::calculateMedian($intervals);
            $lastOrderDate = end($dates);

            // Règle d'alerte du plugin
            if (AlertCalculator::shouldAlert($now, $lastOrderDate, $medianIntervalDays)) {
                $alerts[] = $variantCode;
            }
        }

        return count($alerts);
    }

    /**
     * Trouve la commande de Paul en retard (invariant 4).
     *
     * @param list<DemoOrderSpec> $orders
     */
    private function findPaulLateOrder(array $orders): ?DemoOrderSpec
    {
        foreach ($orders as $spec) {
            if ('ACME-PO-2026-0568' === $spec->poReference) {
                return $spec;
            }
        }

        return null;
    }

    /**
     * Filtre les commandes Acme.
     *
     * @param list<DemoOrderSpec> $orders
     *
     * @return list<DemoOrderSpec>
     */
    private function acmeOrders(array $orders): array
    {
        return array_values(array_filter($orders, static fn (DemoOrderSpec $spec): bool => str_ends_with($spec->customerEmail, '@acme.example')));
    }

    /**
     * Groupe les commandes par référence produit avec leurs dates.
     *
     * @param list<DemoOrderSpec> $orders
     *
     * @return array<string, list<\DateTimeImmutable>> Dates triées par ordre chronologique
     */
    private function groupByVariant(array $orders, \DateTimeImmutable $now): array
    {
        $byVariant = [];

        foreach ($orders as $spec) {
            $completedAt = DemoOrderPlan::completedAt($now, $spec->daysAgo);

            foreach (array_keys($spec->items) as $variantCode) {
                $byVariant[$variantCode][] = $completedAt;
            }
        }

        // Trier les dates pour chaque référence
        foreach ($byVariant as $code => $dates) {
            sort($dates);
            $byVariant[$code] = $dates;
        }

        return $byVariant;
    }

    /**
     * Compte les commandes par mois calendaire.
     *
     * @param list<DemoOrderSpec> $acme
     *
     * @return array<string, int>
     */
    private function ordersPerMonth(array $acme, \DateTimeImmutable $now, string $from, string $to): array
    {
        $months = [];
        for ($month = new \DateTimeImmutable($from . '-01'); $month <= new \DateTimeImmutable($to . '-01'); $month = $month->modify('+1 month')) {
            $months[$month->format('Y-m')] = 0;
        }

        foreach ($acme as $spec) {
            // Ignore cancelled and pending approval orders (rule from DemoOrderHistoryTest)
            if (in_array($spec->scenario->value, ['cancelled', 'pending_approval'], true)) {
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
     * Signature d'un historique : permet de vérifier que les commandes tombent aux mêmes jours du mois.
     *
     * @return list<string>
     */
    private function signature(\DateTimeImmutable $now): array
    {
        $history = DemoOrderPlan::history($now);

        return array_map(
            static fn (DemoOrderSpec $spec): string => implode('|', [
                $spec->poReference,
                $spec->customerEmail,
                json_encode($spec->items),
                DemoOrderPlan::completedAt($now, $spec->daysAgo)->format('d'),
                $spec->scenario->value,
            ]),
            $history,
        );
    }
}
