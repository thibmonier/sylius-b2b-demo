# Tâches : Sprint 031 (chaîne d'approvisionnement, comptes des plugins et page Plugins)

> Sprint Planning Part 2 : décomposition faite au planning, le 2026-10-27 ; développement sur la branche partagée `feature/sprint-31-dev` (créée depuis `main` après la fusion du planning). Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-31`).
>
> Liens : [objectif](../sprint-goal.md), [tableau](../task-board.md), [dépendances](../sprint-dependencies.md), [consigne de worker](../consigne-worker.md), [checklist](../checklist-integration.md), [registre des décisions](../registre-decisions.md), [tâches transverses](./technical-tasks.md), [rétro du sprint 30](../../sprint-30-compte-tableau-de-bord-et-tva/sprint-retro.md), [revue du sprint 30](../../sprint-30-compte-tableau-de-bord-et-tva/sprint-review.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Sacrifice | Statut |
|----|-------|--------|--------|--------|-----------|--------|
| [US-208](./US-208-tasks.md) | Chaîne d'approvisionnement PHP : politique Composer, revue du verrou, workflows durcis | 5 | 11 | 9,5 h | **protégée** | 🔲 (sans maquette) |
| [US-203](./US-203-tasks.md) | Pages de compte des plugins (devis, paiements à terme, assistants IA) alignées sur « Mon compte » | 5 | 15 | 11 h | **n°2** | 🔲 (Twig du relevé 📅 jusqu'au GO de la planche) |
| [US-204](./US-204-tasks.md) | Refonte de la page « Plugins » | 3 | 11 | 7,25 h | **n°1** | 🔲 (code 📅 jusqu'au GO de la maquette) |
| [Transverse](./technical-tasks.md) | Actions de la rétro du sprint 30, contrôles, clôture (sans points) | 0 | 11 | 8,5 h | non sacrifiable | 1 ✅, 10 🔲 |
| **Total** | | **13** | **48** | **36,25 h** | | |

**Total décomposé : 13 points, 48 tâches, 36,25 h**, dont 1,25 h d'actions de Thibaut (3 tâches : GO de la planche du relevé, GO de la maquette « Plugins », contrôles requis de `main`) : **35 h pour Claude**, orchestrateur compris.

> **Périmètre décidé par Thibaut le 2026-10-27** (voir [../registre-decisions.md](../registre-decisions.md)) : chaîne d'approvisionnement PHP de la démo (US-208), pages de compte des plugins par surcharge dans la démo (US-203), page « Plugins » (US-204), plus les actions de la rétro du sprint 30. Ordre de sacrifice : US-204, puis T-TECH-31-11 (LCP de la home), puis US-203 ; US-208 protégée. US-207 vient après US-204 à US-206, US-209 après US-208 : hors sprint.

## Capacité

Méthode des sprints précédents : 2,1 h par point + 8 h de tâches transverses.

| Périmètre | Capacité | Charge de Claude | Marge |
|-----------|----------|------------------|-------|
| 13 points | 13 × 2,1 + 8 = **35,3 h** | 35 h | **0,3 h** (≈ 1 %) |

**Les 13 points tiennent, avec une marge de 0,3 h**, bien plus mince qu'aux sprints 29 et 30 (1,8 h) depuis l'ajout de T-TECH-31-11. Ce qui rend le sprint tenable : US-208 est sans maquette ni porte de Thibaut et avance dès le planning ; les devis et les assistants IA réutilisent la maquette validée d'US-199 (une seule planche nouvelle, celle du relevé) ; les tâches 📅 d'US-204 et du relevé n'attendent que deux GO. Ce qui peut le faire basculer : les retouches de la planche et de la maquette ; la dérive des templates des plugins à reprendre dans le cadre du compte ; le passage du wishlist sur une version stable (changement de `composer.lock` à vérifier hors de l'arbre partagé) ; zizmor, qui peut remonter plusieurs alertes dans le workflow existant.

| # | Sacrifice | Heures Claude libérées | Charge restante | Capacité du périmètre restant | Conséquence |
|---|-----------|------------------------|-----------------|-------------------------------|-------------|
| 0 | (charge décomposée) | n/a | 35 h (13 points) | 35,3 h | Marge de 0,3 h |
| 0b | T-203-08 seule (page de consentement, négociable) | 0,75 h | 34,25 h | 35,3 h | La page de consentement garde son style actuel |
| 1 | US-204 entière | 6,75 h | 28,25 h (10 points) | 29,0 h | La page « Plugins » garde son style ; la maquette validée, si elle l'est, est conservée pour un sprint suivant |
| 2 | T-TECH-31-11 (LCP de la home) | 2 h | 26,25 h (10 points) | 29,0 h | Le LCP mobile de la home n'est pas corrigé dans le sprint ; les mesures de la PR #123 restent la référence |
| 3 | US-203 entière | 10,5 h | 15,75 h (5 points) | 18,5 h | Les pages de compte des plugins gardent leur cadre d'origine |

US-208 n'est jamais sacrifiée. Une story sacrifiée au milieu de son budget est arrêtée et son état consigné, pas abandonnée en silence. L'attente d'un GO de Thibaut (T-203-04, T-204-04) n'est **pas** un sacrifice : les tâches concernées passent 📅 et les autres avancent.

## Répartition par type

| Type | Tâches | Heures |
|------|--------|--------|
| [DB] | 0 | 0 h |
| [BE] | 1 | 1,5 h |
| [FE-WEB] | 6 | 8,25 h |
| [FE-MOB] | 0 | 0 h |
| [TEST] | 15 | 11 h |
| [DOC] | 10 | 5,75 h |
| [REV] | 4 | 1,75 h |
| [OPS] | 12 | 8 h |
| **Total** | **48** | **36,25 h** |

## Fichiers communs et attribution (règle 21)

`●` = fichier modifié, `○` = lu seulement. **Qui commite** : le worker indiqué ; un fichier marqué « section » n'est commité qu'après `git diff -- <fichier>` relu : il ne doit contenir que les lignes du worker, sinon il attend et prévient l'orchestrateur.

| Fichier ou zone | US-208 (w208) | US-203 (w203) | US-204 (w204) | Transverse | Qui commite |
|-----------------|:------:|:------:|:------:|:----------:|-------------|
| `composer.json`, `composer.lock`, `docker/Dockerfile`, `.github/` | ● | | | ○ | w208 seul |
| `vendor/` de l'arbre partagé | | | | ● | **orchestrateur seul** (`composer install` après la fusion d'US-208 ; aucun worker n'y touche) |
| `bin/check-lock-diff`, `tests/Unit/Supply/`, `tests/Fixtures/composer-lock/` (nouveaux) | ● | | | | w208 |
| `docs/durcissement-demo.md` | ● | | | | w208 |
| `templates/bundles/` des plugins quote-request, trade-credit, b2b-mcp (nouveaux) | | ● | | | w203 |
| `config/packages/demo_theme_b2b_account_plugins.yaml` (nouveau) | | ● | | | w203 |
| `config/packages/demo_theme_b2b_account.yaml`, `templates/shop/theme_b2b/account/` | | ○ | | | **personne** (US-199 livrée) |
| `templates/shop/plugins/`, contrôleur `PluginsController` | | | ● | | w204 |
| `assets/shop/styles/_account-plugins.scss`, `_plugins.scss` (nouveaux) | | ● | ● | | chacun le sien |
| `assets/shop/styles/b2b.scss` (une ligne `@import` par story) | | ● | ● | | section : chacun sa ligne |
| `translations/account_plugins.*`, `translations/plugins.*` | | ● | ● | | chacun son domaine |
| `docs/theme-b2b.md` | | ● | | | w203 (section) |
| `docs/captures-vitrine.md` | | ● | ● | | section : chacun la sienne |
| `docs/images/` | | ● (`account-quotes-*`, `account-statement-*`, `account-connections-*`) | ● (`plugins-*`) | | chacun ses fichiers |
| `tests/` de la démo (nouveaux fichiers) | ● | ● | ● | ● | chacun les siens |
| `tests/Functional/Demo/PluginsPageCompatibilityNoteTest.php`, `tests/Smoke/Plugin*` | | | ○ | | **personne** (non modifiés) |
| `config/demo/proof.json` | | | | ● | orchestrateur (`bin/proof-rebase`) |
| `bin/render-page`, `bin/check-disk`, `bin/clean-test-caches`, `bin/proof-*`, `docs/tests-locaux.md` | ○ | ○ | ○ | ● | orchestrateur |
| Plugins (`vendor/tmonier/*`) | ○ | ○ | | | **personne** (aucun plugin modifié) |

Chevauchements à surveiller : US-208 modifie `composer.lock` (wishlist) et `.github/workflows/ci.yaml`, que la CI de la PR du sprint rejoue : l'orchestrateur intègre US-208 d'abord, lance `composer install` dans l'arbre partagé **seulement après** la fusion, et les autres workers n'ont aucun changement de dépendances en cours de sprint. w203 et w204 écrivent chacun leur section de `docs/captures-vitrine.md`. Ordre d'intégration proposé sur la branche partagée : T-TECH-31-01 à 04, puis US-208, US-203, US-204 ; `bin/proof-rebase` après chaque rebase.

## Workers proposés et parallélisme (règle 24, action 1 de la rétro du sprint 30)

| Worker | Story | Domaine de fichiers | Tests lancés | Chevauchements |
|--------|-------|---------------------|--------------|----------------|
| w208 | US-208 | dépendances, workflows, scripts de CI | légers (scripts, fixtures de verrou), suite complète en fin de story | `docs/durcissement-demo.md` seul ; `composer.lock` |
| w203 | US-203 | surcharges de `templates/bundles/` des trois plugins, hooks et styles propres | moyens (fonctionnels, pa11y) | `b2b.scss` (une ligne), `docs/theme-b2b.md` (section), `docs/captures-vitrine.md` (section) |
| w204 | US-204 | page « Plugins » (`templates/shop/plugins/`, styles propres) | légers (tests existants de la page) | `b2b.scss` (une ligne), `docs/captures-vitrine.md` (section) |

Avant de lancer les workers, l'orchestrateur relève `df -h /`, la mémoire libre et le swap (T-TECH-31-01, T-TECH-31-07). **Au-delà de 2 Go de swap ou sous 7 Go de disque, au plus deux workers lancent des tests en même temps**, les autres préparent du code ; aucune suite complète sous 6 Go. Ordre naturel : w208 et w203 d'abord (w204 n'a que du cadrage jusqu'au GO de la maquette) ; une suite complète à la fois.

w208 démarre dès le planning (aucune porte) ; w203 et w204 font cadrage et état « avant » pendant que l'orchestrateur fait T-TECH-31-01 à 04 puis la planche du relevé (T-203-03) et la maquette « Plugins » (T-204-03).

## Règles du sprint

Reprises de [../consigne-worker.md](../consigne-worker.md) (à coller dans chaque consigne de worker) :

- Tests uniquement par `WORKER_ID=wN TEST_DB_NAME=b2b_wN TEST_CACHE_DIR=var/cache-wN bin/run-tests`, **dans l'arbre partagé** (jamais depuis un worktree au `vendor` en lien symbolique) ; une seule suite complète à la fois ; au plus 2 workers qui lancent des tests (règle 24) ; arrêt sous 5 Go, aucune suite complète sous 6 Go ; un seul cache de rendu.
- Jamais de fichier `.env*` ; aucun refus d'outil contourné ; jamais de `cp -R`, `rm -rf` ni `mv` sur un chemin issu d'une variable ou d'un glob sans garde (règle 18).
- Branche partagée `feature/sprint-31-dev`, pas de checkout dans l'arbre partagé ; commits `git add <nouveaux fichiers>` puis `git commit -m "…" -- <chemins>` avec le **bon numéro de story** ; fichiers communs selon la matrice ci-dessus.
- **Aucun Twig de la page « Plugins » ni du relevé de paiements à terme avant le GO de la maquette** ; aucun plugin modifié ; `vendor` de l'arbre partagé jamais modifié par un worker ; aucun identifiant ni nom de secret dans le dépôt public ; ton neutre ; aucun détail de sécurité dans le dépôt public.

## Actions de Thibaut

| Tâche | Action | Durée |
|-------|--------|-------|
| T-203-04 | Valider la planche du relevé de paiements à terme | 0,5 h |
| T-204-04 | Valider la maquette de la page « Plugins » | 0,5 h |
| T-208-11 | Ajouter le job zizmor aux contrôles requis de `main` | 0,25 h |
