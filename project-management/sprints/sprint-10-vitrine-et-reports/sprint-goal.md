# Sprint 10 : Vitrine et reports (images, Sylius Plus, webhooks, reports techniques)

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 10 |
| Début | 2026-10-30 |
| Fin | 2026-11-13 |
| Durée | 11 jours ouvrés (10 effectifs : 2026-11-11 férié) |
| Capacité | 13 points, environ 50 h (62,5 h décomposées, dont 2 h calendaires ; 12 h sacrifiables) |
| Branche | `feature/sprint-10-vitrine-et-reports` |

## Sprint Goal

> « Soigner la vitrine de la démo (images des produits, encart Sylius Plus, récepteur de webhooks) et solder les reports techniques du sprint 9. »

## Sprint Backlog

| ID | Titre | Points | Heures | Statut |
|----|-------|--------|--------|--------|
| US-029 | Récepteur de webhooks de démo (reportée du sprint 9) | 3 | 15,5 h | ready-for-dev |
| US-046 | Encart Sylius Plus sur la page Plugins | 2 | 5,5 h | ready-for-dev |
| US-047 | Images des produits de démo | 5 | 22,5 h | ready-for-dev |
| US-048 | Reports techniques du sprint 9 | 3 | 19 h | ready-for-dev |

Engagement : 13 points, 37 tâches, 62,5 h décomposées pour ≈ 50 h de capacité. Ordre de sacrifice dans [tasks/README.md](./tasks/README.md).

## Décisions de l'utilisateur (2026-10-17)

- **US-046** : texte d'après la documentation officielle de Sylius Plus, aucune promesse de compatibilité, **validation par l'utilisateur avant merge** (T-046-04 bloquante). Politique : pas de promotion des plugins qui recouvrent Sylius Plus.
- **US-047** : source Openverse (API sans clé), licence CC0 uniquement, script manuel lancé une fois, images versionnées avec manifeste, jamais d'appel à la banque au runtime ni en CI, taille du dépôt surveillée.
- **US-048** : créée à partir des actions 1, 2, 3, 4, 5 de la rétrospective du sprint 9 et des points ouverts de la revue.

## Dépendances

- US-029 dépend d'US-004, du garde sortant M5 (sprint 8) et des threads bornés (US-006, ADR-0013).
- US-046 dépend d'US-014. US-047 dépend d'US-008 et d'US-040 (M4).
- US-048 dépend d'US-041, US-044, US-006 ; les tâches Railway demandent le GO et l'accès de l'utilisateur.

## Risques

| Risque | Mitigation |
|--------|------------|
| Peu d'images CC0 pertinentes pour des produits industriels | Spike T-047-01 ; image par famille ; seconde source CC0 pour les familles vides |
| Dépôt alourdi par 210 images | Budget chiffré au spike, garde T-047-05, WebP 800 px |
| Texte Sylius Plus refusé ou à reformuler | T-046-04 bloquante, texte proposé tôt dans le sprint |
| Fixture d'images ou de commandes rallonge le reset | Profilage T-048-04 avant et après, mesure en 3 passes |
| Tâches Railway sans GO ou sans accès | Calendaires, hors chemin critique, jamais closes sur une estimation |
| Charge 121 % de la capacité | Ordre de sacrifice écrit dans tasks/README.md |
| Disque local ~3 Go | Téléchargements en répertoire temporaire, aucun rebuild Docker local |

## Contraintes

Aucun push, merge ni déploiement sans GO de l'utilisateur. Aucun commit sans demande. Chaque worker rend le bloc de rapport de fin (contrat de fin de worker).

## Definition of Done (rappel)

- [ ] Critères d'acceptation automatisés et verts
- [ ] ECS et PHPStan verts
- [ ] Libellés FR et EN si interface
- [ ] Aucun secret dans le dépôt
- [ ] Documentation à jour
- [ ] CI verte

## Cérémonies

| Cérémonie | Date | Durée |
|-----------|------|-------|
| Sprint Planning Part 1 (Quoi) | 2026-10-30 | 1 h |
| Sprint Planning Part 2 (Comment, confirmation de la décomposition) | 2026-10-30 | 1 h |
| Daily Scrum | Chaque jour | 15 min |
| Affinage Backlog | 2026-11-06 | 1 h |
| Sprint Review | 2026-11-13 (matin) | 1 h |
| Rétrospective | 2026-11-13 (après-midi) | 1 h 30 |

Échéance rétro du sprint 9 : action 2 (base de test locale) au 2026-11-06.

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
