# Tâches : Sprint 023 (garde-fous et cadrages)

> Sprint Planning Part 2 : décomposition faite au lancement du sprint, le 2026-10-23, sur la branche `feature/sprint-23-garde-fous`. Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-23`).
>
> Liens : [objectif](../sprint-goal.md), [tableau](../task-board.md), [dépendances](../sprint-dependencies.md), [tâches transverses](./technical-tasks.md), [rétro du sprint 22](../../sprint-22-finalisation/sprint-retro.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Sacrifice | Statut |
|----|-------|--------|--------|--------|-----------|--------|
| [US-168](./US-168-tasks.md) | Contrôle de retard de la preuve publiée | 1 | 6 | 3 h | non sacrifiable | 🔲 |
| [US-166](./US-166-tasks.md) | Méthodes longues découpées et suppression des `usleep` des tests | 3 | 8 | 7,25 h | non sacrifiable | 🔲 |
| [US-169](./US-169-tasks.md) | Spike : passage de la démo à PHPUnit 13 | 2 | 6 | 4 h | non sacrifiable | 🔲 |
| [US-132](./US-132-tasks.md) | Spike P7 : facture électronique France et e-reporting | 3 | 6 | 5,5 h | n°2 | 🔲 |
| [US-130](./US-130-tasks.md) | Spike P5 : connecteur ERP de référence | 3 | 6 | 5,25 h | n°1 | 🔲 |
| [Transverse](./technical-tasks.md) | Ressources, contrôles avant push et avant done, statuts, revue et rétro (sans points) | 0 | 7 | 4 h | non sacrifiable | 🔲 |

**Total décomposé : 12 points, 39 tâches, 29 h**, dont 1 h d'actions de Thibaut (espace disque, trois décisions de spike) : **28 h pour Claude**, orchestrateur compris.

## Capacité

Méthode des sprints précédents : 2,1 h par point + 8 h de tâches transverses = 12 × 2,1 + 8 = **33,2 h**. La charge de Claude (28 h) laisse **5,2 h de marge** : aucun sacrifice n'est prévu.

| # | Sacrifice | Heures Claude | Charge restante | Conséquence |
|---|-----------|---------------|-----------------|-------------|
| 0 | (charge décomposée) | n/a | 28 h | Marge de 5,2 h |
| 1 | US-130 entière (connecteur ERP) | 5 h | 23 h | Pas d'ADR ERP ; 9 points |
| 2 | US-132 entière (facture électronique) | 5,25 h | 17,75 h | Pas de note ni d'ADR ; 6 points |

Si le transcript T-103-03 arrive, US-103 (3 points) et US-121 (5 points) peuvent entrer après accord de Thibaut. Il faut alors les décomposer, puis sacrifier dans l'ordre ci-dessus si la charge dépasse la capacité.

## Répartition par type

| Type | Tâches | Heures |
|------|--------|--------|
| [DOC] | 14 | 12,5 h |
| [TEST] | 7 | 6,5 h |
| [BE] | 4 | 4 h |
| [OPS] | 6 | 3 h |
| [REV] | 8 | 3 h |
| [DB] / [FE-WEB] / [FE-MOB] | 0 | 0 h |
| **Total** | **39** | **29 h** |

## Fichiers principaux et chevauchements entre stories

`●` = fichier modifié, `○` = lu seulement.

| Fichier ou zone | US-168 | US-166 | US-169 | US-132 | US-130 |
|-----------------|:------:|:------:|:------:|:------:|:------:|
| `.github/workflows/ci.yaml` | ● | ● | | | |
| `bin/proof-refresh`, `src/Demo/Proof/Refresh/` | ● | | | | |
| `src/Demo/DataFixtures/`, `src/Form/Extension/`, `src/Demo/Infrastructure/Csv/`, `src/Demo/Command/` | | ● | | | |
| `tests/` | ● | ● | ○ (branche jetable) | | |
| `composer.json`, `composer.lock`, `phpstan.neon` | | | ○ (branche jetable) | | |
| `docs/adr/`, `docs/adr/index.md` | | | | ● (0015) | ● (0016) |
| `docs/tests-locaux.md` | ● | ○ | | | |
| Dépôts de plugins (lecture seule) | | | | ○ facturx | ○ webhook-outbox |

**Conflits à prévoir** :

1. **`.github/workflows/ci.yaml` : US-168 et US-166.** Séquencer US-168 puis US-166, ou mettre la garde d'US-166 dans un test plutôt que dans la CI.
2. **`docs/adr/index.md` : US-132 et US-130.** Ajouts disjoints, conflit trivial ; numéros 0015 et 0016 réservés dans cet ordre.
3. **US-169** ne merge rien dans la démo pendant le sprint : sa branche d'essai est jetée après le rapport.
4. **`.bmad/sprint-status.yaml`** : un seul écrivain, l'orchestrateur.

**Parallélisme possible** (disque limité : un seul serveur lourd à la fois) :
- voie A : US-168 puis US-166 (code de la démo) ;
- voie B : US-132 puis US-130 (documentation seule, sans serveur) ;
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
| T-TECH-23-01b | Libérer de l'espace disque (au moins 5 Go) | 0,25 h |
| T-169-05 | Décider de PHPUnit 13 après lecture du rapport | 0,25 h |
| T-132-06 | Relire la note sur la facture électronique et décider de la suite | 0,25 h |
| T-130-06 | Relire l'ADR du connecteur ERP et décider de la suite | 0,25 h |

Notes :
- **Aucun tag, release ni push sans GO de Thibaut** ; commit, push et PR de la démo sur demande ou GO permanent (« fusionner les PR dès qu'elles sont vertes »).
- **Railway** : aucune commande par Claude ; vérification en production en lecture seule.
- **Politique** : ton neutre, aucune comparaison avec une offre commerciale concurrente ; aucun détail de sécurité dans le dépôt public.

## Consigne de disque et de tests des workers

1. `df -h .`, `bin/check-disk` et mémoire libre avant chaque lot (3,4 Go libres au lancement : `bin/run-tests` refuse sous 5 Go, la CI fait foi en attendant).
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
| US-132 | Dépôt `../sylius-facturx-plugin` : `git fetch`, lecture de `main` | T-132-02 |
| US-130 | Dépôt `../sylius-webhook-outbox-plugin` : `git fetch`, lecture de `main` | T-130-01 |
