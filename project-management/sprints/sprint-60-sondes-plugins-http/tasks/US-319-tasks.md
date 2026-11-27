# US-319 — Test instable `AdminOrderBulkActionsTest` : tâches

> Story : [US-319](../../../backlog/user-stories/US-319.md) · 2 points · Should · Dépôt : `sylius-bulk-actions-plugin` · **Enquête bornée à 4 h** (décision de Thibaut du 2026-11-27)

| ID | Type | Contenu | Heures | Dépend de | Statut |
|----|------|---------|--------|-----------|--------|
| T-319-01 | [TEST] | Enquête : journal de l'échec (run 45953875859, tentative 1), reproduction en local dans Docker sur Sylius ~2.3.0, PHP 8.4, Symfony ^8.0, MySQL 8.4, hypothèses de la fiche dans l'ordre. **Arrêt à 4 h** : journal conservé, état écrit, suite soumise à Thibaut | 4 h | — | 🔲 À faire |
| T-319-02 | [TEST] | Correctif (sans retry ni test ignoré), 20 exécutions vertes de la combinaison, CI de `main` verte (règle 32) | 2 h | T-319-01 | 🔲 À faire |

## Fin de tâche

- T-319-01 : la cause, ou l'état de l'enquête à 4 h, est écrite dans la fiche.
- T-319-02 : 20 exécutions vertes citées (commande et dernière ligne).
