# Sprint 27 : Fiche produit et ERP fictif (fiche produit refondue, vrai extrait du fichier plat, démonstration avec l'ERP fictif)

> **Planifié le 2026-10-24**, le jour de la clôture du sprint 26. Périmètre validé par Thibaut le 2026-10-24 : **13 points** (12 puis US-194, ajoutée sur demande de Thibaut). La décomposition en tâches est faite : voir [tasks/README.md](./tasks/README.md). Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-27`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 27 |
| Début | 2026-10-24 (planning) |
| Fin | au plus tard le 2026-11-21 ; clôture dès que le périmètre est vérifié |
| Capacité | **13 points** ; 45 tâches, 34,5 h (33,75 h pour Claude, 0,75 h d'actions de Thibaut) pour 35,3 h de capacité (13 × 2,1 h + 8 h transverses), soit 1,55 h de marge |
| Vélocité de référence | S23 11, S24 19, S25 17, S26 29 (moyenne 19) |
| Branche | `feature/sprint-27-dev` (branche partagée, créée depuis `main` ; pas de checkout dans l'arbre partagé) |

Le périmètre (13 points) est sous la moyenne des quatre derniers sprints (19), mais la charge en heures est tendue : les cinq actions de la rétrospective du sprint 26 pèsent 8 h de tâches transverses et une story (US-189) attend deux feux verts de Thibaut (maquette, éléments). L'ordre de sacrifice est proposé (US-188, puis US-193) et à confirmer par Thibaut.

## Sprint Goal

> « Donner à la fiche produit la même chaleur que la home (maquette validée avant le code), faire lire à la page d'échange de fichier un vrai extrait produit par le plugin d'export, et montrer le va-et-vient des commandes avec un ERP fictif, journal des échanges compris, sans aucun système réel. »

## Périmètre engagé

Gherkin : au minimum 1 nominal, 2 alternatifs et 2 erreurs par story. « Fiche » = fichier `backlog/user-stories/US-XXX.md`.

| ID | Titre | Pts | MoSCoW | Statut | Sacrifice |
|----|-------|-----|--------|--------|-----------|
| [US-189](../../backlog/user-stories/US-189.md) | Refonte de la fiche produit dans la continuité de la home | 5 | Should | ready-for-dev (maquette en premier) | non sacrifiable |
| [US-193](../../backlog/user-stories/US-193.md) | Extrait réel du fichier plat sur `/echange-fichier` | 2 | Should | ready-for-dev | **n°2 (à confirmer)** |
| [US-188](../../backlog/user-stories/US-188.md) | Démonstration avec l'ERP fictif : simulateur, journal au back-office, documentation (**périmètre réduit, sans US-187**) | 5 | Could | ready-for-dev | **n°1 (à confirmer)** |

| [US-194](../../backlog/user-stories/US-194.md) | Logo de l'auteur dans le pied de page | 1 | Should | en cours (w185) | non sacrifiable |

Total engagé : **13 points**.

- **US-189** est la demande de Thibaut dans la continuité de la home (US-186). Deux portes : la **maquette Claude Design validée par Thibaut avant tout Twig** (T-189-03, T-189-04) et la **vérification en production**, avec le LCP mobile mesuré avant et après au même protocole. Les tâches de code sont 📅 tant que le GO de la maquette n'est pas donné.
- **US-193** est la suite d'US-190 : les fixtures passent des commandes, un export tourne au chargement, la page lit le vrai fichier.
- **US-188** est la story C de l'[ADR 0017](../../../docs/adr/0017-connecteur-erp-de-reference.md). **US-187 (import CSV de prix et de stock) n'est pas au sprint** : US-188 est réduite à ce qui se démontre sans import (voir « Écart de périmètre » ci-dessous).
- Les **cinq actions de la rétrospective du sprint 26** sont des tâches transverses T-TECH-27-01 à T-TECH-27-07 (voir [tasks/technical-tasks.md](./tasks/technical-tasks.md)) ; deux d'entre elles (consigne et checklist) sont déjà écrites au planning.

**Hors sprint** : US-187 (import CSV de prix et de stock, 8 points, backlog), stories D et E des ADR 0016 et 0017, US-128 (attend ses données), US-103 et US-121 (conditionnées au transcript T-103-03), US-154, US-062.

## Écart de périmètre : US-188 sans US-187

La fiche d'US-188 dépend d'US-185 **et** d'US-187. Sans l'import, trois scénarios de la fiche (prix mis à jour, lot rejeté à l'import, lots importés) n'ont pas d'objet. Réduction retenue pour ce sprint :

| Gardé | Retiré (reste au backlog avec US-187) |
|-------|----------------------------------------|
| Export de commandes déjà livré (US-185, US-190) | Import des prix et du stock |
| Simulateur d'« ERP Démo (fictif) » qui lit le dernier CSV de commandes et dépose un **accusé de réception simulé** (CSV de statuts) | CSV de prix et de stock produit par le simulateur |
| Page de journal au back-office : lots sortants (journal d'export du plugin) et accusés entrants du simulateur, avec lignes lues, acceptées, rejetées | Lots d'import, rapport de rejet d'import |
| Documentation de la démonstration, remise à zéro nocturne | Idempotence d'import |

Le « lot rejeté » devient un **fichier de commandes volontairement invalide rejeté par le simulateur** (aucun accusé, rejet visible au journal). Estimation conservée à **5 points** (9,5 h) : la page, le simulateur et le journal restent à écrire. L'accusé de réception est un contrat de démonstration ajouté par cette réduction ; il est à valider par Thibaut (question 1 du rapport de planning).

## Checklist de démarrage (2026-10-24)

### Avant le Sprint Planning

- [x] Sprint précédent terminé : sprint 26 clos le 2026-10-24 (29 points sur 29, revue et rétro écrites)
- [x] Actions de la rétrospective du sprint 26 reprises :
  - action 1 : ✅ règle 13 de `project-management/templates/consigne-worker.md` et ligne de `checklist-integration.md` écrites au planning (T-TECH-27-01) ;
  - action 2 : ✅ règle 14 et ligne de `checklist-integration.md` écrites au planning (T-TECH-27-01) ; audit des compteurs en dur : T-TECH-27-02 ;
  - action 3 : `bin/check-disk` à 6 Go pour une suite complète et relevé du swap : T-TECH-27-03 et T-TECH-27-08, **avant le premier lot** ;
  - action 4 : investigation du 503 puis `docs/tests-locaux.md` : T-TECH-27-04 et T-TECH-27-05, **avant la maquette d'US-189** (T-189-03) ;
  - action 5 : règle 15 de la consigne (mesures consignées le jour du déploiement), garde de largeur étendue aux tables larges (T-TECH-27-06), remesure du LCP « après » de la home (T-TECH-27-07).
- [x] Backlog priorisé : périmètre validé par Thibaut le 2026-10-24
- [x] Stories estimées et « Ready » : voir la Definition of Ready ci-dessous
- [x] Capacité calculée : 35,3 h pour 33,75 h de charge de Claude
- [x] ADR 0017 mis à jour (section « Décision (2026-10-24, sprint 27) »), fiches US-188, US-189 et US-193 à jour
- [x] Branche `feature/sprint-27-dev` créée depuis `main` ; `.bmad/sprint-status.yaml` mis à jour
- [ ] **Disque : relever `df -h .` et `bin/check-disk` avant le premier lot** (6 Go pour une suite complète, 5 Go pour tout le reste) ; swap relevé (T-TECH-27-08)

### Après le Sprint Planning

- [x] Sprint backlog visible : [task-board.md](./task-board.md), [tasks/README.md](./tasks/README.md)
- [x] Dépendances : [sprint-dependencies.md](./sprint-dependencies.md)
- [x] Consigne de worker et checklist du sprint : [consigne-worker.md](./consigne-worker.md), [checklist-integration.md](./checklist-integration.md)
- [ ] 503 en mode prod local compris et documenté (T-TECH-27-04, T-TECH-27-05) avant la maquette
- [ ] Maquette de la fiche produit publiée par l'orchestrateur et validée par Thibaut (T-189-03, T-189-04)
- [ ] Éléments complémentaires (photos, textes) fournis par Thibaut ou GO sur banque libre, si la maquette en demande (T-189-05)

## Definition of Ready (contrôle du 2026-10-24)

| US | Fiche | Gherkin (1+2+2 min.) | Prérequis d'environnement | État |
|----|-------|----------------------|----------------------------|------|
| US-189 | oui | oui (1+4+3 après affinage) | maquette validée avant Twig ; polices Fraunces et Inter déjà auto-hébergées (US-186) ; rendu du noyau en mode prod local (T-TECH-27-04) ; production en lecture seule ; 6 Go libres | prête (porte : maquette, puis GO) |
| US-194 | oui | oui (1+2+2) | fichier du logo de l'auteur ; réalisée par w185 | en cours |
| US-193 | oui | oui (1+2+2) | MySQL de la démo, `bin/run-tests` ; plugin de fichier plat `^0.1` déjà installé ; 6 Go libres | prête |
| US-188 | oui (affinée, périmètre réduit) | oui (1+2+2) | US-185 et US-190 mergées (faites) ; US-193 pour les fixtures de commandes ; pas d'US-187 ; 6 Go libres | prête (après US-193 pour T-193-04) |

## Definition of Done (rappel)

- [ ] Chaque scénario Gherkin relié à un test ou à une preuve dans le fichier de tâches
- [ ] Stories de code : commit de tests rouges cité dans la PR (y compris pour le code de l'orchestrateur)
- [ ] US-189 : maquette validée par Thibaut avant le premier gabarit Twig ; rendu vérifié en production après le déploiement automatique de `main` (pa11y WCAG2AAA en FR et en EN, 360 et 1280 px, LCP mobile avant/après) ; la story reste en review jusque-là
- [ ] US-193 : `/echange-fichier` vérifiée en production (pa11y, 360 px, extrait visible). US-188 : pages publiques vérifiées en production ; la page du back-office l'est en local et sur l'image reconstruite (aucun identifiant n'est saisi en production)
- [ ] **Mesures de production consignées dans le fichier de tâches le jour du déploiement** (action 5) ; 0 tâche de contrôle en production ouverte à la clôture
- [ ] PHPStan max sur **tout le dépôt**, tests compris, ECS et Rector verts, rejoués avant le push ; aucune nouvelle entrée `ignoreErrors` ; aucun compteur écrit en dur
- [ ] PR de la démo qui ajoute ou supprime un fichier de tests : `php bin/proof-refresh` puis `php bin/proof-check`
- [ ] Aucun secret ni détail de sécurité exploitable dans un dépôt public ; ton neutre, aucune comparaison avec Sylius Plus, aucun ERP réel nommé
- [ ] Texte public relu contre sa source avant le commit ; documentation à jour
- [ ] CI verte (« Quality and smoke tests », « Docker image »), run terminé avant le merge ; `main` protégée (PR requise)
- [ ] Aucun push, tag, release, dépôt ni PR de plugin sans GO de Thibaut ; aucun identifiant saisi en production

## Actions de Thibaut

- Valider la maquette de la fiche produit (T-189-04) ; fournir les photos ou éléments qu'elle demande, ou donner le GO sur une banque libre (T-189-05).
- Confirmer l'ordre de sacrifice (US-188, puis US-193) et l'accusé de réception simulé d'US-188.
- Quitter les applications lourdes si le swap dépasse la moitié de l'espace libre (relevé avant chaque lot).

## Risques

| Risque | Probabilité | Impact | Mitigation |
|--------|-------------|--------|------------|
| Maquette de la fiche refusée ou retouchée plusieurs fois : US-189 bloquée avant Twig | Moyenne | Élevé | Maquette produite tôt, états connecté, anonyme, B2C et rupture montrés ensemble ; travaux sans Twig (mesure avant, cadrage, photos) avancés pendant l'attente ; US-193 et US-188 avancent en parallèle |
| 503 en mode prod local non résolu : captures et maquette sans rendu réel | Moyenne | Moyen | T-TECH-27-04 en premier, borné à 1 h ; à défaut, script de captures documenté et écart noté dans la revue |
| Charge à 33,75 h pour 35,3 h de capacité | Élevée | Moyen | Ordre de sacrifice proposé (US-188, US-193) ; tâches sans marge relues à mi-sprint ; aucune story ajoutée sans retrait |
| Espace libre sous 6 Go : aucune suite complète | Moyenne | Élevé | `bin/check-disk` durci (T-TECH-27-03), relevé avant et après chaque lot, nettoyage `--all` hors lots, une seule suite complète à la fois |
| Régression de LCP ou d'accessibilité sur la fiche (photos, polices) | Moyenne | Moyen | Budget d'images testé, `font-src 'self'`, mesure avant/après au même protocole, story en review jusqu'à la preuve |
| Fixtures de commandes (US-193) et remise à zéro (US-188) touchent les mêmes fichiers | Moyenne | Moyen | US-193 d'abord (T-193-04), US-188 reprend son résultat (T-188-07) ; un seul worker sur `DemoResetCommand` à la fois |
| Accusé de réception simulé pris pour un contrat d'ERP réel | Faible | Moyen | Nom « ERP Démo (fictif) » partout, statuts « simulés » à l'écran, aucun éditeur nommé, test de charte |
| Page du back-office non vérifiable en production (aucun identifiant saisi) | Certaine | Faible | Vérification locale et sur l'image reconstruite ; DoD écrite en conséquence |
| Commit qui embarque les fichiers d'un autre worker (arbre partagé) | Moyenne | Moyen | Règle 13 : `git commit -m "…" -- <chemins>`, `git show --stat` avant push |
| CI rouge après des contrôles locaux verts | Moyenne | Moyen | Règle 14 : PHPStan sur tout le dépôt, compteurs déduits du catalogue ou de la preuve |

## Cérémonies

Calées sur le travail réel : la revue et la rétro se tiennent dès que le périmètre est vérifié, au plus tard le 2026-11-21. Les fichiers `sprint-review.md` et `sprint-retro.md` sont créés à la clôture (T-TECH-27-12), pas au planning.

| Cérémonie | Quand | Durée |
|-----------|-------|-------|
| Sprint Planning (Parts 1 et 2) | 2026-10-24 (périmètre validé par Thibaut, décomposition faite) | fait |
| Point d'étape | À la validation de la maquette (T-189-04) | 30 min |
| Sprint Review | Dès le périmètre vérifié, au plus tard le 2026-11-21 | 1 h |
| Rétrospective | Le même jour que la revue | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
