# Tâches — Sprint 009 (reports)

> Sprint Planning Part 2 du 2026-10-16 (décomposition). Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-9`, `in-progress`). Sprint 8 : [../../sprint-8-demo-et-durcissement/tasks/README.md](../../sprint-8-demo-et-durcissement/tasks/README.md). Objectif : [../sprint-goal.md](../sprint-goal.md). Tableau : [../task-board.md](../task-board.md). Dépendances : [../sprint-dependencies.md](../sprint-dependencies.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Statut |
|----|-------|--------|--------|--------|--------|
| [US-006](./US-006-tasks.md) | Spike : mode worker FrankenPHP | 2 | 6 | 9h | 🔲 |
| [US-029](./US-029-tasks.md) | Récepteur de webhooks de démo | 3 | 7 | 15.5h | ➡️ sprint 10 |
| [US-044](./US-044-tasks.md) | Données de remplissage des contenants (container-fill) | 5 | 13 | 29.5h | 🔲 |
| [US-041 (reports)](./US-041-tasks.md) | T-041-06 à T-041-09 et T-041-15 | 4 (capacité) | 5 | 14h | 🔲 |

**Total décomposé** : 31 tâches | 68h. **Engagé après arbitrage** : 24 tâches | 52,5h | 11 points (US-029 reportée au sprint 10).

## Capacité

Ratio observé au sprint 8 : 3,6 à 3,7 h/point. 14 points ≈ **52 h**. Charge décomposée : **68 h**, soit **+16 h (≈ 131 %)**.

| Levier de réduction | Heures | Effet |
|---------------------|--------|-------|
| T-041-06 et T-041-07 (marquées sacrifiables) | 7.5h | 60.5h |
| US-029 (MoSCoW Could) en entier | 15.5h | 45h si les deux leviers sont pris |
| T-041-15 : calendaire (0.5h, ≥ 2026-10-23, attente, hors chemin critique) | 0.5h | pas de charge continue |

**Arbitrage de l'utilisateur (2026-10-16)** : US-029 est reportée au sprint 10, avec ses tâches telles quelles. Le sprint engage US-044, US-006 et T-041-06 à 09, soit 52,5 h pour environ 52 h de capacité. Ordre de sacrifice si le sprint dérive : T-041-06 et T-041-07 (7,5 h).

## Répartition par type

| Type | Tâches | Heures | % |
|------|--------|--------|---|
| [DB] | 1 | 2h | 3 % |
| [BE] | 11 | 31.5h | 46 % |
| [FE-WEB] | 2 | 4h | 6 % |
| [TEST] | 9 | 23.5h | 35 % |
| [OPS] | 2 | 1.5h | 2 % |
| [DOC] | 3 | 2.5h | 4 % |
| [REV] | 3 | 3h | 4 % |
| **Total** | **31** | **68h** | |

## Points d'attention (mises à jour à la décomposition)

- **US-006** (+2h) : le Caddyfile n'a ni `num_threads` ni `max_threads` (constat R5 du sprint 1 non traité) : bornage des threads dans T-006-01 et mesure mémoire avec supercronic dans T-006-06 (nouvelle).
- **US-029** (+1.5h) : depuis le commit e86a683 (M5), `OutboundGuardHttpClient` décore aussi `tmonier_webhook.http_client.transport` : récepteur sur un hôte autorisé (`127.0.0.1`), sans redirection, exempté de la barrière de démo par son chemin exact.
- **US-044** : priorités de fixtures négatives (presets -5, `demo_container_fill` -40), conversions cm/kg → mm/g, canal B2B seul, vérification sur le rendu réel.
- **T-041-06** : mesurer avec la fixture d'US-044 chargée (la base de comparaison change).
- **T-041-15** : pas avant le 2026-10-23, GO utilisateur, hors chemin critique.
- **Contraintes** : disque ~3 Go, aucun rebuild Docker local (CI pour les images), pas de test lancé pendant la décomposition.

## Fichiers

- [US-006 - Spike : mode worker FrankenPHP](./US-006-tasks.md)
- [US-029 - Récepteur de webhooks de démo](./US-029-tasks.md)
- [US-044 - Données de remplissage des contenants](./US-044-tasks.md)
- [US-041 (reports) - Qualité des données et du reset](./US-041-tasks.md)

## Conventions

- **ID** : T-[US]-[Numéro] (ex. T-044-05) ; les identifiants T-041-xx du sprint 8 sont conservés.
- **Taille** : 0.5h à 8h max.
- **Statuts** : 🔲 À faire | 🔄 En cours | 👀 Review | ✅ Done | 🚫 Bloqué | 📅 Calendaire
