# Sprint 17 : Conversion (annonce, profil, mesure, parcours guidés et page « Intégrer »)

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 17 |
| Début | 2027-02-05 (revue et rétro du sprint 16 ; démarrage effectif le 2027-02-08) |
| Fin | 2027-02-19 |
| Durée | 10 jours ouvrés (2027-02-08 au 2027-02-19) |
| Capacité | 38 points (35 Must, 3 Should), contre 27 au sprint 16 ; les heures seront fixées par la décomposition en tâches (`/project:decompose-tasks 017`) |
| Branche | `feature/sprint-17-conversion` |

## Sprint Goal

> « Transformer l'élan de l'annonce du 2026-10-19 en visites et en échanges (profil à jour, posts de suite, suivi à J+7 et J+30, mesure par campagne, charte, calendrier, kit média) et livrer la conversion dans la démo : parcours guidés complets par persona et page « Intégrer ces plugins dans votre projet ». »

## Sprint Backlog

| ID | Titre | Points | MoSCoW | Statut |
|----|-------|--------|--------|--------|
| US-105 | Parcours guidés : moteur de visite par étapes | 8 | Must | ready-for-dev |
| US-106 | Parcours guidés : contenu par persona et écran de sortie vers le contact | 5 | Must | ready-for-dev |
| US-107 | Page « Intégrer ces plugins dans votre projet » | 5 | Must | ready-for-dev |
| US-116 | Charte éditoriale LinkedIn et messages clés | 2 | Must | ready-for-dev |
| US-120 | Kit média (1/2) : GIF courts de PunchOut, devis et commande rapide | 5 | Must | ready-for-dev |
| US-122 | Calendrier éditorial LinkedIn sur huit semaines | 3 | Must | ready-for-dev |
| US-123 | Posts LinkedIn rédigés, lot 1 : trois posts de suite à l'annonce | 3 | Must | ready-for-dev |
| US-127 | Suivi de l'annonce d'ouverture, de J+1 à J+30 | 2 | Must | ready-for-dev |
| US-129 | Profil LinkedIn : texte, bannière et section « À la une » | 2 | Must | ready-for-dev |
| US-117 | Mesure de la communication : visites par campagne sans cookie (Umami, US-143) | 3 | Should | ready-for-dev |

Total : 38 points. Les stories de communication ont été recadrées le 2026-10-20 (annonce déjà publiée : US-123 passe de 5 à 3 points, US-127 de 3 à 2, US-129 relevée de Should à Must et avancée du sprint 18, US-117 avancée du sprint 18).

## Jalon

- **Actions de la rétro du sprint 16**, prises en tâches sans points : (1) verrou de `bin/run-tests` ; (2) isolation des comptes de démo partagés ; (3) arrêt des tâches de fond ; (4) reconstruction de l'image. Détail dans [sprint-retro.md](../sprint-16-demo-credible/sprint-retro.md).
- **Premier post de suite publié au plus tard le 2026-10-26**, profil à jour avant lui (US-129, puis US-123).
- **Bilan à J+7 de l'annonce le 2026-10-26** (US-127, mesure US-117).
- **Fin de sprint** : bilan à J+7 fait, parcours guidés et page « Intégrer » en ligne.

## Dépendances

- US-105 avant US-106 (moteur puis contenu).
- US-116 avant US-122 (charte puis calendrier) ; US-122 et US-120 avant US-123 (posts) ; US-097 (sprint 16) avant US-120.
- US-129 avant US-123 (profil à jour avant le post de suite).
- US-143 (sprint 16, Umami) avant US-117 et US-127.
- US-107 et US-106 : pas de dépendance mutuelle ; fichiers à partager à surveiller dans le thème.

## Risques

| Risque | Mitigation |
|--------|------------|
| Échéance du 2026-10-26 (post de suite et bilan J+7) très en avance sur le reste du sprint | Traiter US-116, US-129, US-123 (premier post) et US-127 en premier, avant les stories de démo |
| Parcours guidés (8 + 5 pts) : moteur ambitieux, accessibilité (AAA) du guidage | Moteur minimal d'abord (YAGNI), pa11y AAA et `bin/measure-mobile` sur les pages touchées |
| Valeurs Umami / Calendly absentes en production | Paramètres vides, composants masqués ; action de Thibaut identifiée |
| Disque saturé, tests concurrents (rétro S16) | `df -h .`, `bin/clean-test-caches`, verrou de `bin/run-tests` |
| Détail exploitable ou donnée privée dans un dépôt public | Journal des échanges hors dépôt, aucun nom ni contenu de conversation |

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

- [ ] Sprint 16 : statut `done`, revue et rétrospective tenues (`sprint-review.md`, `sprint-retro.md`)
- [ ] Branche `feature/sprint-17-conversion` active ; `git fetch` et branche vérifiés
- [ ] Base de test : `bin/prepare-test-db` (voir `docs/tests-locaux.md`) ; Docker PHP 8.4
- [ ] `df -h .` : au moins 5 Go libres ; `bin/clean-test-caches` lancé
- [ ] Les 10 stories ont des critères Gherkin et une estimation (vérifié le 2026-10-20)
- [ ] Quatre actions de la rétro du sprint 16 prises en tâches sans points
- [ ] Umami opérationnel (US-143) : campagne `ouverture-demo`, source `linkedin`
- [ ] Actions de Thibaut identifiées : publications LinkedIn, mise à jour du profil, valeurs Railway
- [ ] Échéances du 2026-10-26 (premier post de suite, bilan J+7) planifiées en début de sprint

## Cérémonies

| Cérémonie | Date | Durée |
|-----------|------|-------|
| Sprint Planning Part 1 (Quoi) | 2027-02-08 | 1 h |
| Sprint Planning Part 2 (Comment) | 2027-02-08 | 1 h |
| Daily Scrum | Chaque jour ouvré | 15 min |
| Point d'étape (sacrifices) | 2027-02-12 | 30 min |
| Affinage Backlog (sprint 18) | 2027-02-15 | 1 h |
| Sprint Review | 2027-02-19 (matin) | 1 h |
| Rétrospective | 2027-02-19 (après-midi) | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
