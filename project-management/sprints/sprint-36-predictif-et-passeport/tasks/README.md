# Tâches : Sprint 036 (prédictif et passeport)

> Sprint Planning Part 2 : décomposition faite au planning, le 2026-10-29 ; développement sur la branche `feature/sprint-36-predictif-et-passeport` (arbre principal, déjà créée). Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-36`).
>
> Liens : [objectif](../sprint-goal.md), [tableau](../task-board.md), [dépendances](../sprint-dependencies.md), [consigne de worker](../consigne-worker.md), [checklist](../checklist-integration.md), [registre des décisions](../registre-decisions.md), [tâches transverses](./technical-tasks.md), [rétro du sprint 35](../../sprint-35-commerce-predictif/sprint-retro.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Statut |
|----|-------|--------|--------|--------|--------|
| [US-240](./US-240-tasks.md) | Commerce prédictif : alertes garanties à toute date dans la démo et captures (suite d'US-219) | 3 | 8 | 6,5 h | 🔲 prête |
| [US-230](./US-230-tasks.md) | Passeport numérique produit : squelette du plugin et modèle de données ouvert et versionné | 5 | 13 | 11 h | 🔲 prête |
| [US-231](./US-231-tasks.md) | Passeport numérique produit : page publique du passeport | 3 | 8 | 6,5 h | 🔲 prête |
| [US-239](./US-239-tasks.md) | Veille hebdomadaire des versions du socle et récapitulatif des montées majeures | 3 | 9 | 6,5 h | 🔲 prête |
| [Transverse](./technical-tasks.md) | Actions de la rétro du sprint 35 (sans points) | 0 | 5 | 2,5 h | 🔲 |
| **Total** | | **14** | **43** | **33 h** | |

**Total décomposé : 14 points, 43 tâches, 33 h**, dont 1 h d'actions de Thibaut (GO dépôt passeport, GO tags) : **32 h pour Claude**.

> **Périmètre décidé par Thibaut le 2026-10-29** (voir [../registre-decisions.md](../registre-decisions.md)) : suite d'US-219 (US-240), lancement du plugin passeport numérique produit (US-230 + US-231), veille hebdomadaire (US-239), plus les actions de la rétro du sprint 35 (T-TECH-36-01 à 05).

## Capacité

Méthode des sprints précédents : 2,1 h par point + 8 h de tâches transverses.

| Périmètre | Capacité | Charge de Claude | Marge |
|-----------|----------|------------------|-------|
| 14 points | 14 × 2,1 + 8 = **37,4 h** | 32 h | **5,4 h** (14,4 %) |

**Les 14 points tiennent confortablement**, avec une marge de 5,4 h.

## Répartition par type

| Type | Tâches | Heures |
|------|--------|--------|
| [DB] | 2 | 2 h |
| [BE] | 11 | 11 h |
| [FE-WEB] | 4 | 4 h |
| [TEST] | 15 | 10 h |
| [DOC] | 6 | 3,5 h |
| [REV] | 4 | 2 h |
| [OPS] | 1 | 0,5 h |
| **Total** | **43** | **33 h** |

## Ordre d'exécution proposé

1. **T-TECH-36-01 à 05** : actions de rétro (parallèles)
2. **US-240** : alertes garanties (pas de dépendance)
3. **US-239** : veille hebdomadaire (parallèle à US-240)
4. **US-230** : squelette du plugin passeport (après GO dépôt de Thibaut)
5. **US-231** : page publique (dépend d'US-230)

## Workers proposés et parallélisme

| Worker | Story | Domaine de fichiers | Chevauchements |
|--------|-------|---------------------|----------------|
| w240 | US-240 | plugin prédictif, fixtures démo | aucun |
| w239 | US-239 | workflow GitHub, bin/stack-versions-report | aucun |
| w230 | US-230 | nouveau dépôt plugin passeport | aucun |
| w231 | US-231 | plugin passeport (après US-230) | dépend d'US-230 |

Parallélisme recommandé : w240 et w239 peuvent avancer en parallèle ; w230 après GO de Thibaut ; w231 après US-230.
