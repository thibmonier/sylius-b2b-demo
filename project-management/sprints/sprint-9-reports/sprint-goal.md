# Sprint 9 : Reports (container-fill et reports techniques)

> **Arbitrage du 2026-10-16** : US-029 (récepteur de webhooks, 15,5 h) est reportée au sprint 10. Ses tâches restent ci-dessous pour mémoire, hors engagement du sprint 9.

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 9 |
| Début | 2026-10-16 |
| Fin | 2026-10-30 |
| Durée | 11 jours ouvrés |
| Capacité | 14 points (prévision) |
| Branche | `feature/sprint-9-reports` |

## Sprint Goal

> « Faire vivre container-fill dans la démo et solder les reports techniques du sprint 8. »

## Sprint Backlog

| ID | Titre | Points | Statut |
|----|-------|--------|--------|
| US-006 | Spike : mode worker FrankenPHP | 2 | ready-for-dev |
| US-029 | Récepteur de webhooks de démo | 3 | reportée au sprint 10 |
| US-044 | Données de remplissage des contenants (container-fill) | 5 | ready-for-dev |
| US-041 (reports) | T-041-06 à T-041-09 (S2-R7 à R10, S4-R3) | 4 h + 3,5 h + 4 h + 2 h | à faire |
| US-041 (daté) | T-041-15 relevé de coût Railway à J+7 | 0,5 h | **pas avant le 2026-10-23**, hors chemin critique, GO utilisateur |

Total stories : 10 points. Tâches décomposées : 35 h (US-006, US-029, reports). US-044 : décomposition à faire au Planning Part 2.

## Dépendances

- US-044 dépend d'US-008, US-010, US-039 (livrées).
- T-041-06 et T-041-07 dépendent de T-041-05 ; T-041-15 de T-041-12 (sprint 8, terminé).
- T-041-09 dépend de T-041-08.

## Risques

| Risque | Mitigation |
|--------|------------|
| Disque local ~3 Go libres | Limiter les rebuilds Docker, tests ciblés, CI pour le reste |
| Spike worker mode peut exiger un rebuild d'image | Regrouper les expérimentations, un seul build |
| Fixture de démo container-fill rend le reset plus lent | Mesurer avec T-041-06 |
| Relevé de coût impossible avant le 2026-10-23 | Tâche datée, hors chemin critique |

## Contraintes

Aucun push, merge ni déploiement sans GO de l'utilisateur. Aucun commit sans demande.

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
| Sprint Planning Part 1 (Quoi) | 2026-10-16 | 1 h |
| Sprint Planning Part 2 (Comment, décomposition d'US-044) | 2026-10-16 | 1 h |
| Daily Scrum | Chaque jour | 15 min |
| Affinage Backlog | 2026-10-23 | 1 h |
| Sprint Review | 2026-10-30 (matin) | 1 h |
| Rétrospective | 2026-10-30 (après-midi) | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
