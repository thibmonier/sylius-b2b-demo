# Sprint 47 — Audit finalisation

**Durée** : 2026-11-01 → 2026-11-15
**Capacité** : 11 points
**Vélocité cible** : 11 points (4 stories)

## Sprint Goal

Finaliser les corrections de l'audit sprint 44 : couverture de tests des plugins gpsr et agentic-feeds, refactoring du webhook-outbox, mise à jour de la CVE du thème, et push de la couverture CI des 28 plugins.

## Stories

| ID | Titre | Points | Priorité |
|----|-------|--------|----------|
| US-262 | Augmenter couverture tests gpsr (30,5 % → ≥ 70 %) | 3 | Moyenne |
| US-263 | Augmenter couverture tests agentic-feeds (36,2 % → ≥ 70 %) | 3 | Moyenne |
| US-264 | Refactoring DeliveryClaimer webhook-outbox (310L → 2 services) | 3 | Moyenne |
| US-265 | Mettre à jour contraintes dépendances thème (CVE symfony/mime) | 2 | Moyenne |

## Tâches techniques

| ID | Titre | Heures |
|----|-------|--------|
| T-TECH-47-01 | Marquer sprint 46 comme done | 0,25h |
| T-TECH-47-02 | Pousser les 28 plugins coverage CI (US-253) | 1h |
| T-TECH-47-03 | Report T-TECH-43-04 (montant maquette Copilote) | 0,25h |

## Contexte

Ce sprint clôture le cycle d'audit lancé au sprint 44 (33 dépôts audités). Les sprints 45 et 46 ont traité les 6 constats de priorité haute et moyenne haute. Ce sprint traite les 4 constats restants de priorité moyenne.

Toutes les stories portent sur des dépôts plugins externes ; la PR de la démo sera docs-only (review, retro, statuts).
