# Tâches : Sprint 027 (fiche produit et ERP fictif)

> Sprint Planning Part 2 : décomposition faite au planning, le 2026-10-24, sur la branche partagée `feature/sprint-27-dev`. Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-27`).
>
> Liens : [objectif](../sprint-goal.md), [tableau](../task-board.md), [dépendances](../sprint-dependencies.md), [consigne de worker](../consigne-worker.md), [checklist](../checklist-integration.md), [tâches transverses](./technical-tasks.md), [rétro du sprint 26](../../sprint-26-vitrine-et-flux/sprint-retro.md), [revue du sprint 26](../../sprint-26-vitrine-et-flux/sprint-review.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Sacrifice | Statut |
|----|-------|--------|--------|--------|-----------|--------|
| [US-189](./US-189-tasks.md) | Refonte de la fiche produit dans la continuité de la home | 5 | 13 | 11,5 h | non sacrifiable | 🔲 (maquette en premier, code 📅) |
| [US-193](./US-193-tasks.md) | Extrait réel du fichier plat sur `/echange-fichier` | 2 | 8 | 4,5 h | **n°2 (à confirmer)** | 🔲 |
| [US-188](./US-188-tasks.md) | Démonstration avec l'ERP fictif (périmètre réduit, sans US-187) | 5 | 11 | 9,5 h | **n°1 (à confirmer)** | 🔲 |
| [Transverse](./technical-tasks.md) | Actions de la rétro du sprint 26, contrôles, GO, clôture (sans points) | 0 | 12 | 8 h | non sacrifiable | 1 ✅, 11 🔲 |
| [US-194](./US-194-tasks.md) | Logo de l'auteur dans le pied de page | 1 | 1 | 1 h | non sacrifiable | 🔄 (w185) |
| **Total** | | **13** | **45** | **34,5 h** | | |

**Total décomposé : 13 points, 45 tâches, 34,5 h**, dont 0,75 h d'actions de Thibaut (2 tâches : validation de la maquette, éléments de la fiche) : **33,75 h pour Claude**, orchestrateur compris.

> **Périmètre validé par Thibaut le 2026-10-24.** Fiche produit refondue après maquette (US-189), vrai extrait du fichier plat (US-193), démonstration avec l'ERP fictif réduite à ce qui se démontre sans import (US-188). US-187 (import CSV de prix et de stock) reste au backlog.

## Capacité

Méthode des sprints précédents : 2,1 h par point + 8 h de tâches transverses.

| Périmètre | Capacité | Charge de Claude | Marge |
|-----------|----------|------------------|-------|
| 13 points | 13 × 2,1 + 8 = **35,3 h** | 33,75 h | **1,55 h** |

La marge est faible (1,55 h contre 3,3 h au sprint 26) : le périmètre en points est sous la moyenne (19), mais les actions de la rétro pèsent 8 h de transverse et la maquette d'US-189 demande deux feux verts de Thibaut. Ordre de sacrifice proposé, **à confirmer par Thibaut** : **US-188, puis US-193**.

| # | Sacrifice | Heures Claude libérées | Charge restante | Capacité du périmètre restant | Conséquence |
|---|-----------|------------------------|-----------------|-------------------------------|-------------|
| 0 | (charge décomposée) | n/a | 33,75 h (13 points) | 35,3 h | Marge de 1,55 h |
| 1 | US-188 entière (simulateur, journal, documentation) | 9,5 h | 24,25 h (8 points) | 24,8 h | La page `/echange-fichier` garde son extrait réel ; l'ERP fictif reste au backlog avec US-187 |
| 2 | US-193 entière (extrait réel du fichier plat) | 4,5 h | 19,75 h (6 points) | 20,6 h | La page garde l'extrait reconstruit des événements (état du sprint 26) |

Une story sacrifiée au milieu de son budget est arrêtée et son état consigné, pas abandonnée en silence. L'attente d'un feu vert de Thibaut (T-189-04, T-189-05) n'est **pas** un sacrifice : US-189 passe 📅 et les deux autres stories avancent.

## Répartition par type

| Type | Tâches | Heures |
|------|--------|--------|
| [DOC] | 11 | 6,75 h |
| [TEST] | 13 | 9 h |
| [FE-WEB] | 5 | 7,5 h |
| [BE] | 6 | 6,5 h |
| [OPS] | 6 | 3 h |
| [REV] | 4 | 1,75 h |
| [DB] | 0 | 0 h |
| [FE-MOB] | 0 | 0 h |
| **Total** | **45** | **34,5 h** |

## Fichiers principaux et chevauchements entre stories

`●` = fichier modifié, `○` = lu seulement.

| Fichier ou zone | US-189 | US-193 | US-188 | Transverse |
|-----------------|:------:|:------:|:------:|:----------:|
| `templates/shop/theme_b2b/` (fiche produit), `assets/shop/styles/b2b.scss`, `config/packages/demo_theme_b2b.yaml` | ● | | | |
| `translations/` (un nouveau domaine par story) | ● (fiche) | ● (page) | ● (échanges) | |
| `templates/demo/erp_flatfile.html.twig` | | ● | ● (lien) | |
| `src/Demo/Erp/` | | ● | ● | |
| `src/Demo/Command/DemoResetCommand.php`, `src/Demo/DataFixtures/` | | ● | ● | |
| `src/Demo/Controller/`, `templates/admin/` (page des échanges) | | ○ | ● | |
| `tests/` de la démo (nouveaux fichiers ; `ErpFlatfilePageTest` adapté) | ● | ● | ● | ● (compteurs) |
| `config/demo/proof.json` | ● (rafraîchi) | ● (rafraîchi) | ● (rafraîchi) | |
| `bin/check-disk`, `bin/run-tests`, `docs/tests-locaux.md` | | | | ● |
| `project-management/templates/` | | | | ● (fait au planning) |
| `docs/erp-demo.md`, `docs/adr/0017-*.md`, `docs/webhooks-demo.md` | | | ● | |
| `docs/theme-b2b.md`, `docs/images/product-*`, README de la démo | ● | | ● | |
| `vendor/tmonier/sylius-erp-flatfile-plugin` | | ○ | ○ | |

Ordre de merge proposé : transverses (T-TECH-27-02, 03, 06) puis US-193 puis US-188 (qui reprend T-193-04) ; US-189 indépendante, après le GO de la maquette. `config/demo/proof.json` : `php bin/proof-refresh` rejoué après chaque rebase, jamais fusionné à la main.

## Règles du sprint

Reprises de [../consigne-worker.md](../consigne-worker.md) (à coller dans chaque consigne de worker) :

- Tests uniquement par `WORKER_ID=wN TEST_DB_NAME=b2b_wN TEST_CACHE_DIR=var/cache-wN bin/run-tests` ; une seule suite complète à la fois ; arrêt sous 5 Go, aucune suite complète sous 6 Go.
- Jamais de fichier `.env*` ; aucun refus d'outil contourné.
- Branche partagée `feature/sprint-27-dev`, pas de checkout dans l'arbre partagé ; commits `git add <nouveaux fichiers>` puis `git commit -m "…" -- <chemins>`.
- Pas de push, tag ni dépôt de plugin sans GO de Thibaut ; aucun identifiant saisi en production ; ton neutre, aucune comparaison avec Sylius Plus ; aucun détail de sécurité dans le dépôt public.

## Actions de Thibaut

| Tâche | Action | Durée |
|-------|--------|-------|
| T-189-04 | Valider la maquette de la fiche produit (ou demander des retouches) | 0,5 h |
| T-189-05 | Fournir les photos ou éléments demandés par la maquette, ou donner le GO sur une banque libre | 0,25 h |
| (hors tâche) | Confirmer l'ordre de sacrifice et l'accusé de réception simulé d'US-188 | quelques minutes |
