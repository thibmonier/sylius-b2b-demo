# Tâches : Sprint 030 (compte, tableau de bord et TVA)

> Sprint Planning Part 2 : décomposition faite au planning, le 2026-10-25 ; développement sur la branche partagée `feature/sprint-30-dev`. Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-30`).
>
> Liens : [objectif](../sprint-goal.md), [tableau](../task-board.md), [dépendances](../sprint-dependencies.md), [consigne de worker](../consigne-worker.md), [checklist](../checklist-integration.md), [registre des décisions](../registre-decisions.md), [tâches transverses](./technical-tasks.md), [rétro du sprint 29](../../sprint-29-liste-panier-et-photos/sprint-retro.md), [revue du sprint 29](../../sprint-29-liste-panier-et-photos/sprint-review.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Sacrifice | Statut |
|----|-------|--------|--------|--------|-----------|--------|
| [US-199](./US-199-tasks.md) | Refonte de la page « Mon compte » | 5 | 11 | 10,5 h | **protégée** | 🔲 (code 📅 jusqu'au GO de la maquette) |
| [US-201](./US-201-tasks.md) | Tableau de bord des achats plus lisible (surcharge dans la démo) | 5 | 11 | 10,5 h | **n°2** | 🔲 (code 📅 jusqu'au GO de la maquette) |
| [US-202](./US-202-tasks.md) | Catégories de taxe : TVA au panier et sur les commandes | 3 | 9 | 6 h | **n°1** | 🔲 (sans maquette) |
| [Transverse](./technical-tasks.md) | Actions de la rétro du sprint 29, contrôles, clôture (sans points) | 0 | 10 | 7,5 h | non sacrifiable | 1 ✅, 9 🔲 |
| **Total** | | **13** | **41** | **34,5 h** | | |

**Total décomposé : 13 points, 41 tâches, 34,5 h**, dont 1 h d'actions de Thibaut (2 tâches : GO de la maquette « Mon compte », GO de la maquette du tableau de bord) : **33,5 h pour Claude**, orchestrateur compris.

> **Périmètre décidé par Thibaut le 2026-10-25** (voir [../registre-decisions.md](../registre-decisions.md)) : « Mon compte » (US-199), tableau de bord des achats (US-201, surcharge dans la démo) et catégories de taxe (US-202, limite comparée au HT), plus les tâches reportées. Ordre de sacrifice : US-202, puis US-201 ; US-199 protégée.

## Capacité

Méthode des sprints précédents : 2,1 h par point + 8 h de tâches transverses.

| Périmètre | Capacité | Charge de Claude | Marge |
|-----------|----------|------------------|-------|
| 13 points | 13 × 2,1 + 8 = **35,3 h** | 33,5 h | **1,8 h** |

**Les 13 points tiennent, avec une marge de 1,8 h (5 %), comme au sprint 29.** Les deux refontes sont à leur capacité exacte (10 h de Claude pour 10,5 h) ; US-202 a 0,3 h de marge propre ; les transverses prennent 7,5 h sur 8. Ce qui rend le sprint tenable : 13 points livrés aux sprints 27 à 29 ; US-202 et les transverses comblent l'attente des GO ; les sous-pages du compte sont négociables. Ce qui peut le faire basculer : les retouches des deux maquettes, la surcharge du graphique du plugin et la limite HT (que le plugin ne fait pas : voir US-202).

| # | Sacrifice | Heures Claude libérées | Charge restante | Capacité du périmètre restant | Conséquence |
|---|-----------|------------------------|-----------------|-------------------------------|-------------|
| 0 | (charge décomposée) | n/a | 33,5 h (13 points) | 35,3 h | Marge de 1,8 h |
| 1 | US-202 entière | 6 h | 27,5 h (10 points) | 29,0 h | Le panier reste avec « Total » seul, sans TVA |
| 2 | US-201 entière | 10 h | 17,5 h (5 points) | 18,5 h | Le tableau de bord garde le style du plugin ; la maquette validée est conservée pour un sprint suivant |

Une story sacrifiée au milieu de son budget est arrêtée et son état consigné, pas abandonnée en silence. L'attente d'un GO de Thibaut (T-199-04, T-201-04) n'est **pas** un sacrifice : les tâches concernées passent 📅 et les autres avancent.

## Répartition par type

| Type | Tâches | Heures |
|------|--------|--------|
| [DB] | 1 | 1 h |
| [BE] | 1 | 0,75 h |
| [FE-WEB] | 5 | 10,75 h |
| [FE-MOB] | 0 | 0 h |
| [TEST] | 13 | 8,5 h |
| [DOC] | 10 | 6,25 h |
| [REV] | 4 | 2 h |
| [OPS] | 7 | 5,25 h |
| **Total** | **41** | **34,5 h** |

## Fichiers communs et attribution (règle 21, action 3 de la rétro du sprint 29)

`●` = fichier modifié, `○` = lu seulement. **Qui commite** : le worker indiqué ; un fichier marqué « section » n'est commité qu'après `git diff -- <fichier>` relu : il ne doit contenir que les lignes du worker, sinon il attend et prévient l'orchestrateur.

| Fichier ou zone | US-199 (w199) | US-201 (w201) | US-202 (w202) | Transverse | Qui commite |
|-----------------|:------:|:------:|:------:|:----------:|-------------|
| `templates/shop/theme_b2b/account/` (nouveau) | ● | | | | w199 |
| `templates/shop/theme_b2b/insights/` (nouveau), `templates/bundles/` du plugin company-insights | | ● | | | w201 |
| `templates/shop/theme_b2b/cart/` | | | ● | | w202 |
| `config/packages/demo_theme_b2b_account.yaml` (nouveau) | ● | | | | w199 |
| `config/packages/demo_theme_b2b_insights.yaml` (nouveau) | | ● | | | w201 |
| `config/packages/demo_theme_b2b.yaml` | ○ | ○ | ○ | | **personne** (aucune modification ce sprint) |
| `assets/shop/styles/_account.scss`, `_insights.scss` (nouveaux) | ● | ● | | | chacun le sien |
| `assets/shop/styles/b2b.scss` (une ligne `@import` par story) | ● | ● | | | section : chacun sa ligne |
| `translations/account.*`, `translations/insights.*`, `translations/cart.*` | ● | ● | ● | | chacun son domaine |
| `config/packages/demo_fixtures.yaml`, `fixtures/`, `src/Demo/DataFixtures/` | | ○ | ● | | w202 |
| Import ERP simulé de la démo, `src/Demo/Theme/Cart*`, décoration d'approbation | | | ● | | w202 |
| `docs/theme-b2b.md` | ● | ● | ● | | section : chacun la sienne |
| `docs/images/` | ● (`account-*`) | ● (`insights-*`) | ● (`cart-*`) | | chacun ses fichiers |
| `tests/` de la démo (nouveaux fichiers) | ● | ● | ● | ● | chacun les siens |
| `config/demo/proof.json` | | | | ● | orchestrateur (`bin/proof-rebase`) |
| `bin/render-page`, `bin/clean-test-caches`, `bin/check-disk`, `bin/proof-*`, `docs/tests-locaux.md`, `ErpDemoExchangeFixtureTest` | ○ | ○ | ○ | ● | orchestrateur |
| Plugins (`vendor/tmonier/*`) | ○ | ○ | ○ | | **personne** (aucun plugin modifié) |

Chevauchements à surveiller : US-202 change les montants (TVA) que lit le tableau de bord d'US-201 ; l'orchestrateur fait merger US-202 avant les captures finales d'US-201, ou note au registre que le tableau de bord reste en HT. Ordre de merge proposé : T-TECH-30-01 à 04, puis US-202, US-199, US-201 ; PR fusionnées en série, `bin/proof-rebase` après chaque rebase.

## Workers proposés

| Worker | Story | Domaine de fichiers | Chevauchements |
|--------|-------|---------------------|----------------|
| w202 | US-202 | fixtures, import ERP, panier, approbation (démo) | montants lus par US-201 |
| w199 | US-199 | compte (`account/`, hooks et styles propres) | `b2b.scss` (une ligne), `docs/theme-b2b.md` (section) |
| w201 | US-201 | tableau de bord (`insights/`, `templates/bundles/`, hooks et styles propres) | `b2b.scss` (une ligne), `docs/theme-b2b.md` (section), montants d'US-202 |

w202 démarre dès le planning (sans maquette) ; w199 et w201 font cadrage et état « avant » pendant que l'orchestrateur fait T-TECH-30-01 à 03 puis les deux maquettes.

## Règles du sprint

Reprises de [../consigne-worker.md](../consigne-worker.md) (à coller dans chaque consigne de worker) :

- Tests uniquement par `WORKER_ID=wN TEST_DB_NAME=b2b_wN TEST_CACHE_DIR=var/cache-wN bin/run-tests`, **dans l'arbre partagé** (jamais depuis un worktree au `vendor` en lien symbolique) ; une seule suite complète à la fois ; arrêt sous 5 Go, aucune suite complète sous 6 Go ; un seul cache de rendu.
- Jamais de fichier `.env*` ; aucun refus d'outil contourné ; jamais de `cp -R`, `rm -rf` ni `mv` sur un chemin issu d'une variable ou d'un glob sans garde (règle 18).
- Branche partagée `feature/sprint-30-dev`, pas de checkout dans l'arbre partagé ; commits `git add <nouveaux fichiers>` puis `git commit -m "…" -- <chemins>` avec le **bon numéro de story** ; fichiers communs selon la matrice ci-dessus.
- **Aucun Twig du compte ni du tableau de bord avant le GO de la maquette** ; aucun plugin modifié ; aucun identifiant en production ; ton neutre ; aucun détail de sécurité dans le dépôt public.

## Actions de Thibaut

| Tâche | Action | Durée |
|-------|--------|-------|
| T-199-04 | Valider la maquette « Mon compte » | 0,5 h |
| T-201-04 | Valider la maquette du tableau de bord des achats | 0,5 h |
