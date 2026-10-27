# Tâches : Sprint 028 (noms, photos et import ERP)

> Sprint Planning Part 2 : décomposition faite au planning, le 2026-10-25, sur la branche partagée `feature/sprint-28-dev`. Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-28`).
>
> Liens : [objectif](../sprint-goal.md), [tableau](../task-board.md), [dépendances](../sprint-dependencies.md), [consigne de worker](../consigne-worker.md), [checklist](../checklist-integration.md), [tâches transverses](./technical-tasks.md), [rétro du sprint 27](../../sprint-27-fiche-produit-et-erp-fictif/sprint-retro.md), [revue du sprint 27](../../sprint-27-fiche-produit-et-erp-fictif/sprint-review.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Sacrifice | Statut |
|----|-------|--------|--------|--------|-----------|--------|
| [US-195](./US-195-tasks.md) | Noms de produits sans marque réelle | 2 | 7 | 4 h | **protégée** | ✅ (T-195-06 contrôlée en production le 2026-10-27) |
| [US-196](./US-196-tasks.md) | Photos du catalogue cohérentes et sans marque | 3 | 10 | 6,5 h | **n°2** | 🚫 (T-196-09 et T-196-10 contrôlées le 2026-10-27 : la page Crédits des images déborde à 360 px) |
| [US-187](./US-187-tasks.md) | Import CSV de prix et de stock : plugin (A, 5 pts) puis démonstration (B, 3 pts) | 8 | 21 | 17,2 h | **n°1 pour la tranche B** | ✅ (T-187-20 🟡) |
| [Transverse](./technical-tasks.md) | Actions de la rétro du sprint 27, contrôles, GO, clôture (sans points) | 0 | 10 | 6,5 h | non sacrifiable | 3 ✅, 3 🟡, 3 🔲, 1 👀 |
| **Total** | | **13** | **48** | **34,2 h** | | |

**Total décomposé : 13 points, 48 tâches, 34,2 h**, dont 0,95 h d'actions de Thibaut (4 tâches : choix sur la planche, trois sur le plugin : questions, GO, Packagist) : **33,25 h pour Claude**, orchestrateur compris.

> **Périmètre validé par Thibaut le 2026-10-24.** Noms sans marque (US-195), photos cohérentes (US-196), import de prix et de stock (US-187). L'ordre de sacrifice est **validé par Thibaut le 2026-10-25**.

## Capacité

Méthode des sprints précédents : 2,1 h par point + 8 h de tâches transverses.

| Périmètre | Capacité | Charge de Claude | Marge |
|-----------|----------|------------------|-------|
| 13 points | 13 × 2,1 + 8 = **35,3 h** | 33,25 h | **2,05 h** |

La marge (2,05 h) est supérieure à celle du sprint 27 (1,55 h) mais US-187 occupe 8 points à 16,75 h, soit 16,8 h attendues : **l'estimation à 8 points tient sans marge propre**. Ordre de sacrifice **validé par Thibaut le 2026-10-25** : **la tranche B d'US-187, puis US-196** ; US-195 est protégée.

| # | Sacrifice | Heures Claude libérées | Charge restante | Capacité du périmètre restant | Conséquence |
|---|-----------|------------------------|-----------------|-------------------------------|-------------|
| 0 | (charge décomposée) | n/a | 33,25 h (13 points) | 35,3 h | Marge de 2,05 h |
| 1 | US-187 tranche B (simulateur, import au chargement, page étendue, documentation) | 6,75 h | 26,5 h (10 points) | 29,0 h | Le plugin est livré et publié (`v0.2.0`) mais n'est pas encore branché sur la démo ; la tranche B reste au backlog |
| 2 | US-196 entière (planche, import, photos) | 6 h | 20,5 h (7 points) | 22,7 h | Les photos actuelles restent ; la planche déjà montrée à Thibaut est conservée pour un sprint suivant |

Une story sacrifiée au milieu de son budget est arrêtée et son état consigné, pas abandonnée en silence. L'attente d'un feu vert de Thibaut (T-187-02, T-187-10, T-187-12, T-196-04) n'est **pas** un sacrifice : les tâches concernées passent 📅 et les autres stories avancent.

## Répartition par type

| Type | Tâches | Heures |
|------|--------|--------|
| [DB] | 1 | 1,5 h |
| [BE] | 8 | 8,5 h |
| [FE-WEB] | 1 | 1,25 h |
| [FE-MOB] | 0 | 0 h |
| [TEST] | 12 | 8,5 h |
| [DOC] | 12 | 7,75 h |
| [REV] | 4 | 1,5 h |
| [OPS] | 10 | 5,2 h |
| **Total** | **48** | **34,2 h** |

## Fichiers principaux et chevauchements entre stories

`●` = fichier modifié, `○` = lu seulement.

| Fichier ou zone | US-195 | US-196 | US-187 | Transverse |
|-----------------|:------:|:------:|:------:|:----------:|
| `fixtures/csv/*.csv`, fichier de la liste noire | ● | ○ (noms) | | |
| `fixtures/images/`, `bin/fetch-product-images`, `src/Demo/Images/`, `ProductImageAttacher` | | ● | | |
| `src/Demo/Erp/`, page des échanges, `templates/admin/` ou `templates/demo/` | | | ● (B) | |
| `src/Demo/Command/DemoResetCommand.php`, `src/Demo/DataFixtures/` (nouveaux fichiers) | | | ● (B) | |
| `composer.json`, `composer.lock` | | | ● (B, après le tag) | |
| Dépôt du plugin `sylius-erp-flatfile-plugin` | | | ● (A) | |
| `translations/` (un nouveau domaine ou fichier par story) | | | ● | |
| `tests/` de la démo (nouveaux fichiers) | ● | ● | ● | ● |
| `config/demo/proof.json` | ● (rafraîchi) | ● (rafraîchi) | ● (rafraîchi) | ● (`bin/proof-rebase`) |
| `bin/render-page`, `bin/clean-test-caches`, `bin/check-disk`, `bin/proof-*`, `docs/tests-locaux.md` | | ○ | | ● |
| `project-management/templates/` | | | | ● (fait au planning) |
| `docs/erp-demo.md`, `docs/adr/0017-*.md`, `docs/images-produits.md` | | ● | ● | |

Ordre de merge proposé : transverses (T-TECH-28-01, 02) puis US-195 puis US-196 ; US-187 tranche A (dépôt du plugin) en parallèle, tranche B après le tag du plugin. `config/demo/proof.json` : `bin/proof-rebase` après chaque rebase, jamais fusionné à la main ; PR fusionnées en série.

## Règles du sprint

Reprises de [../consigne-worker.md](../consigne-worker.md) (à coller dans chaque consigne de worker) :

- Tests uniquement par `WORKER_ID=wN TEST_DB_NAME=b2b_wN TEST_CACHE_DIR=var/cache-wN bin/run-tests` ; une seule suite complète à la fois ; arrêt sous 5 Go, aucune suite complète sous 6 Go ; un seul cache de rendu.
- Jamais de fichier `.env*` ; aucun refus d'outil contourné.
- Branche partagée `feature/sprint-28-dev`, pas de checkout dans l'arbre partagé ; commits `git add <nouveaux fichiers>` puis `git commit -m "…" -- <chemins>` avec le **bon numéro de story**.
- Pas de push, tag ni dépôt de plugin sans GO de Thibaut ; aucun identifiant en production ; ton neutre ; aucun détail de sécurité dans le dépôt public ; « que devient cet état après un déploiement ? ».

## Actions de Thibaut

| Tâche | Action | Durée |
|-------|--------|-------|
| T-187-02 | Trancher les questions 3, 4 et 6 de l'ADR 0017 (propositions fournies) | 0,25 h |
| T-196-04 | Choisir les photos sur la planche et donner le GO explicite de téléchargement | 0,5 h |
| T-187-10 | GO pour push, tag et release du plugin (`v0.2.0`) | 0,1 h |
| T-187-12 | Soumettre ou mettre à jour le paquet sur Packagist s'il le faut | 0,1 h |
