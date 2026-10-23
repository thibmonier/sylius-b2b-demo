# Sprint 22 : Finalisation (clôture des sprints 18 à 21, suites des audits du 2026-10-22, finitions de lisibilité)

> **Plan du 2026-10-23 : 27 points** (8 Must, 15 Should, 4 Could), décidé par Thibaut après les audits du 2026-10-22 (qualité 67/100, sécurité, performance). La **décomposition en tâches sera faite au lancement du sprint** (commande de démarrage), pas avant. Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-22`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 22 |
| Début | 2026-10-26 (planning) |
| Fin | 2026-11-06 |
| Durée | 10 jours ouvrés (2026-10-26 au 2026-11-06) |
| Capacité | **27 points** : 8 Must (dont US-163, déjà faite le 2026-10-23), 15 Should, 4 Could. Vélocité de référence : S18 25, S19 25, S20 22, S21 17. Heures à fixer par la décomposition |
| Branche | à créer au lancement (`feature/sprint-22-finalisation`, depuis `main`) |

Périmètre issu de la [feuille de route](../../backlog/roadmap-ouverture-et-communication.md), du report du sprint 21 (US-154, US-088, US-098) et des actions 1 à 6 de l'[audit du 2026-10-22](../../audits/2026-10-22-post-sprint-21/audit-report.md).

## Sprint Goal

> « Finaliser la démo comme preuve vérifiable : vérifier en production et clore les sprints 18 à 21, solder les points de sécurité et de qualité des audits du 2026-10-22, puis livrer les dernières finitions de lisibilité. »

## Périmètre engagé

Gherkin : minimum 1 nominal + 2 alternatifs + 2 erreurs. « Fiche » = fichier `backlog/user-stories/US-XXX.md`.

| ID | Titre | Pts | MoSCoW | Statut | Sacrifice |
|----|-------|-----|--------|--------|-----------|
| Tâche de sprint | Clôture des reviews : vérification en production des sprints 18 à 21 puis passage à done | 0 | Must | backlog | non sacrifiable |
| [US-163](../../backlog/user-stories/US-163.md) | Clôture des deux points hauts de l'audit et checklist US-079 | 2 | Must | **done** (faite le 2026-10-23, PR #57) | sans objet |
| [US-164](../../backlog/user-stories/US-164.md) | Bornes du parc d'équipements des comptes partagés et limitation de débit des routes récentes | 3 | Must | backlog | non sacrifiable |
| [US-162](../../backlog/user-stories/US-162.md) | Anomalies mineures relevées en production | 1 | Must | backlog | non sacrifiable |
| [US-161](../../backlog/user-stories/US-161.md) | Solde de la rétro du sprint 17 (si encore ouverte) | 2 | Must | in-progress | non sacrifiable |
| [US-084](../../backlog/user-stories/US-084.md) | Qualité : Rector en CI, seuil de couverture, `bin/proof-refresh` (recadrée) | 5 | Should | backlog | |
| [US-165](../../backlog/user-stories/US-165.md) | Dettes qualité de l'audit | 3 | Should | backlog | n°5 |
| [US-167](../../backlog/user-stories/US-167.md) | Performance à faible effort | 2 | Should | backlog | |
| [US-088](../../backlog/user-stories/US-088.md) | Journal d'audit lisible : reset masqué et événements métier | 3 | Should | backlog | n°4 |
| [US-098](../../backlog/user-stories/US-098.md) | Jauge de remplissage condensée sur la fiche produit | 2 | Should | backlog | n°3 |
| [US-154](../../backlog/user-stories/US-154.md) | Company-insights : conversion des devis et économies des prix dégressifs | 3 | Could | backlog | n°2 |
| [US-062](../../backlog/user-stories/US-062.md) | Mineurs du script d'images | 1 | Could | backlog | n°1 |

Total : **27 points**. **Ordre de sacrifice** : US-062, US-154, US-098, US-088, US-165 ; les Must, US-084 et US-167 ne se sacrifient pas.

**Sprint 23 (esquisse)** : US-128, US-103 et US-121 (conditionnées au transcript T-103-03), US-166, US-132, US-130. **Abandonnées le 2026-10-23** (valeur de preuve faible / sans demande) : US-094, US-100, US-095, US-102, US-131, US-133 à US-138, US-093, US-101.

## Actions de Thibaut

- GO de merge des PR de plugins en attente, tags et Packagist (parc d'équipements, purchase-approval), puis déploiement et vérification en production.
- Relectures et validations des stories en review (kit média, profil, calendrier, captures company-insights).
- Application du profil LinkedIn v2 et premier post avant le 2026-10-26 (hors développement).
- Transcript T-103-03 (conditionne US-103 et US-121 du sprint 23).

## Risques

| Risque | Probabilité | Impact | Mitigation |
|--------|-------------|--------|------------|
| Vérification en production retardée (Railway, disponibilité de Thibaut) | Moyenne | Moyen | Tâche de clôture planifiée en premier ; les stories restent en review tant qu'elle n'est pas faite |
| Disque local limité | Moyenne | Moyen | `bin/check-disk` avant chaque lot, un seul worker à la fois |
| Détail de sécurité divulgué dans le dépôt public | Faible | Élevé | Stories de sécurité formulées en termes généraux ; rapports détaillés hors dépôt |
| Capacité entamée par des entretiens éventuels | Moyenne | Moyen | Tampon : sacrifices ordonnés ci-dessus |

## Definition of Done (rappel)

- [ ] Critères d'acceptation automatisés et verts (stories de code)
- [ ] ECS et PHPStan max verts, rejoués avant le push
- [ ] Libellés FR et EN
- [ ] Rendu réel des pages touchées vérifié, `bin/measure-mobile`, pa11y WCAG2AAA
- [ ] Aucun secret ni détail exploitable dans le dépôt
- [ ] Documentation à jour
- [ ] CI verte (« Quality and smoke tests », « Docker image »), run terminé avant le merge ; `main` protégée (PR requise)

## Cérémonies

| Cérémonie | Date | Durée |
|-----------|------|-------|
| Sprint Planning Part 1 (Quoi) | 2026-10-26 | 1 h |
| Sprint Planning Part 2 (Comment, décomposition en tâches) | 2026-10-26 | 1 h |
| Daily Scrum | Chaque jour ouvré | 15 min |
| Point d'étape (sacrifices) | 2026-10-30 | 30 min |
| Affinage Backlog (sprint 23) | 2026-11-02 | 1 h |
| Sprint Review | 2026-11-06 (matin) | 1 h |
| Rétrospective | 2026-11-06 (après-midi) | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
