# US-304 — Sondes des plugins B2B (lot 1) : tâches

> Story : [US-304](../../../backlog/user-stories/US-304.md) · 5 points · Must · Dépôts : `sylius-webhook-outbox-plugin`, `sylius-purchase-approval-plugin`, `sylius-trade-credit-plugin`, en parallèle · Releases dans T-TECH-60-03, sur GO

| ID | Type | Contenu | Heures | Dépend de | Statut |
|----|------|---------|--------|-----------|--------|
| T-304-01 | [TEST] [BE] [DOC] | **webhook-outbox** : `suggest` (nouvelle section) et `require-dev` santé `^0.5` ; tests rouges (une ligne par ligne du tableau, boot sans le plugin de santé) ; sondes `backlog` et `dead_deliveries` ; README EN/FR, CHANGELOG | 4 h | — | 🔲 À faire |
| T-304-02 | [TEST] [BE] [DOC] | **purchase-approval** : `suggest` et `require-dev` ; tests rouges ; sondes `overdue` et `closed_orders` ; README, CHANGELOG | 3 h | — | 🔲 À faire |
| T-304-03 | [TEST] [BE] [DOC] | **trade-credit** : `suggest` et `require-dev` ; tests rouges ; sonde `stale_reservations` (lecture seule) ; README, CHANGELOG | 3 h | — | 🔲 À faire |

## Fin de tâche

- Rouge d'assertion vu en CI sur toute la matrice du dépôt, suites unitaire et non unitaire (règle 26).
- `composer.json` : le plugin de santé n'apparaît **pas** en `require`.
- Sondes vues sur la page de santé de l'application de test du plugin.
