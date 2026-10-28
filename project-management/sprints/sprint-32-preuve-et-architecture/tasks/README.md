# Tâches : Sprint 032 (preuve technique, architecture et script d'images)

> Sprint Planning Part 2 : décomposition faite au planning, le 2026-10-28 ; développement sur la branche `feature/sprint-32-preuve-et-architecture` (arbre principal, déjà créée). Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-32`).
>
> Liens : [objectif](../sprint-goal.md), [tableau](../task-board.md), [dépendances](../sprint-dependencies.md), [consigne de worker](../consigne-worker.md), [checklist](../checklist-integration.md), [registre des décisions](../registre-decisions.md), [tâches transverses](./technical-tasks.md), [rétro du sprint 31](../../sprint-31-chaine-approvisionnement-et-comptes/sprint-retro.md), [revue du sprint 31](../../sprint-31-chaine-approvisionnement-et-comptes/sprint-review.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Sacrifice (proposition) | Statut |
|----|-------|--------|--------|--------|-------------------------|--------|
| [US-206](./US-206-tasks.md) | Refonte de la page « Architecture » dans la continuité de la home | 5 | 12 | 11 h | **protégée** | 🔲 cadrage, 📅 maquette puis GO |
| [US-205](./US-205-tasks.md) | Refonte de la page « Preuve technique » dans la continuité de la home | 3 | 11 | 7 h | **n°2** | 🔲 cadrage, 📅 maquette puis GO |
| [US-062](./US-062-tasks.md) | Mineurs du script d'images | 1 | 5 | 2 h | **n°1** | 🔲 prête, sans porte |
| [Transverse](./technical-tasks.md) | Actions de la rétro du sprint 31, reliquats, contrôles, clôture (sans points) | 0 | 13 | 8 h | non sacrifiable (sauf T-TECH-32-04) | 2 ✅ |
| **Total** | | **9** | **41** | **28 h** | | |

**Total décomposé : 9 points, 41 tâches, 28 h**, dont 1,25 h d'actions de Thibaut (3 tâches : GO de la maquette « Preuve technique », GO de la maquette « Architecture », disque libéré) : **26,75 h pour Claude**, orchestrateur compris.

> **Périmètre décidé par Thibaut le 2026-10-28** (voir [../registre-decisions.md](../registre-decisions.md)) : refonte de la page « Preuve technique » (US-205), refonte de la page « Architecture » (US-206), mineurs du script d'images (US-062), plus les actions de la rétro du sprint 31 et ses reliquats. Ordre de sacrifice **proposé** par l'orchestrateur, à confirmer par Thibaut : US-062, puis US-205 ; US-206 protégée (la page la plus lourde, prérequis d'US-207). US-207 puis US-209 viennent au sprint 33 : hors sprint.

## Capacité

Méthode des sprints précédents : 2,1 h par point + 8 h de tâches transverses.

| Périmètre | Capacité | Charge de Claude | Marge |
|-----------|----------|------------------|-------|
| 9 points | 9 × 2,1 + 8 = **26,9 h** | 26,75 h | **0,15 h** (≈ 0,6 %) |

**Les 9 points tiennent, avec une marge de 0,15 h**, quasi nulle. Ce qui rend le sprint tenable : US-062 est sans maquette ni porte et avance dès le planning ; US-205 et US-206 réutilisent le style de la page « Plugins » (US-204, `_plugins.scss`) et les classes `b2b-home-*` ; l'état « avant » se passe de capture locale (0,25 h par story) ; les tâches 📅 n'attendent que deux GO et le disque. Ce qui peut le faire basculer : le disque (4,7 Go libres au lancement : sans retour au-dessus de 5 Go, les tâches de test et de rendu restent en attente) ; le canal B2C de la page « Architecture » (styles à charger) ; les retouches des maquettes, surtout celle d'« Architecture » (six sections, un schéma) ; les invariants des tests existants du schéma et du tableau, qui contraignent le Twig.

| # | Sacrifice (proposition) | Heures Claude libérées | Charge restante | Capacité du périmètre restant | Conséquence |
|---|-------------------------|------------------------|-----------------|-------------------------------|-------------|
| 0 | (charge décomposée) | n/a | 26,75 h (9 points) | 26,9 h | Marge de 0,15 h |
| 0b | T-TECH-32-04 seule (images produit locales, capture du panier, négociable) | 1 h | 25,75 h | 26,9 h | La capture du panier reste en reliquat (DoD de T-TECH-31-04) |
| 1 | US-062 entière | 2 h | 24,75 h (8 points) | 24,8 h | Les mineurs du script d'images retournent au backlog |
| 2 | US-205 entière | 6,5 h | 18,25 h (5 points) | 18,5 h | La page « Preuve technique » garde son style ; la maquette validée, si elle l'est, est conservée pour un sprint suivant |

US-206 n'est jamais sacrifiée. Une story sacrifiée au milieu de son budget est arrêtée et son état consigné, pas abandonnée en silence. L'attente d'un GO de Thibaut (T-205-04, T-206-04) ou du disque n'est **pas** un sacrifice : les tâches concernées passent 📅 et les autres avancent.

## Répartition par type

| Type | Tâches | Heures |
|------|--------|--------|
| [DB] | 0 | 0 h |
| [BE] | 1 | 0,75 h |
| [FE-WEB] | 5 | 9 h |
| [FE-MOB] | 0 | 0 h |
| [TEST] | 13 | 7 h |
| [DOC] | 9 | 4,5 h |
| [REV] | 4 | 1,5 h |
| [OPS] | 9 | 5,25 h |
| **Total** | **41** | **28 h** |

## Fichiers communs et attribution (règle 21)

`●` = fichier modifié, `○` = lu seulement. **Qui commite** : le worker indiqué ; un fichier marqué « section » n'est commité qu'après `git diff -- <fichier>` relu : il ne doit contenir que les lignes du worker, sinon il attend et prévient l'orchestrateur.

| Fichier ou zone | US-062 (w062) | US-205 (w205) | US-206 (w206) | Transverse | Qui commite |
|-----------------|:------:|:------:|:------:|:----------:|-------------|
| `src/Demo/Images/Fetch/`, `bin/fetch-product-images`, `tests/Unit/Demo/Images/`, `docs/images-produits.md` | ● | | | | w062 |
| `templates/demo/proof.html.twig`, `templates/demo/proof/_fact.html.twig`, `ProofController` | | ● | | | w205 |
| `templates/demo/architecture.html.twig`, `ArchitectureController` | | | ● | | w206 |
| `assets/shop/styles/_proof.scss`, `_architecture.scss` (nouveaux) | | ● | ● | | chacun le sien |
| `assets/shop/styles/b2b.scss` (une ligne `@import` par story) | | ● | ● | | section : chacun sa ligne |
| `assets/shop/styles/_plugins.scss`, `_home.scss` | | ○ | ○ | | **personne** (références de style) |
| `translations/proof.*`, `translations/architecture.*` | | ● (`proof`) | ● (`architecture`) | | chacun son domaine |
| `docs/captures-vitrine.md` | | ● | ● | | section : chacun la sienne |
| `docs/images/` | | ● (`proof-*`) | ● (`architecture-*`) | | chacun ses fichiers |
| `tests/` de la démo (nouveaux fichiers) | ● | ● | ● | ● | chacun les siens |
| `tests/Functional/Demo/ProofPageTest.php`, `ArchitecturePageTest.php`, `tests/Unit/Demo/Images/ProductImageFetcherTest.php` | ○ | ○ | ○ | | **personne** (non modifiés) |
| `config/demo/proof.json` | | | | ● | orchestrateur (`bin/proof-rebase`) |
| `bin/render-page`, `bin/check-disk`, `bin/run-tests`, `bin/clean-test-caches`, `bin/proof-*`, `docs/tests-locaux.md` | ○ | ○ | ○ | ● | orchestrateur |
| `composer.json`, `composer.lock`, `docker/Dockerfile`, `.github/`, `vendor/` | | | | | **personne** (aucun changement de dépendances ce sprint) |
| Plugins (`vendor/tmonier/*`) | | | | | **personne** (aucun plugin modifié) |

Chevauchements à surveiller : w205 et w206 écrivent chacun leur ligne d'import dans `b2b.scss` et leur section de `docs/captures-vitrine.md`. L'orchestrateur modifie `bin/check-disk`, `bin/run-tests` et `bin/render-page` (T-TECH-32-01, T-TECH-32-03) : les workers ne les touchent pas. Ordre d'intégration proposé sur la branche : T-TECH-32-01 à 05, puis US-062, US-205, US-206 ; `bin/proof-rebase` après chaque rebase.

## Workers proposés et parallélisme (règle 24)

| Worker | Story | Domaine de fichiers | Tests lancés | Chevauchements |
|--------|-------|---------------------|--------------|----------------|
| w062 | US-062 | fetcher et tests unitaires du script d'images | légers (unitaires) | aucun |
| w205 | US-205 | page « Preuve technique » (`templates/demo/proof*`, styles propres) | légers (`ProofPageTest` et nouveaux tests) | `b2b.scss` (une ligne), `docs/captures-vitrine.md` (section) |
| w206 | US-206 | page « Architecture » (`templates/demo/architecture*`, styles propres) | moyens (fonctionnels, pa11y) | `b2b.scss` (une ligne), `docs/captures-vitrine.md` (section) |

Avant de lancer les workers, l'orchestrateur relève `df -h /`, la mémoire libre et le swap (T-TECH-32-01, T-TECH-32-09). **Aucun test ni rendu sous 5 Go libres** : tant que le disque n'est pas libéré (T-TECH-32-02), les workers écrivent tests et code mais ne lancent rien, et le disent dans leur rapport ; **au-delà de 2 Go de swap ou sous 7 Go de disque, au plus deux workers lancent des tests en même temps**, aucune suite complète sous 6 Go. Ordre naturel : w062 d'abord (aucune porte) ; w205 et w206 font cadrage et état « avant » pendant que l'orchestrateur fait T-TECH-32-01, 03 puis les deux maquettes (T-205-03, T-206-03).

## Règles du sprint

Reprises de [../consigne-worker.md](../consigne-worker.md) (à coller dans chaque consigne de worker) :

- Tests uniquement par `WORKER_ID=wN TEST_DB_NAME=b2b_wN TEST_CACHE_DIR=var/cache-wN bin/run-tests`, **dans l'arbre partagé** (jamais depuis un worktree au `vendor` en lien symbolique) ; une seule suite complète à la fois ; aucun test sous 5 Go, aucune suite complète sous 6 Go ; un seul cache de rendu.
- Jamais de fichier `.env*` ; aucun refus d'outil contourné ; jamais de `cp -R`, `rm -rf` ni `mv` sur un chemin issu d'une variable ou d'un glob sans garde (règle 18).
- Branche `feature/sprint-32-preuve-et-architecture`, pas de checkout dans l'arbre partagé ; commits `git add <nouveaux fichiers>` puis `git commit -m "…" -- <chemins>` avec le **bon numéro de story** ; fichiers communs selon la matrice ci-dessus.
- **Aucun Twig des pages « Preuve technique » et « Architecture » avant le GO de la maquette**, noté au registre ; aucun plugin modifié ; `vendor` de l'arbre partagé jamais modifié par un worker ; aucun détail de sécurité dans le dépôt public ; ton neutre.
- **Règle 26** : toute PR hors sprint a sa ligne au registre avant d'être ouverte ; un worker relit le registre avant d'écrire « j'attends ».

## Actions de Thibaut

| Tâche | Action | Durée |
|-------|--------|-------|
| T-205-04 | Valider la maquette de la page « Preuve technique » | 0,5 h |
| T-206-04 | Valider la maquette de la page « Architecture » | 0,5 h |
| T-TECH-32-02 | Libérer le disque (cible 10 Go libres) | 0,25 h |
