# Tâches : Sprint 029 (liste, panier et photos)

> Sprint Planning Part 2 : décomposition faite au planning, le 2026-10-25, sur la branche partagée `feature/sprint-29-dev`. Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-29`).
>
> Liens : [objectif](../sprint-goal.md), [tableau](../task-board.md), [dépendances](../sprint-dependencies.md), [consigne de worker](../consigne-worker.md), [checklist](../checklist-integration.md), [registre des décisions](../registre-decisions.md), [tâches transverses](./technical-tasks.md), [rétro du sprint 28](../../sprint-28-noms-photos-et-import-erp/sprint-retro.md), [revue du sprint 28](../../sprint-28-noms-photos-et-import-erp/sprint-review.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Sacrifice | Statut |
|----|-------|--------|--------|--------|-----------|--------|
| [US-198](./US-198-tasks.md) | Refonte de la page de liste des produits | 5 | 11 | 11 h | **protégée** | 🔲 (code 📅 jusqu'au GO de la maquette) |
| [US-200](./US-200-tasks.md) | Refonte de la page panier | 5 | 11 | 10,75 h | **n°2** | 🔲 (code 📅 jusqu'au GO de la maquette) |
| [US-197](./US-197-tasks.md) | Photos du catalogue : cadrage et variantes de couleur | 3 | 9 | 6,75 h | **n°1** | 🔲 (tranche 2 📅 jusqu'au GO de la planche) |
| [Transverse](./technical-tasks.md) | Actions des rétros des sprints 27 et 28, contrôles, clôture (sans points) | 0 | 8 | 6,5 h | non sacrifiable | 1 ✅, 7 🔲 |
| **Total** | | **13** | **39** | **35 h** | | |

**Total décomposé : 13 points, 39 tâches, 35 h**, dont 1,5 h d'actions de Thibaut (3 tâches : GO de la maquette de la liste, GO de la maquette du panier, choix sur la planche et GO de téléchargement) : **33,5 h pour Claude**, orchestrateur compris.

> **Périmètre décidé par Thibaut le 2026-10-25** (voir [../registre-decisions.md](../registre-decisions.md)) : liste (US-198), panier (US-200) et cadrage des photos (US-197), plus les trois tâches reportées du sprint 28. Ordre de sacrifice : US-197, puis US-200 ; US-198 protégée.

## Capacité

Méthode des sprints précédents : 2,1 h par point + 8 h de tâches transverses.

| Périmètre | Capacité | Charge de Claude | Marge |
|-----------|----------|------------------|-------|
| 13 points | 13 × 2,1 + 8 = **35,3 h** | 33,5 h | **1,8 h** |

**Les 13 points tiennent, avec une marge de 1,8 h (5 %), plus étroite que celle du sprint 28 (2,05 h).** Les deux refontes sont estimées à 10,5 h chacune pour 10,5 h de capacité (5 × 2,1) : il n'y a pas de marge propre dans les stories. Ce qui rend le sprint tenable : (1) 13 points a été livré aux sprints 27 et 28 avec une marge comparable ; (2) l'attente des GO de maquette est comblée par US-197 (tranche 1) et les transverses ; (3) la vue liste d'US-198 et la commande rapide d'US-200 sont négociables. Ce qui peut le faire basculer : deux maquettes à valider (les retouches de la fiche produit ont coûté du temps au sprint 27), le disque (5,5 Go libres, aucune suite complète sous 6 Go) et les retouches de la planche.

| # | Sacrifice | Heures Claude libérées | Charge restante | Capacité du périmètre restant | Conséquence |
|---|-----------|------------------------|-----------------|-------------------------------|-------------|
| 0 | (charge décomposée) | n/a | 33,5 h (13 points) | 35,3 h | Marge de 1,8 h |
| 1 | US-197 entière | 6,25 h | 27,25 h (10 points) | 29,0 h | Les photos restent cadrées au centre et aux couleurs actuelles ; le point focal (tranche 1) peut être gardé s'il est mergé |
| 2 | US-200 entière | 10,25 h | 17,0 h (5 points) | 18,5 h | Le panier garde son thème actuel ; la maquette validée est conservée pour un sprint suivant |

Une story sacrifiée au milieu de son budget est arrêtée et son état consigné, pas abandonnée en silence. L'attente d'un GO de Thibaut (T-198-04, T-200-04, T-197-05) n'est **pas** un sacrifice : les tâches concernées passent 📅 et les autres stories avancent.

## Répartition par type

| Type | Tâches | Heures |
|------|--------|--------|
| [DB] | 0 | 0 h |
| [BE] | 2 | 1,75 h |
| [FE-WEB] | 4 | 10 h |
| [FE-MOB] | 0 | 0 h |
| [TEST] | 12 | 9 h |
| [DOC] | 11 | 7,5 h |
| [REV] | 3 | 1,75 h |
| [OPS] | 7 | 5 h |
| **Total** | **39** | **35 h** |

## Fichiers principaux et chevauchements entre stories

`●` = fichier modifié, `○` = lu seulement.

| Fichier ou zone | US-198 | US-200 | US-197 | Transverse |
|-----------------|:------:|:------:|:------:|:----------:|
| `templates/shop/theme_b2b/` (liste : `product/`) | ● | | | |
| `templates/shop/theme_b2b/` (panier : `cart/`) | | ● | | |
| Composant d'image de la carte et de la fiche (`object-position`) | ○ (réutilise) | | ● (T-197-03) | |
| `config/packages/demo_theme_b2b.yaml` (hooks) | ● (liste) | ● (panier) | | |
| `assets/shop/styles/b2b.scss` | ● | ● | ● (cadrage) | |
| `translations/` (un domaine par story) | ● | ● | | |
| `fixtures/images/`, `bin/fetch-product-images`, `src/Demo/Images/`, `ProductImageAttacher` | | | ● | |
| `tests/` de la démo (nouveaux fichiers) | ● | ● | ● | ● |
| `config/demo/proof.json` | ● (rafraîchi) | ● (rafraîchi) | ● (rafraîchi) | ● (`bin/proof-rebase`) |
| `bin/render-page`, `bin/clean-test-caches`, `bin/check-disk`, `bin/proof-*`, `docs/tests-locaux.md` | ○ | ○ | ○ | ● |
| `docs/theme-b2b.md`, `docs/images/` | ● | ● | | |
| `docs/images-produits.md` | | | ● | |

`b2b.scss` et `demo_theme_b2b.yaml` sont touchés par trois workers : une section par story (préfixes `product-list`, `cart`, `product-image`), jamais de reformatage du fichier entier. Ordre de merge proposé : T-TECH-29-01 et 02, puis US-197 tranche 1 (point focal), US-198, US-200, US-197 tranche 2 ; `config/demo/proof.json` : `bin/proof-rebase` après chaque rebase, PR fusionnées en série.

## Règles du sprint

Reprises de [../consigne-worker.md](../consigne-worker.md) (à coller dans chaque consigne de worker) :

- Tests uniquement par `WORKER_ID=wN TEST_DB_NAME=b2b_wN TEST_CACHE_DIR=var/cache-wN bin/run-tests` ; une seule suite complète à la fois ; arrêt sous 5 Go, aucune suite complète sous 6 Go ; un seul cache de rendu.
- Jamais de fichier `.env*` ; aucun refus d'outil contourné ; jamais de `cp -R`, `rm -rf` ni `mv` sur un chemin issu d'une variable ou d'un glob sans garde (règle 18).
- Branche partagée `feature/sprint-29-dev`, pas de checkout dans l'arbre partagé ; commits `git add <nouveaux fichiers>` puis `git commit -m "…" -- <chemins>` avec le **bon numéro de story**.
- **Aucun Twig de la liste ni du panier avant le GO de la maquette ; aucun téléchargement de photo avant le GO de la planche** ; aucun identifiant en production ; ton neutre ; aucun détail de sécurité dans le dépôt public.

## Actions de Thibaut

| Tâche | Action | Durée |
|-------|--------|-------|
| T-198-04 | Valider la maquette de la liste des produits | 0,5 h |
| T-200-04 | Valider la maquette du panier | 0,5 h |
| T-197-05 | Choisir les photos sur la planche et donner le GO explicite de téléchargement | 0,5 h |
