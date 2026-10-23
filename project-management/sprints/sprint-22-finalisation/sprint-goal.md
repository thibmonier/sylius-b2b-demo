# Sprint 22 : Finalisation (clôture des sprints 18 à 21, suites des audits du 2026-10-22, finitions de lisibilité)

> **Plan du 2026-10-23 : 27 points** (8 Must, 15 Should, 4 Could), décidé par Thibaut après les audits du 2026-10-22 (qualité 67/100, sécurité, performance). **Sprint lancé le 2026-10-23** (commande de démarrage) ; la décomposition en tâches est faite : voir [tasks/README.md](./tasks/README.md). Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-22`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 22 |
| Début | 2026-10-26 (planning) |
| Fin | 2026-11-06 |
| Durée | 10 jours ouvrés (2026-10-26 au 2026-11-06) |
| Capacité | **27 points** : 8 Must (dont US-163, déjà faite le 2026-10-23), 15 Should, 4 Could. Vélocité de référence : S18 25, S19 25, S20 22, S21 17. **Décomposition du 2026-10-23 : 90 tâches, 77 h (74,5 h pour Claude)** contre 60,5 h de capacité ; dépassement de 14 h absorbé par les sacrifices ordonnés (voir [tasks/README.md](./tasks/README.md)) |
| Branche | `feature/sprint-22-finalisation` (créée depuis `main`, commit `95d68a5`) |

Périmètre issu de la [feuille de route](../../backlog/roadmap-ouverture-et-communication.md), du report du sprint 21 (US-154, US-088, US-098) et des actions 1 à 6 de l'[audit du 2026-10-22](../../audits/2026-10-22-post-sprint-21/audit-report.md).

## Sprint Goal

> « Finaliser la démo comme preuve vérifiable : vérifier en production et clore les sprints 18 à 21, solder les points de sécurité et de qualité des audits du 2026-10-22, puis livrer les dernières finitions de lisibilité. »

## Périmètre engagé

Gherkin : minimum 1 nominal + 2 alternatifs + 2 erreurs. « Fiche » = fichier `backlog/user-stories/US-XXX.md`.

| ID | Titre | Pts | MoSCoW | Statut | Sacrifice |
|----|-------|-----|--------|--------|-----------|
| [T-TECH-22-01](./tasks/technical-tasks.md) | Clôture des reviews : vérification en production des sprints 18 à 21 puis passage à done | 0 | Must | backlog | non sacrifiable |
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

## Checklist de démarrage (2026-10-23)

### Avant le Sprint Planning

- [x] Sprint précédent officiellement terminé : sprint 21 clos le 2026-10-22 (PR #50 et #51 mergées, revue et rétro écrites) ; ses stories restent en `review` (vérification en production = T-TECH-22-01)
- [x] Rétrospective du sprint 21 : actions en cours reprises (disque, un seul worker de plugin, jamais phpunit en direct)
- [x] Backlog priorisé par le PO (plan du 2026-10-23, décisions de Thibaut après les audits)
- [x] Stories candidates estimées et « Ready » : voir la Definition of Ready ci-dessous
- [x] Capacité calculée : 60,5 h (25 points restants × 2,1 h + 8 h transverses), charge décomposée 74,5 h pour Claude
- [x] Branche `feature/sprint-22-finalisation` créée ; `.bmad/sprint-status.yaml` : sprint 22 `in-progress`
- [ ] Planning Parts 1 et 2 avec Thibaut le 2026-10-26 (arbitrage des sacrifices si besoin)

### Après le Sprint Planning

- [x] Sprint backlog visible : [task-board.md](./task-board.md), [tasks/README.md](./tasks/README.md)
- [x] Dépendances : [sprint-dependencies.md](./sprint-dependencies.md)
- [ ] Daily Scrum et point d'étape du 2026-10-30 planifiés dans le calendrier de Thibaut
- [ ] Relevé du disque au démarrage effectif (2026-10-26) : `df -h .` et `bin/check-disk` (6,5 Go libres le 2026-10-23, `bin/run-tests` refuse sous 5 Go)

## Definition of Ready (contrôle du 2026-10-23)

| US | Fiche | Gherkin (1+2+2 min.) | Prérequis d'environnement | État |
|----|-------|----------------------|----------------------------|------|
| US-164 | oui | oui | aucun | prête (sécurité : termes généraux dans le dépôt public) |
| US-162 | oui | oui | aucun | prête |
| US-161 (solde) | oui | oui | aucun | prête, à ne conserver que si encore ouverte |
| US-084 | oui | oui | aucun | prête ; seuil de couverture à fixer sur la mesure de la CI ; Rector séquencé avec les autres branches |
| US-165 | oui | oui | aucun | prête |
| US-167 | oui | oui | aucun | prête |
| US-088 | oui | oui | aucun | prête, **à recadrer au cadrage** : l'exclusion du reset (US-087) et trois types d'événements (US-097) existent déjà ; reste à compléter et vérifier |
| US-098 | oui | oui | aucun | prête, **emplacement du gabarit à confirmer** (plugin container-fill ou surcharge dans la démo) |
| US-154 | oui | oui | dépôt `../sylius-company-insights-plugin`, GO de Thibaut | prête ; release 0.3.0 et montée de la démo après GO |
| US-062 | oui | oui | aucun | prête (script dans la démo : `bin/fetch-product-images`, `src/Demo/Images/Fetch/`) |

## Frontières de la décomposition

- Estimations de 0,25 h à 3 h, 90 tâches (77 h) ; types [DOC], [TEST], [BE], [FE-WEB], [OPS], [REV] (aucun [DB] ni [FE-MOB]).
- Tests de la démo : uniquement `WORKER_ID=wN TEST_DB_NAME=b2b_wN TEST_CACHE_DIR=var/cache-wN bin/run-tests` (refuse sous 5 Go libres) ; jamais phpunit en direct ; si le disque est insuffisant, la CI valide.

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
