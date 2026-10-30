# Tâches : Sprint 037 (copilote et passeport)

> Sprint Planning Part 2 : décomposition faite au planning, le 2026-10-30 ; développement sur la branche `feature/sprint-37-copilote-et-passeport` (arbre principal, déjà créée). Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-37`).
>
> Liens : [objectif](../sprint-goal.md), [tableau](../task-board.md), [dépendances](../sprint-dependencies.md), [consigne de worker](../consigne-worker.md), [checklist](../checklist-integration.md), [registre des décisions](../registre-decisions.md), [tâches transverses](./technical-tasks.md), [rétro du sprint 36](../../sprint-36-predictif-et-passeport/sprint-retro.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Statut |
|----|-------|--------|--------|--------|--------|
| [US-211](./US-211-tasks.md) | Copilote d'achat : squelette du plugin, widget du compte et adaptateur rejoué | 5 | 15 | 11 h | 🔲 prête |
| [US-232](./US-232-tasks.md) | Passeport numérique produit : QR et lien GS1 Digital Link | 3 | 8 | 6,5 h | 🔲 prête |
| [US-233](./US-233-tasks.md) | Passeport numérique produit : pièces détachées compatibles | 3 | 8 | 6,5 h | 🔲 prête |
| [US-241](./US-241-tasks.md) | Passeport numérique produit : installation dans la démo, captures et audit pa11y AAA | 2 | 7 | 4 h | 🔲 prête |
| [Transverse](./technical-tasks.md) | Actions de la rétro du sprint 36 (sans points) | 0 | 10 | 4 h | 🔲 |
| **Total** | | **13** | **48** | **32 h** | |

**Total décomposé : 13 points, 43 tâches, 32 h**, dont 1 h d'actions de Thibaut (GO dépôt copilote, GO tags) : **31 h pour Claude**.

> **Périmètre décidé par Thibaut le 2026-10-30** (voir [../registre-decisions.md](../registre-decisions.md)) : copilote d'achat walking skeleton (US-211), QR et lien GS1 Digital Link (US-232), pièces détachées compatibles (US-233), installation du plugin passeport dans la démo (US-241), plus les actions de la rétro du sprint 36 (T-TECH-37-01 à 05).

## Capacité

Méthode des sprints précédents : 2,1 h par point + 8 h de tâches transverses.

| Périmètre | Capacité | Charge de Claude | Marge |
|-----------|----------|------------------|-------|
| 13 points | 13 × 2,1 + 8 = **35,3 h** | 31 h | **4,3 h** (12,2 %) |

**Les 13 points tiennent confortablement**, avec une marge de 4,3 h.

## Répartition par type

| Type | Tâches | Heures |
|------|--------|--------|
| [DB] | 0 | 0 h |
| [BE] | 12 | 11,5 h |
| [FE-WEB] | 6 | 5 h |
| [TEST] | 19 | 12,5 h |
| [DOC] | 8 | 2,5 h |
| [REV] | 2 | 0,5 h |
| [OPS] | 1 | 0 h |
| **Total** | **48** | **32 h** |

## Ordre d'exécution proposé

1. **T-TECH-37-01 à 05** : actions de rétro (parallèles)
2. **US-241** : installation du plugin passeport dans la démo (après US-231 v0.1.0, pas de dépendance avec US-232/233)
3. **US-211** : squelette du plugin copilote (GO dépôt donné et dépôt créé le 2026-10-30)
4. **US-232** et **US-233** : extensions du plugin passeport (parallèles dans le même dépôt, séquentielles côté worker)

## Workers proposés et parallélisme

| Worker | Story | Domaine de fichiers | Chevauchements |
|--------|-------|---------------------|----------------|
| w241 | US-241 | démo, fixtures, captures | aucun |
| w211 | US-211 | nouveau dépôt plugin copilote | aucun |
| w232-233 | US-232, US-233 | plugin passeport (après US-230) | même dépôt, séquentiel |

Parallélisme recommandé : w241 et w211 peuvent avancer en parallèle ; w232-233 séquentiel après US-231 fusionnée. Contrainte : au plus deux workers lancent des tests en même temps (8 Go RAM).
