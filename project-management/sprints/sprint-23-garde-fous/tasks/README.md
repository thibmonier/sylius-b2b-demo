# Tâches : Sprint 023 (garde-fous et cadrages)

> Sprint Planning Part 2 : décomposition faite au lancement du sprint, le 2026-10-23, sur la branche `feature/sprint-23-garde-fous`. Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-23`).
>
> Liens : [objectif](../sprint-goal.md), [tableau](../task-board.md), [dépendances](../sprint-dependencies.md), [tâches transverses](./technical-tasks.md), [rétro du sprint 22](../../sprint-22-finalisation/sprint-retro.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Sacrifice | Statut |
|----|-------|--------|--------|--------|-----------|--------|
| [US-168](./US-168-tasks.md) | Contrôle de retard de la preuve publiée | 1 | 6 | 3 h | non sacrifiable | 🔲 |
| [US-166](./US-166-tasks.md) | Méthodes longues découpées et suppression des `usleep` des tests | 3 | 8 | 7,25 h | non sacrifiable | 🔲 |
| [US-169](./US-169-tasks.md) | Spike : passage de la démo à PHPUnit 13 | 2 | 6 | 4 h | n°1 | 🔲 |
| [US-170](./US-170-tasks.md) | Page « Architecture de la démo » | 3 | 7 | 6 h | non sacrifiable | 🔲 |
| [US-173](./US-173-tasks.md) | Spike : distributions de plugins (packs clé en main) | 2 | 6 | 4 h | n°2 | 🔲 |
| [Transverse](./technical-tasks.md) | Ressources, contrôles avant push et avant done, statuts, revue et rétro (sans points) | 0 | 7 | 4 h | non sacrifiable | 🔲 |

**Total décomposé : 11 points, 40 tâches, 28,25 h**, dont 0,75 h d'actions de Thibaut (espace disque, fait ; deux décisions de spike) : **27,5 h pour Claude**, orchestrateur compris.

> **Remaniement du 2026-10-23** : US-132 et US-130 (spikes facture électronique et ERP, 6 points) sortent au profit d'US-170 (page d'architecture, 3 points) et d'US-173 (spike distributions, 2 points). Origine : critique demandée par Thibaut à un architecte Sylius et à un client B2B. US-171 (matrice de compatibilité) et US-172 (guide de surcharge) visent le sprint 24.

## Capacité

Méthode des sprints précédents : 2,1 h par point + 8 h de tâches transverses = 11 × 2,1 + 8 = **31,1 h**. La charge de Claude (27,5 h) laisse **3,6 h de marge** : aucun sacrifice n'est prévu.

| # | Sacrifice | Heures Claude | Charge restante | Conséquence |
|---|-----------|---------------|-----------------|-------------|
| 0 | (charge décomposée) | n/a | 27,5 h | Marge de 3,6 h |
| 1 | US-169 entière (spike PHPUnit 13) | 3,75 h | 23,75 h | La PR #54 reste ouverte ; 9 points |
| 2 | US-173 entière (spike distributions) | 3,75 h | 20 h | Pas d'ADR des packs ; 7 points |

Si le transcript T-103-03 arrive, US-103 (3 points) et US-121 (5 points) peuvent entrer après accord de Thibaut. Il faut alors les décomposer, puis sacrifier dans l'ordre ci-dessus si la charge dépasse la capacité.

## Répartition par type

| Type | Tâches | Heures |
|------|--------|--------|
| [TEST] | 9 | 8,25 h |
| [DOC] | 11 | 6,5 h |
| [BE] | 5 | 4,75 h |
| [OPS] | 7 | 4,25 h |
| [FE-WEB] | 2 | 2,5 h |
| [REV] | 6 | 2 h |
| [DB] / [FE-MOB] | 0 | 0 h |
| **Total** | **40** | **28,25 h** |

## Fichiers principaux et chevauchements entre stories

`●` = fichier modifié, `○` = lu seulement.

| Fichier ou zone | US-168 | US-166 | US-169 | US-170 | US-173 |
|-----------------|:------:|:------:|:------:|:------:|:------:|
| `.github/workflows/ci.yaml` | ● | ● | | | |
| `bin/proof-refresh`, `src/Demo/Proof/` | ● | | | ○ (`ProofReader`) | |
| `src/Demo/DataFixtures/`, `src/Form/Extension/`, `src/Demo/Infrastructure/Csv/`, `src/Demo/Command/` | | ● | | | |
| `src/Demo/Controller/`, `templates/demo/`, `translations/` | | | | ● | |
| `tests/` | ● | ● | ○ (branche jetable) | ● | |
| `composer.json`, `composer.lock`, `phpstan.neon` | | | ○ (branche jetable) | | |
| `docs/adr/`, `docs/adr/index.md` | | | | | ● (0015) |
| `docs/tests-locaux.md` | ● | ○ | | | |
| `docs/vitrine-plugins.md`, `composer.json` des plugins (lecture) | | | | | ○ |

**Conflits à prévoir** :

1. **`.github/workflows/ci.yaml` : US-168 et US-166.** Séquencer US-168 puis US-166, ou mettre la garde d'US-166 dans un test plutôt que dans la CI.
2. **`src/Demo/Proof/` : US-168 (modifie) et US-170 (lit `ProofReader`).** Merger US-168 avant US-170, ou ne pas toucher `ProofReader` dans US-168.
3. **US-169 et US-173** ne mergent rien dans le code de la démo : les branches et projets d'essai sont jetés après le rapport.
4. **`.bmad/sprint-status.yaml`** : un seul écrivain, l'orchestrateur.

**Parallélisme possible** (un seul serveur lourd à la fois) :
- voie A : US-168, puis US-166, puis US-170 (code de la démo) ;
- voie B : US-173 (lecture et projet jetable, sans serveur de la démo) ;
- US-169 : sur la CI seulement.

## Actions de la rétrospective du sprint 22 appliquées

| Action | Application dans ce sprint |
|--------|----------------------------|
| 1. Scénarios contrôlés avant le done | Table « Scénarios et preuves » dans chaque fichier de tâches ; T-TECH-23-04 |
| 2. TDD vérifiable malgré le squash | Tâches [TEST] rouges commitées seules et citées dans la PR (US-168, US-166) ; T-TECH-23-04 |
| 3. Budget de ressources | T-TECH-23-01 et T-TECH-23-01b |
| 4. Règle des refus | Faite (PR #68) : `project-management/templates/consigne-worker.md`, règle 8 |
| 5. Statuts des tâches | Tâche cochée dans le commit qui la livre ; T-TECH-23-05 |
| 6. Calendrier | Revue et rétro dès que le périmètre est vérifié |

## Actions de Thibaut

| ID | Action | Durée |
|----|--------|-------|
| T-TECH-23-01b | Libérer de l'espace disque (au moins 5 Go) : ✅ 5,9 Go le 2026-10-23 | 0,25 h |
| T-169-05 | Décider de PHPUnit 13 après lecture du rapport | 0,25 h |
| T-173-05 | Relire l'ADR des distributions et décider (forme, packs, stories) | 0,25 h |

Notes :
- **Aucun tag, release ni push sans GO de Thibaut** ; commit, push et PR de la démo sur demande ou GO permanent (« fusionner les PR dès qu'elles sont vertes »).
- **Railway** : aucune commande par Claude ; vérification en production en lecture seule.
- **Politique** : ton neutre, aucune comparaison avec une offre commerciale concurrente ; aucun détail de sécurité dans le dépôt public.

## Consigne de disque et de tests des workers

1. `df -h .`, `bin/check-disk` et mémoire libre avant chaque lot (5,9 Go libres le 2026-10-23 ; `bin/run-tests` refuse sous 5 Go).
2. Tests de la démo : **uniquement** `WORKER_ID=wN TEST_DB_NAME=b2b_wN TEST_CACHE_DIR=var/cache-wN bin/run-tests`. **Jamais phpunit en direct.**
3. Un seul serveur lourd à la fois (démo locale ou MySQL de test), arrêté dès la fin de la vérification.
4. ECS (`src tests`), PHPStan max et Rector rejoués sur l'arbre exact avant chaque push (T-TECH-23-02).
5. Refus d'outil : ne jamais le contourner ; le noter dans le rapport et continuer (règle 8 de `project-management/templates/consigne-worker.md`).
6. Chaque worker place le « Rapport de fin » du gabarit `consigne-worker.md` en tête de sa réponse finale.

## Prérequis d'environnement par story

| US | Prérequis | Vérifié par |
|----|-----------|-------------|
| US-168, US-166 | Démo à jour sur `feature/sprint-23-garde-fous` ; disque (`bin/check-disk`) ; conteneur MySQL du projet pour `bin/run-tests`, sinon CI | T-TECH-23-01 |
| US-169 | Branche de la PR #54 récupérée (`gh pr checkout 54` dans une branche d'essai) | T-169-01 |
| US-170 | Démo à jour ; démo locale pour le rendu réel (un seul serveur lourd à la fois) | T-TECH-23-01 |
| US-173 | `docs/vitrine-plugins.md` à jour ; projet jetable hors dépôt, supprimé après l'essai | T-173-02 |
