# Task Board — Sprint 009 (Reports)

> **Arbitrage du 2026-10-16** : US-029 (récepteur de webhooks, 15,5 h) est reportée au sprint 10. Ses tâches restent ci-dessous pour mémoire, hors engagement du sprint 9.

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Calendaire

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Assigné |
|----|-----|------|-------|------------|---------|
| T-044-01 | US-044 | [OPS] | Fixture des presets du plugin dans la suite `demo` | 1h | |
| T-044-02 | US-044 | [BE] | Squelette `demo_container_fill` (-40, `EntityManagerInterface`, CSV partagé) | 2.5h | |
| T-044-03 | US-044 | [BE] | `ContainerCapacityCalculator` (cm/kg → mm/g, capacité) | 2.5h | |
| T-044-04 | US-044 | [TEST] | Tests unitaires du calculateur, 3 produits à la main | 1.5h | |
| T-044-05 | US-044 | [BE] | Données logistiques par variante (colis, unités, vente par colis entiers) | 3.5h | |
| T-044-06 | US-044 | [BE] | Capacités palette EUR et cartons ; mode couches | 3h | |
| T-044-07 | US-044 | [BE] | Règles B2B : une bloquante, une `min_fill_percent` | 2h | |
| T-044-08 | US-044 | [TEST] | Idempotence et reset (deux resets, durée) | 2.5h | |
| T-044-09 | US-044 | [TEST] | Rendu réel : jauge, blocage, colis, plan, B2C | 4h | |
| T-044-10 | US-044 | [TEST] | Non-régression PunchOut, devis, approbation, commande rapide, historique ; rôle de dérogation | 3h | |
| T-044-11 | US-044 | [FE-WEB] | Parcours de démo dans le catalogue des plugins FR/EN | 1.5h | |
| T-044-12 | US-044 | [DOC] | Formule de capacité, règles, parcours | 1h | |
| T-044-13 | US-044 | [REV] | Revue | 1.5h | |
| T-006-01 | US-006 | [BE] | Mode worker derrière un drapeau, threads bornés | 2h | |
| T-006-02 | US-006 | [TEST] | Test de fuite d'état en mode worker | 2.5h | |
| T-006-03 | US-006 | [BE] | Mesure p50/p95 classique contre worker | 1.5h | |
| T-006-06 | US-006 | [TEST] | Mémoire sous charge avec supercronic | 1.5h | |
| T-006-04 | US-006 | [DOC] | ADR-0013 | 1h | |
| T-006-05 | US-006 | [REV] | Revue du spike | 0.5h | |
| T-041-08 | US-041 | [BE] | Prix « dès » calculé en lot | 4h | |
| T-041-09 | US-041 | [TEST] | Requêtes constantes pour 5, 20 et 50 produits | 2h | |
| T-029-01 | US-029 | [DB] | Entité `ReceivedWebhook` et migration | 2h | |
| T-029-02 | US-029 | [BE] | Récepteur compatible avec le garde sortant | 4h | |
| T-029-03 | US-029 | [BE] | Abonnement de démo et validateur d'URL | 2.5h | |
| T-029-04 | US-029 | [FE-WEB] | Page « Webhooks reçus » FR/EN | 2.5h | |
| T-029-05 | US-029 | [TEST] | Test fonctionnel avec livraison réelle via le transport gardé | 3h | |
| T-029-06 | US-029 | [DOC] | Documentation de la page et de l'outbox | 0.5h | |
| T-029-07 | US-029 | [REV] | Revue SSRF, signature, rétention | 1h | |
| T-041-06 | US-041 | [BE] | Réduire les ≈ 420 requêtes du reset d'au moins 50 % (sacrifiable) | 4h | |
| T-041-07 | US-041 | [TEST] | S2-R8 à R10, constats par écrit (sacrifiable) | 3.5h | |

## 📅 Calendaire

| ID | US | Tâche | Estimation | Pas avant |
|----|-----|-------|------------|-----------|
| T-041-15 | US-041 | [OPS] Relevé complet du coût Railway à J+7 (GO utilisateur, hors chemin critique) | 0.5h | 2026-10-23 |

## 🔄 En Cours
| ID | US | Tâche | Démarré | Assigné |
|----|-----|-------|---------|---------|

## 👀 En Review
| ID | US | Tâche | Reviewer |
|----|-----|-------|----------|

## ✅ Terminé
| ID | US | Tâche | Réel | Terminé |
|----|-----|-------|------|---------|

## 🚫 Bloqué
| ID | US | Raison | Action |
|----|-----|--------|--------|

## Métriques
- **Tâches** : 31 total | 0 terminées (0 %)
- **Heures** : 68h estimées | 0h consommées | 68h restantes (capacité ≈ 52h, voir [tasks/README.md](./tasks/README.md))
