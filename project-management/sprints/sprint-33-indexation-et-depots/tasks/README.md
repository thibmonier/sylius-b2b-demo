# Tâches : Sprint 033 (indexation des pages vitrine et dépôts de plugins)

> Sprint Planning Part 2 : décomposition faite au planning, le 2026-10-28 ; développement sur la branche `feature/sprint-33-indexation-et-depots` (arbre principal, déjà créée). Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-33`).
>
> Liens : [objectif](../sprint-goal.md), [tableau](../task-board.md), [dépendances](../sprint-dependencies.md), [consigne de worker](../consigne-worker.md), [checklist](../checklist-integration.md), [registre des décisions](../registre-decisions.md), [tâches transverses](./technical-tasks.md), [rétro du sprint 32](../../sprint-32-preuve-et-architecture/sprint-retro.md), [revue du sprint 32](../../sprint-32-preuve-et-architecture/sprint-review.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Sacrifice (registre) | Statut |
|----|-------|--------|--------|--------|----------------------|--------|
| [US-207](./US-207-tasks.md) | Indexer les pages vitrine : sitemap, hreflang, métadonnées et données structurées | 5 | 15 | 10 h | **protégée** | 🔲 cadrage, 📅 GO de la liste blanche |
| [US-209](./US-209-tasks.md) | Chaîne d'approvisionnement des dépôts de plugins | 8 | 15 | 16,5 h | **n°1 tranche (b) Composer, n°2 tranche (a) workflows** | 🔲 prête, sans porte |
| [Transverse](./technical-tasks.md) | Actions de la rétro du sprint 32, reliquats, contrôles, clôture (sans points) | 0 | 15 | 8,5 h | non sacrifiable (sauf T-TECH-33-09) | 2 ✅ |
| **Total** | | **13** | **45** | **35 h** | | |

**Total décomposé : 13 points, 45 tâches, 35 h**, dont 0,75 h d'actions de Thibaut (3 tâches : GO de la liste blanche, déclaration du sitemap, disque libéré) : **34,25 h pour Claude**, orchestrateur compris.

> **Périmètre décidé par Thibaut le 2026-10-28** (voir [../registre-decisions.md](../registre-decisions.md)) : US-207, US-209, plus les actions de la rétro du sprint 32 et ses reliquats. **Ordre de sacrifice décidé** : US-209 tranche (b) Composer, puis tranche (a) workflows ; US-207 protégée. GO dépôts de plugins : push, PR et fusion si la CI est verte, sans tag.

## Capacité

Méthode des sprints précédents : 2,1 h par point + 8 h de tâches transverses.

| Périmètre | Capacité | Charge de Claude | Marge |
|-----------|----------|------------------|-------|
| 13 points | 13 × 2,1 + 8 = **35,3 h** | 34,25 h | **1,05 h** (≈ 3 %) |

**Les 13 points tiennent, avec une marge de 1,05 h** : aucun sacrifice n'est appliqué au planning. Ce qui rend le sprint tenable : pas de maquette ni de porte de Thibaut pour US-209 (le GO est au registre) ; le script fait le travail répétitif des 29 dépôts (environ 8 minutes par dépôt, 1 h par lot de sept) ; US-207 ne change pas le rendu des pages (capture de constat de 0,25 h) ; la plupart des tâches d'US-209 demandent peu de disque. Ce qui peut le faire basculer : une CI rouge préexistante ou une branche protégée sur plusieurs des 29 dépôts (chaque exception coûte plus que les 8 minutes prévues) ; la version de Composer de la CI de certains plugins, qui peut ne pas lire `config.policy` ; les tests existants à adapter pour US-207 (13 lignes de la table) ; le disque (5 Go libres : les tâches de test et de rendu d'US-207 restent 📅 jusqu'au retour au-dessus de 5 Go).

Ordre de sacrifice décidé par Thibaut (registre du 2026-10-28), appliqué **dans l'ordre** si le suivi montre un dépassement en cours de sprint. Répartition indicative des points d'US-209 d'après les heures : tranche (a) 7, tranche (b) 1.

| # | Sacrifice | Heures Claude libérées | Charge restante | Capacité du périmètre restant | Conséquence |
|---|-----------|------------------------|-----------------|-------------------------------|-------------|
| 0 | (charge décomposée) | n/a | 34,25 h (13 points) | 35,3 h | Marge de 1,05 h |
| 0b | T-TECH-33-09 seule (images produit locales, capture du panier, négociable) | 1 h | 33,25 h | 35,3 h | La capture du panier reste en reliquat (DoD de T-TECH-31-04) |
| 1 | US-209 **tranche (b) Composer** (T-209-04 et ses parts dans les tests et les lots) | environ 2,75 h | 31,5 h (12 points) | 33,2 h | Les PR de dépôts ne portent que les workflows ; audit Composer, politique, `allow-plugins` et revue du verrou retournent au backlog ; story livrée « tranche (a) » |
| 2 | US-209 **tranche (a) workflows**, ensuite | environ 13,75 h | 17,75 h (5 points) | 18,5 h | US-209 entière retourne au backlog (relevé de départ conservé) ; il reste US-207 et les transverses |

**US-207 n'est jamais sacrifiée.** Une story sacrifiée au milieu de son budget est arrêtée et son état consigné, pas abandonnée en silence : les dépôts déjà fusionnés restent conformes, le rapport final liste ceux qui ne le sont pas. L'attente d'un GO de Thibaut (T-207-03, décision d'une exception de T-209-11) ou du disque n'est **pas** un sacrifice : les tâches concernées passent 📅 et les autres avancent.

## Répartition par type

| Type | Tâches | Heures |
|------|--------|--------|
| [DB] | 0 | 0 h |
| [BE] | 3 | 3 h |
| [FE-WEB] | 1 | 1 h |
| [FE-MOB] | 0 | 0 h |
| [TEST] | 9 | 6,75 h |
| [DOC] | 10 | 6,25 h |
| [REV] | 3 | 1,25 h |
| [OPS] | 19 | 16,75 h |
| **Total** | **45** | **35 h** |

## Fichiers communs et attribution (règle 21)

`●` = fichier modifié, `○` = lu seulement. **Qui commite** : le worker indiqué ; un fichier marqué « section » n'est commité qu'après `git diff -- <fichier>` relu : il ne doit contenir que les lignes du worker, sinon il attend et prévient l'orchestrateur.

| Fichier ou zone | US-207 (w207) | US-209 (w209) | Transverse | Qui commite |
|-----------------|:------:|:------:|:----------:|-------------|
| `src/Demo/Http/NoIndexSubscriber.php`, `src/Demo/Controller/RobotsController.php`, nouveau `src/Demo/Seo/` | ● | | | w207 |
| `templates/shop/seo/metatags.html.twig` | ● | | | w207 |
| `translations/*.{fr,en}.yaml` des pages vitrine (clés de titre et de description), `translations/architecture.*` (une phrase) | ● | | | w207 (section : ses clés seulement) |
| Tests existants de la table des invariants ([US-207-tasks.md](./US-207-tasks.md)), `tests/Fixtures/bin/demo-mode-router.php`, `bin/verify-public-mode` | ● | | | w207 (une ligne de registre avant chaque modification) |
| `docs/ouverture-publique.md`, `docs/durcissement-demo.md`, `docs/seo-et-vitesse.md`, `docs/dossier-de-go.md` | ● | | | w207 |
| `docs/captures-vitrine.md` | ● (section) | | ● (section, T-TECH-33-06) | section : chacun la sienne |
| `bin/plugin-supply-chain`, `config/demo/plugin-repositories.*`, `docs/chaine-approvisionnement-plugins.md` (nouveaux) | | ● | | w209 |
| `tests/Unit/Bin/PluginSupplyChainTest.php` (nouveau) | | ● | | w209 |
| `bin/check-pinned-refs`, `bin/check-lock-diff`, gabarit de workflow de la démo | | ○ | | **personne** (lus par le script) |
| `project-management/sprints/sprint-33-…/releve-depart-depots.md`, `rapport-final-depots.md` (nouveaux) | | ● | | w209 |
| Les 29 dépôts de plugins (`.github/`, `composer.json` section `config`) | | ● | | w209 (règle 29, un dépôt à la fois) |
| `bin/render-page`, `bin/check-disk`, `bin/run-tests`, `bin/clean-test-caches`, `bin/measure-mobile`, `bin/proof-*`, `docs/tests-locaux.md` | ○ | ○ | ● | orchestrateur |
| `config/demo/proof.json` | | | ● | orchestrateur (`bin/proof-rebase`) |
| `.railway/` (déclaration du réglage d'activation, `preserve()`) | ● | | | w207, relu par l'orchestrateur avant le commit |
| `composer.json`, `composer.lock`, `docker/Dockerfile`, `.github/` de la démo, `vendor/` | | | | **personne** (aucun changement de dépendances ni de workflow de la démo) |
| Plugins installés dans la démo (`vendor/tmonier/*`) | | | | **personne** |

Chevauchements à surveiller : w207 et l'orchestrateur écrivent chacun une section de `docs/captures-vitrine.md` ; le réglage d'activation touche `.railway/` (variable à garder par `preserve()`). L'orchestrateur modifie `bin/check-disk`, `bin/run-tests` et `bin/render-page` (T-TECH-33-04, 05 et 08) : les workers ne les touchent pas. Ordre d'intégration proposé sur la branche : T-TECH-33-01 à 08, puis US-209 (script, relevé, rapport) et US-207 ; `bin/proof-rebase` après chaque rebase. Les PR des 29 dépôts de plugins sont indépendantes de la PR de la démo.

## Workers proposés et parallélisme (règle 24)

| Worker | Story | Domaine de fichiers | Tests lancés | Chevauchements |
|--------|-------|---------------------|--------------|----------------|
| w209 | US-209 | script et dépôts de plugins (hors de l'arbre de la démo) | légers (script sur fixtures) | aucun avec w207 |
| w207 | US-207 | en-têtes, `robots.txt`, sitemap, métadonnées, données structurées, tests existants de la table | moyens (fonctionnels, smoke, pa11y) | `docs/captures-vitrine.md` (section), `.railway/` (réglage) |

Avant de lancer les workers, l'orchestrateur relève `df -h /`, la mémoire libre et le swap (T-TECH-33-11). **Aucun test ni rendu sous 5 Go libres** : tant que le disque n'est pas libéré (T-TECH-33-03), les workers écrivent tests et code mais ne lancent rien, et le disent dans leur rapport (la story n'est alors pas « rouge prouvé », règle 27) ; **au-delà de 2 Go de swap ou sous 7 Go de disque, au plus deux workers lancent des tests en même temps**, aucune suite complète sous 6 Go. Ordre naturel : w209 d'abord (aucune porte, peu de disque : relevé, script, pilote, lots) ; w207 fait cadrage et table des invariants pendant que l'orchestrateur fait T-TECH-33-04, 05 et 08, puis tests rouges et code dès que le disque le permet.

## Règles du sprint

Reprises de [../consigne-worker.md](../consigne-worker.md) (à coller dans chaque consigne de worker) :

- Tests uniquement par `WORKER_ID=wN TEST_DB_NAME=b2b_wN TEST_CACHE_DIR=var/cache-wN bin/run-tests`, **dans l'arbre partagé** (jamais depuis un worktree au `vendor` en lien symbolique) ; une seule suite complète à la fois ; aucun test sous 5 Go, aucune suite complète sous 6 Go ; un seul cache de rendu.
- Jamais de fichier `.env*` ; aucun refus d'outil contourné ; jamais de `cp -R`, `rm -rf` ni `mv` sur un chemin issu d'une variable ou d'un glob sans garde (règle 18).
- Branche `feature/sprint-33-indexation-et-depots`, pas de checkout dans l'arbre partagé ; commits `git add <nouveaux fichiers>` puis `git commit -m "…" -- <chemins>` avec le **bon numéro de story** ; fichiers communs selon la matrice ci-dessus.
- **Règle 27 : rouge prouvé** (l'échec observé de chaque test cité dans le commit des tests rouges). **Règle 28 : table « invariants de test »** au cadrage. **Règle 29 : dépôts de plugins** (`git fetch` et branche courante contrôlés avant tout commit ; PR, fusion si la CI est verte, **aucun tag**).
- Aucune action chez un moteur de recherche ; aucun détail de sécurité dans les dépôts publics ; ton neutre ; plugins en recoupement et import ERP absents des données structurées et des descriptions.
- **Règle 26** : toute PR hors sprint a sa ligne au registre avant d'être ouverte ; un worker relit le registre avant d'écrire « j'attends ».

## Actions de Thibaut

| Tâche | Action | Durée |
|-------|--------|-------|
| T-207-03 | Confirmer la liste blanche des pages indexées et l'activation de l'indexation | 0,25 h |
| T-207-14 | Déclarer le sitemap dans Google Search Console (après le déploiement) | 0,25 h |
| T-TECH-33-03 | Libérer le disque (cible 10 Go libres) | 0,25 h |
| T-209-11 | Décider des exceptions remontées (dépôt à CI rouge préexistante, sans CI, à branche protégée), si besoin | à la demande |
