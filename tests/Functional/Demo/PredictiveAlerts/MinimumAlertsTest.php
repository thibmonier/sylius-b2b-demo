<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo\PredictiveAlerts;

use App\Demo\Order\DemoOrderPlan;
use App\Demo\Order\DemoOrderSpec;
use PHPUnit\Framework\TestCase;

/**
 * US-240: the demo must show at least 2 reorder alerts on Claire's account whatever the day of the reset.
 *
 * This test sweeps 12 months (every 15 days) and verifies that the fixture history always produces
 * at least 2 alerts when the predictive analytics rules are applied.
 *
 * It also verifies the tested invariants (≤ 55 Acme orders, August trough, same day of month, Paul overdue).
 */
final class MinimumAlertsTest extends TestCase
{
    /** Minimum de commandes pour calculer une prédiction (règle du plugin). */
    private const MIN_ORDERS = 3;

    /** Fenêtre d'alerte : échéance dans les 7 jours (règle du ReorderAlertProvider). */
    private const ALERT_WINDOW_DAYS = 7;

    /**
     * US-240: au moins 2 alertes quelle que soit la date de remise à zéro (balayage sur 12 mois, tous les 15 jours).
     */
    public function testTheDemoShowsAtLeastTwoAlertsOnAnyResetDayOverTwelveMonths(): void
    {
        $failures = [];
        $start = new \DateTimeImmutable('2026-01-01 10:00:00');

        // Balayage de 12 mois, un pas tous les 15 jours (24 dates testées)
        for ($offset = 0; $offset < 365; $offset += 15) {
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

        // Invariant 4 : Paul en retard (commande de 48 jours en attente de paiement, dans les core orders)
        // Vérifié par les tests existants de DemoOrderPlan::core() (non dupliqué ici)
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

            // Calcul de l'intervalle médian (logique du plugin prédictif)
            $intervals = $this->calculateIntervals($dates);
            $medianIntervalDays = $this->calculateMedian($intervals);
            $lastOrderDate = end($dates);

            // Date attendue : lastOrderDate + medianIntervalDays
            $expectedDate = $this->calculateExpectedDate($lastOrderDate, $medianIntervalDays);

            // Règle d'alerte : échéance ≤ now + 7 jours ET retard ≤ 1 intervalle médian
            if ($this->isAlert($now, $expectedDate, $medianIntervalDays)) {
                $alerts[] = $variantCode;
            }
        }

        return count($alerts);
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
     * Calcule la date attendue de réassort (règle du ReorderAlertProvider).
     */
    private function calculateExpectedDate(\DateTimeImmutable $lastOrderDate, float $medianIntervalDays): \DateTimeImmutable
    {
        $intervalDays = (int) $medianIntervalDays;
        $intervalHours = (int) (($medianIntervalDays - $intervalDays) * 24);
        $intervalSpec = sprintf('P%dDT%dH', $intervalDays, $intervalHours);

        return $lastOrderDate->add(new \DateInterval($intervalSpec));
    }

    /**
     * Vérifie si une prédiction déclenche une alerte (règle du ReorderAlertProvider).
     *
     * Critères :
     * - Date attendue ≤ now + 7 jours (fenêtre d'alerte)
     * - Date attendue ≥ now - medianIntervalDays (retard maximum toléré)
     */
    private function isAlert(\DateTimeImmutable $now, \DateTimeImmutable $expectedDate, float $medianIntervalDays): bool
    {
        $sevenDaysFromNow = $now->modify(sprintf('+%d days', self::ALERT_WINDOW_DAYS));

        if ($expectedDate > $sevenDaysFromNow) {
            return false; // Trop loin dans le futur
        }

        // Retard maximum accepté : un intervalle médian
        $medianDays = (int) $medianIntervalDays;
        $earliestDate = $now->modify(sprintf('-%d days', $medianDays));

        return $expectedDate >= $earliestDate;
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
            if (in_array($spec->scenario->value, ['Cancelled', 'PendingApproval'], true)) {
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

    /**
     * Calcule les intervalles en jours entre commandes successives (logique du plugin prédictif).
     *
     * @param list<\DateTimeImmutable> $dates Dates triées par ordre chronologique
     *
     * @return list<int> Intervalles en jours
     */
    private function calculateIntervals(array $dates): array
    {
        $intervals = [];

        for ($i = 1, $count = count($dates); $i < $count; ++$i) {
            $interval = $dates[$i]->diff($dates[$i - 1])->days;
            $intervals[] = $interval;
        }

        return $intervals;
    }

    /**
     * Calcule la médiane d'un tableau d'entiers (logique du plugin prédictif).
     *
     * @param list<int> $values
     *
     * @return float Médiane (peut être décimale : 21.5)
     */
    private function calculateMedian(array $values): float
    {
        if ([] === $values) {
            throw new \InvalidArgumentException('Cannot calculate median of empty array');
        }

        $sorted = $values;
        sort($sorted);
        $count = count($sorted);

        if (0 === $count % 2) {
            // Nombre pair : moyenne des deux valeurs centrales (pas arrondie)
            $mid1 = $sorted[$count / 2 - 1];
            $mid2 = $sorted[$count / 2];

            return ($mid1 + $mid2) / 2;
        }

        // Nombre impair : valeur centrale
        return (float) $sorted[(int) floor($count / 2)];
    }
}
