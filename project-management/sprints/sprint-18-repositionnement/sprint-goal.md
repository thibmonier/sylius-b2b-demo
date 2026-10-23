# Sprint 18 : Repositionnement (recherche d'emploi, PHP 8.5, dettes du sprint 17)

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 18 |
| Début | 2027-02-19 (revue et rétro du sprint 17 ; démarrage effectif le 2027-02-22) |
| Fin | 2027-03-05 |
| Durée | 10 jours ouvrés (2027-02-22 au 2027-03-05) |
| Capacité | 36 points (22 Must, 14 Should), contre 38 au sprint 17 ; les heures seront fixées par la décomposition en tâches (`/project:decompose-tasks 018`) |
| Branche | `feature/sprint-18-repositionnement` |

Périmètre validé par Thibaut le 2026-10-21 (section « Sprint 18 » de la [feuille de route](../../backlog/roadmap-ouverture-et-communication.md)).

## Sprint Goal

> « Aligner la démo, les plugins et le contenu LinkedIn sur la recherche d'emploi de Thibaut (appel à l'action vers le profil, guide technique, preuve de qualité), passer la démo en PHP 8.5 après la faille critique du 2026-10-21 et solder les dettes et les actions de rétro du sprint 17. »

## Sprint Backlog

| ID | Titre | Points | MoSCoW | Statut |
|----|-------|--------|--------|--------|
| US-158 | Repositionnement de la démo : profil LinkedIn, libellés neutres, guide technique | 3 | Must | in-progress |
| US-147 | Démo en PHP 8.5 (image, CI et plateforme Composer) | 3 | Must | ready-for-dev |
| US-148 | Plugins : limiteurs de débit typés `RateLimiterFactoryInterface` (Symfony 7.4 et 8 ; Symfony 6.4 abandonné) | 3 | Must | ready-for-dev |
| US-160 | Dettes du sprint 17 : verrou atomique, GET sans écriture de session, menu compte nommé | 5 | Must | ready-for-dev |
| US-161 | Actions de la rétrospective du sprint 17 (disque, isolation, vérification croisée, attente bornée, prérequis) | 5 | Must | ready-for-dev |
| US-159 | Contenu LinkedIn réaligné sur la recherche d'emploi : calendrier et posts de suite | 3 | Must | ready-for-dev |
| US-124 | Posts LinkedIn, lot 2 : semaines 3 à 8, quatre piliers | 5 | Should | ready-for-dev |
| US-111 | Bloc de preuve technique : qualité, compatibilité, versions | 3 | Should | ready-for-dev |
| US-103 | Assistants IA : page utile avec transcript d'une vraie conversation | 3 | Should | ready-for-dev |
| US-068 | Liens vers la démo et le profil de l'auteur dans les 24 README | 3 | Should | ready-for-dev |

Total : 36 points (22 Must, 14 Should). US-158 est déjà en cours de réalisation (code non commité sur la branche).

**Ordre de sacrifice** : US-068, US-103, US-111, puis la seconde tranche d'US-124. Les Must ne se sacrifient pas ; US-147 passe en tête si la veille programmée ne l'a pas livrée.

## Jalons

- **Faille critique PHP (CVSS 9.2, publiée le 2026-10-21)** : US-147 est prioritaire et peut être livrée avant le sprint par la veille programmée dès que l'image FrankenPHP PHP 8.5 corrigée est publiée.
- **Premier post de suite publié au plus tard le 2026-10-26**, profil v2 appliqué avant (US-159).
- **Bilan J+30 de l'annonce le 2026-11-18** (tâche d'US-127).
- **GO unique de merge des 24 PR de README** par Thibaut (US-068).
- **Fin de sprint** : démo en PHP 8.5, dettes et actions de rétro du sprint 17 soldées, calendrier et posts alignés.

## Dépendances

- US-158 avant US-111 (libellés et CTA neutres avant le bloc de preuve) et avant US-068 (URL du profil, `DEMO_PROFILE_URL`).
- US-159 avant US-124 (calendrier réaligné avant le lot 2).
- US-147 avant la CI et le déploiement du reste ; US-148 indépendante (dépôts de plugins).
- US-161 (disque, verrou, isolation) à traiter tôt : elle conditionne la fiabilité des tests des autres stories.
- US-103 avant US-121 (sprint 19).
- Entrée requise de Thibaut : URL de son profil LinkedIn (`DEMO_PROFILE_URL`).

## Risques

| Risque | Mitigation |
|--------|------------|
| Disque de la machine à ~4,5 Go libres (risque majeur, rétro S17) | `df -h .` en début et milieu de journée, `bin/clean-test-caches`, traiter US-161 en premier, un seul build d'image à la fois |
| Passage PHP 8.5 : extensions ou dépendances incompatibles, image corrigée non encore publiée | Veille programmée, version corrigée seulement ; PHPStan et suite complète avant bascule ; retour arrière par tag d'image |
| Échéance du 2026-10-26 (post de suite) très en avance sur le sprint | US-159 traitée en premier, indépendamment du code |
| URL du profil LinkedIn absente | Paramètre vide, bouton masqué ; action de Thibaut identifiée |
| Code de US-158 non commité partagé avec d'autres travaux | Un seul écrivain par fichier, commit seulement sur GO |
| Détail exploitable ou donnée privée dans un dépôt public | Aucune précision sur les failles dans les dépôts publics, revue avant merge |
| US-103 : seulement 3 scénarios Gherkin | Compléter à 1 nominal + 2 alternatifs + 2 erreurs au planning |

## Contraintes

Aucune action externe sans GO : pas de push, de PR, de merge ni de tag sans confirmation. Publication sur LinkedIn et commandes Railway par Thibaut. `main` protégée : PR et contrôles requis, attendre la fin du run CI. Tests via `bin/run-tests` seulement. Seul l'orchestrateur reconstruit l'image Docker. Rapports de fin ≤ 100 mots.

## Definition of Done (rappel)

- [ ] Critères d'acceptation automatisés et verts (stories de code)
- [ ] ECS (`src tests`) et PHPStan verts
- [ ] Libellés FR et EN
- [ ] Rendu réel des pages touchées vérifié, `bin/measure-mobile`, pa11y WCAG2AAA
- [ ] Aucun secret ni détail exploitable dans le dépôt
- [ ] Documentation à jour
- [ ] CI verte (« Quality and smoke tests », « Docker image »), run terminé avant le merge

## Checklist pré-sprint

- [ ] Sprint 17 : statut `done`, revue et rétrospective tenues (`sprint-review.md`, `sprint-retro.md`)
- [ ] Branche `feature/sprint-18-repositionnement` active ; `git fetch` et branche vérifiés
- [ ] `df -h .` : au moins 5 Go libres (4,5 Go constatés) ; `bin/clean-test-caches` lancé
- [ ] Base de test : `bin/prepare-test-db` (voir `docs/tests-locaux.md`)
- [ ] Les 10 stories ont des critères Gherkin et une estimation (vérifié le 2026-10-21) ; US-103 à compléter
- [ ] Image FrankenPHP PHP 8.5 corrigée disponible (sinon veille programmée)
- [ ] URL du profil LinkedIn fournie par Thibaut (`DEMO_PROFILE_URL`)
- [ ] Échéance du 2026-10-26 (premier post de suite) planifiée en début de sprint
- [ ] Actions de la rétro du sprint 17 prises en charge par US-161

## Cérémonies

| Cérémonie | Date | Durée |
|-----------|------|-------|
| Sprint Planning Part 1 (Quoi) | 2027-02-22 | 1 h |
| Sprint Planning Part 2 (Comment) | 2027-02-22 | 1 h |
| Daily Scrum | Chaque jour ouvré | 15 min |
| Point d'étape (sacrifices) | 2027-02-26 | 30 min |
| Affinage Backlog (sprint 19) | 2027-03-01 | 1 h |
| Sprint Review | 2027-03-05 (matin) | 1 h |
| Rétrospective | 2027-03-05 (après-midi) | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
