# Tâches — Sprint 1 (Walking Skeleton)

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Statut |
|----|-------|--------|--------|--------|--------|
| US-001 | Projet Sylius 2.3 avec les 21 plugins | 8 | 8 | 8h | 🔲 |
| US-002 | Image Docker FrankenPHP multi-stage | 5 | 6 | 6h | 🔲 |
| US-003 | Environnement local docker compose | 3 | 4 | 3.5h | 🔲 |
| US-004 | Tâches planifiées dans le conteneur | 3 | 3 | 3h | 🔲 |
| US-005 | CI GitHub Actions et tests de fumée | 3 | 3 | 4.5h | 🔲 |
| US-007 | Canaux B2B et B2C, FR/EN | 3 | 4 | 3h | 🔲 |
| US-031 | Correctifs des plugins publiés | 1 | 3 | 1.5h | 🔲 |
| — | Tâches techniques transverses | — | 5 | 2.5h | 🔲 |
| **Total** | | **26 pts** | **36** | **32h** | |

## Répartition par type

| Type | Tâches | Heures | % |
|------|--------|--------|---|
| [OPS] | 19 | 16h | 50% |
| [BE] | 9 | 8.5h | 27% |
| [TEST] | 7 | 5h | 16% |
| [DOC] | 1 | 0.5h | 2% |
| **TOTAL** | **36** | **32h** | 100% |

## Fichiers de tâches

- [US-001 — Projet Sylius 2.3 + 21 plugins](./US-001-tasks.md) — 8 tâches, 8h
- [US-002 — Image Docker FrankenPHP](./US-002-tasks.md) — 6 tâches, 6h
- [US-003 — Environnement local docker compose](./US-003-tasks.md) — 4 tâches, 3.5h
- [US-004 — Tâches planifiées](./US-004-tasks.md) — 3 tâches, 3h
- [US-005 — CI GitHub Actions](./US-005-tasks.md) — 3 tâches, 4.5h
- [US-007 — Canaux B2B et B2C](./US-007-tasks.md) — 4 tâches, 3h
- [US-031 — Correctifs plugins](./US-031-tasks.md) — 3 tâches, 1.5h
- [Tâches techniques transverses](./technical-tasks.md) — 5 tâches, 2.5h

## Conventions

- **ID** : T-[NumUS]-[Numéro] (ex : T-001-05) ou T-TECH-[Numéro] pour les transverses
- **Taille** : 0.5h – 2h (cible), 4h max
- **Statuts** : 🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué
- **TDD** : toutes les tâches `[TEST]` sont écrites en TDD (test rouge → code → vert)

## Chemin critique

```
T-031-01/02/03 (parallèle, dès j1)
    ↓
T-TECH-01/02/03/04 (dès j1, parallèle)
    ↓
T-001-01 → T-001-02 → T-001-03 → T-001-04 → T-001-05/06 → T-001-07 → T-001-08
                                               ↓
                                   T-007-01 → T-007-02 → T-007-03 → T-007-04
                                               ↓
T-002-01 → T-002-02 → T-002-03 ─────────────────────────────────────────────────────→ T-002-05 → T-002-06
              └──────────────────── T-002-04 ─────────────────────────────────────────┘
                                               ↓                      ↓
                               T-003-01 → T-003-02/03 → T-003-04    T-004-01 → T-004-02 → T-004-03
                                               ↓
                               T-005-01 → T-005-02/03
```
