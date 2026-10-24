# Sprint 26 : Vitrine et flux (refonte de la home, factures électroniques simulées, export CSV des commandes)

> **Clos le 2026-10-24** : 29 points livrés sur 29 engagés ([revue](./sprint-review.md), [rétro](./sprint-retro.md)). **Lancé le 2026-10-24**, le jour de la clôture du sprint 25. Périmètre décidé par Thibaut le 2026-10-24 : **23 points engagés**. La décomposition en tâches est faite : voir [tasks/README.md](./tasks/README.md). Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-26`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 26 |
| Début | 2026-10-24 (lancement) |
| Fin | au plus tard le 2026-11-21 ; clôture dès que le périmètre est vérifié |
| Capacité | **23 points** ; 70 tâches, 54,5 h (53 h pour Claude, 1,5 h d'actions de Thibaut) pour 56,3 h de capacité (23 × 2,1 h + 8 h transverses), soit 3,3 h de marge |
| Vélocité de référence | S23 11, S24 19, S25 17 (moyenne 15,7) ; S22 23 |
| Branche | `feature/sprint-26-vitrine-et-flux` (PR de la démo, une par story) ; branches par story dans les dépôts de plugins ; dossier local du nouveau plugin |

Le périmètre (23 points) dépasse la moyenne des trois derniers sprints (15,7) et égale le meilleur sprint récent (S22, 23). Il tient parce que six points sont des corrections de 1 point ou des stories de démo sur un socle déjà publié ; l'ordre de sacrifice est décidé (US-185, puis US-184).

## Sprint Goal

> « Donner à la démo une vitrine qui donne envie de la parcourir (home refaite, photos libres de droits, liens de présentation) et montrer deux flux de bout en bout sans système réel : la facture électronique transmise à une plateforme fictive avec ses statuts et sa relance, et l'export des commandes en fichier plat. »

## Périmètre engagé

Gherkin : au minimum 1 nominal, 2 alternatifs et 2 erreurs par story. « Fiche » = fichier `backlog/user-stories/US-XXX.md`.

| ID | Titre | Pts | MoSCoW | Statut | Sacrifice |
|----|-------|-----|--------|--------|-----------|
| [US-186](../../backlog/user-stories/US-186.md) | Refonte du design de la home | 8 | Should | done | non sacrifiable |
| [US-183](../../backlog/user-stories/US-183.md) | PA fictive de démonstration (ADR 0016, story B) | 5 | Should | done | non sacrifiable |
| [US-185](../../backlog/user-stories/US-185.md) | Contrat de fichier plat et export CSV des commandes, dépôt atomique (ADR 0017, story A) | 5 | Could | done | **n°1** |
| [US-184](../../backlog/user-stories/US-184.md) | Statuts de transmission au back-office et relance d'un envoi en erreur (ADR 0016, story C) | 3 | Should | done | **n°2** |
| [US-181](../../backlog/user-stories/US-181.md) | Plugin b2b-mcp : `symfony/lock` et routes MCP en double | 1 | Should | done | non sacrifiable |
| [US-182](../../backlog/user-stories/US-182.md) | Garde : `code` des tableaux qui se coupent | 1 | Should | done | non sacrifiable |

Total engagé au planning : **23 points**. Ajoutées en cours de sprint, sur GO ou retour de Thibaut : US-190 (2), US-191 (1), US-192 (3), soit **29 points livrés** (voir [sprint-review.md](./sprint-review.md)). US-189 (5) reste au sprint 27.

**Ordre de sacrifice** (décidé par Thibaut) : US-185, puis US-184.
- **US-186** est la demande du jour de Thibaut : une home plus design, colorée, avec un hero chaleureux et des photos qui donnent envie. Deux portes : la **maquette Claude Design validée par Thibaut avant toute intégration Twig** (T-186-05) et la **vérification en production** (la story reste en review jusque-là, LCP mobile mesuré avant et après).
- **US-183 et US-184** sont les stories B et C de l'[ADR 0016](../../../docs/adr/0016-facture-electronique-pa-pdp.md) : la PA fictive vit dans la démo, la relance demande une version 0.2.0 du plugin de transmission (tag sur GO). US-183 dépend du paquet publié sur Packagist (T-180-13 du sprint 25).
- **US-185** est la story A de l'[ADR 0017](../../../docs/adr/0017-connecteur-erp-de-reference.md), acceptée le 2026-10-24 : un nouveau plugin développé en local (nom proposé `tmonier/sylius-erp-flatfile-plugin`), dont le dépôt, le tag et la soumission à Packagist attendent le GO de Thibaut.
- **US-181 et US-182** sont les sorties du sprint 25 (installation réelle des packs, débordement de la page des packs en production).

**Hors sprint** : stories B ([US-187](../../backlog/user-stories/US-187.md)) et C ([US-188](../../backlog/user-stories/US-188.md)) de l'ADR 0017, au backlog (fiches courtes) ; stories D et E de l'ADR 0016 et de l'ADR 0017, non créées ; US-128 attend ses données (bilan J+30 du 2026-11-18, fin du calendrier éditorial le 2026-12-15) ; US-103 et US-121 restent conditionnées au transcript T-103-03 ; US-154 et US-062 sont au backlog.

## Checklist de démarrage (2026-10-24)

### Avant le Sprint Planning

- [x] Sprint précédent terminé : sprint 25 clos le 2026-10-24 (17 points sur 17, revue et rétro écrites)
- [x] Actions de la rétrospective du sprint 25 reprises :
  - action 1 : ✅ ligne ajoutée à `checklist-integration.md` (T-TECH-26-01), budget disque écrit dans US-184 et US-185, exécution à chaque lot (T-TECH-26-05) ;
  - action 2 : US-182 (garde sur les `code` des tableaux) ;
  - action 3 : ✅ ligne ajoutée à `checklist-integration.md` (T-TECH-26-02), exécution avant chaque tag (T-TECH-26-11) ;
  - action 4 : ✅ règle 12 de `consigne-worker.md` (T-TECH-26-03), appliquée à US-185.
- [x] Backlog priorisé : périmètre décidé par Thibaut le 2026-10-24
- [x] Stories estimées et « Ready » : voir la Definition of Ready ci-dessous
- [x] Capacité calculée : 56,3 h pour 53 h de charge de Claude
- [x] ADR 0016 (décision du 2026-10-24, stories B et C) et ADR 0017 (statut accepté, section « Décision (2026-10-24) ») mis à jour, index des ADR à jour
- [x] Branche `feature/sprint-26-vitrine-et-flux` créée ; `.bmad/sprint-status.yaml` mis à jour
- [ ] **Disque : 5,2 Go libres le 2026-10-24, sous le seuil de 6 Go : aucun nouveau lot avant que Thibaut ait libéré de l'espace** (candidats et commande dans [tasks/README.md](./tasks/README.md), « Actions de Thibaut ») ; `bin/run-tests` refuse sous 5 Go

### Après le Sprint Planning

- [x] Sprint backlog visible : [task-board.md](./task-board.md), [tasks/README.md](./tasks/README.md)
- [x] Dépendances : [sprint-dependencies.md](./sprint-dependencies.md)
- [ ] Maquette de la home publiée par l'orchestrateur et validée par Thibaut (T-186-04, T-186-05)
- [ ] Paquet `tmonier/sylius-einvoice-transmission-plugin` résolu sur Packagist (prérequis d'US-183)
- [ ] GO de Thibaut pour les publications hors démo (T-181-04, T-184-06, T-185-11), à demander quand le code est prêt

## Definition of Ready (contrôle du 2026-10-24)

| US | Fiche | Gherkin (1+2+2 min.) | Prérequis d'environnement | État |
|----|-------|----------------------|----------------------------|------|
| US-186 | oui | oui (1+3+4) | maquette validée avant Twig ; photos et licences ; Chromium et pa11y jetables ; production en lecture seule ; 6 Go libres | prête (porte : maquette) |
| US-183 | oui | oui (1+3+2) | paquet de transmission sur Packagist ; Docker et MySQL du projet ; 6 Go libres | prête (porte : Packagist) |
| US-185 | oui | oui (1+2+2) | nouveau dossier local sans `.env*`, MySQL de la démo, budget `vendor/` d'environ 400 Mo ; GO avant tout dépôt distant | prête |
| US-184 | oui | oui (1+2+2) | US-183 mergée ; Docker, MySQL de la démo, budget `vendor/` ; GO avant le tag | prête (après US-183) |
| US-181 | oui | oui | clone de `sylius-b2b-mcp-plugin` ; CI comme juge ; GO avant le tag | prête |
| US-182 | oui | oui | aucun prérequis lourd ; production en lecture seule | prête |

## Definition of Done (rappel)

- [ ] Chaque scénario Gherkin relié à un test ou à une preuve dans le fichier de tâches
- [ ] Stories de code : commit de tests rouges cité dans la PR (y compris pour le code de l'orchestrateur)
- [ ] US-186 et US-182 : rendu vérifié en production après le déploiement automatique de `main` (US-186 : pa11y WCAG2AAA en FR et en EN, 360 px, 1280 px, LCP mobile avant/après) ; les stories restent en review jusque-là
- [ ] Maquette de la home validée par Thibaut avant le premier gabarit Twig ; crédits de chaque photo notés (source, auteur, licence)
- [ ] PR de la démo qui ajoute ou supprime un fichier de tests : `php bin/proof-refresh` puis `php bin/proof-check`
- [ ] ECS, PHPStan max et Rector verts, rejoués avant le push ; aucune nouvelle entrée `ignoreErrors`
- [ ] Aucun secret ni détail de sécurité exploitable dans un dépôt public
- [ ] Texte public relu contre sa source avant le commit ; version visée concordante avec Packagist avant tout tag
- [ ] Documentation à jour
- [ ] CI verte (démo : « Quality and smoke tests », « Docker image » ; plugins : tous les jobs), run terminé avant le merge ; `main` protégée (PR requise)
- [ ] Aucun push, tag, release, dépôt ni PR publique hors démo sans GO de Thibaut

## Actions de Thibaut

- Libérer de l'espace disque avant le premier lot (5,2 Go libres) : voir la liste dans [tasks/README.md](./tasks/README.md).
- Valider la maquette de la home (T-186-05).
- GO de publication : tag correctif de `sylius-b2b-mcp-plugin` (T-181-04), tag `v0.2.0` du plugin de transmission (T-184-06), dépôt et tag `v0.1.0` du nouveau plugin (T-185-11).
- Soumettre à Packagist : le plugin de transmission s'il ne l'est pas (T-180-13, prérequis d'US-183) et le nouveau plugin (T-185-13).

## Risques

| Risque | Probabilité | Impact | Mitigation |
|--------|-------------|--------|------------|
| Espace disque sous 6 Go : aucun lot ne peut démarrer | Élevée (5,2 Go au planning) | Élevé | Demande à Thibaut avant le premier lot (liste de `vendor/` candidats) ; nettoyage après chaque lot ; un seul serveur lourd à la fois ; budget `vendor/` écrit pour US-184 et US-185 |
| Maquette de la home refusée ou retouchée plusieurs fois : US-186 (8 points) bloquée avant Twig | Moyenne | Élevé | Maquette produite tôt (T-186-04 dès le cadrage et les photos) ; travaux sans Twig (mesure avant, photos, crédits) avancés pendant l'attente ; point d'étape à la première validation |
| Photo sous une licence mal lue | Faible | Élevé | Licence relue page par page, fichier de crédits testé, aucune photo sans ligne ; remplacement par une autre photo si un doute demeure |
| Régression de LCP ou d'accessibilité en production (photos lourdes) | Moyenne | Moyen | Budget d'images testé (poids, dimensions, `srcset`), mesure avant/après au même protocole, story en review jusqu'à la preuve |
| Plugin de transmission absent de Packagist : US-183 et US-184 bloquées | Moyenne | Moyen | Contrôle en T-183-02 ; demande à Thibaut dès le lancement ; US-185 et US-186 avancent en parallèle |
| Publication sans GO (dépôt, tag, PR de pack) | Faible | Élevé | Tâches 📅 de Thibaut avant toute écriture distante (T-TECH-26-12) ; un GO couvre l'objet nommé seulement |
| Version publiée non concordante (tag, CHANGELOG, contrainte) | Moyenne | Moyen | Ligne de checklist (action 3 du sprint 25), contrôle avant chaque tag (T-TECH-26-11) |
| Second serveur de base ou fichier `.env*` dans le nouveau plugin | Faible | Moyen | Règle 12 de `consigne-worker.md` ; MySQL de la démo, base à son nom |
| Statuts de facturation présentés comme officiels, plateforme réelle nommée | Moyenne | Élevé | Statuts « à vérifier », bandeau « simulés », `PublicTextCharter`, revue (T-183-10) |
| Texte public inexact ou contraire à la charte (home, README, page de la PA fictive) | Moyenne | Élevé | Relecture contre la source avant le commit (T-TECH-26-11), test de charte sur la home |
| Conflit de preuve (`proof.json`) entre quatre PR de la démo | Moyenne | Faible | Merges l'un après l'autre, `proof-refresh` rejoué après chaque rebase (T-TECH-26-10) |
| Débordement d'un tableau non vu par les tests | Moyenne | Moyen | Garde d'US-182 ; valeurs réelles les plus longues dans les tests ; contrôle de largeur en production |
| Worker bloqué par un refus prévisible de `git` en worktree isolé | Faible | Moyen | Règle 10 : toute tâche avec `git` dans l'arbre principal, séquencée |

## Cérémonies

Calées sur le travail réel : la revue et la rétro se tiennent dès que le périmètre est vérifié, au plus tard le 2026-11-21. Les fichiers `sprint-review.md` et `sprint-retro.md` sont créés à la clôture (T-TECH-26-13), pas au planning.

| Cérémonie | Quand | Durée |
|-----------|-------|-------|
| Sprint Planning (Parts 1 et 2) | 2026-10-24 (périmètre décidé par Thibaut, décomposition faite) | fait |
| Point d'étape | À la validation de la maquette (T-186-05) ou au premier GO de publication | 30 min |
| Sprint Review | Dès le périmètre vérifié, au plus tard le 2026-11-21 | 1 h |
| Rétrospective | Le même jour que la revue | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
