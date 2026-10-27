# Sprint 31 : Chaîne d'approvisionnement, comptes des plugins et page Plugins (verrou et workflows durcis, pages de compte des plugins dans « Mon compte », refonte de la page « Plugins »)

> **Planifié le 2026-10-27**, après la clôture du sprint 30 (13 points sur 13). Périmètre décidé par Thibaut le 2026-10-27 : **13 points** ([registre des décisions](./registre-decisions.md)). La décomposition en tâches est faite : voir [tasks/README.md](./tasks/README.md). Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-31`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 31 |
| Début | 2026-10-27 (planning) |
| Fin | **2026-11-09** (10 jours ouvrés) au plus tard ; clôture dès que le périmètre est vérifié |
| Capacité | **13 points** ; 48 tâches, 36,25 h (35 h pour Claude, 1,25 h d'actions de Thibaut) pour 35,3 h de capacité (13 × 2,1 h + 8 h transverses), soit 0,3 h de marge après l'ajout de T-TECH-31-11 (LCP de la home, 2 h, décision de Thibaut du 2026-10-27) |
| Vélocité de référence | S26 29, S27 13, S28 13, S29 13, S30 13 (moyenne 16) |
| Branche | `feature/sprint-31-dev` (branche partagée, créée depuis `main` après la fusion du planning ; pas de checkout dans l'arbre partagé) |

Le périmètre (13 points) est égal aux quatre derniers sprints. La marge est de 6,5 % : US-208, sans maquette ni porte de Thibaut, avance dès le planning ; les devis et les assistants IA réutilisent la maquette validée d'US-199 ; deux GO de Thibaut jalonnent le sprint (planche du relevé, maquette « Plugins »). L'ordre de sacrifice ci-dessous est **décidé par Thibaut le 2026-10-27**.

## Sprint Goal

> « Fermer les portes d'entrée de la chaîne d'approvisionnement PHP de la démo (verrou, plugins Composer, workflows), et donner aux pages de compte des plugins et à la page « Plugins » la charte de « Mon compte » et de la home, avec des maquettes validées avant le code. »

## Périmètre engagé

Gherkin : au minimum 1 nominal, 2 alternatifs et 2 erreurs par story (US-208 : 1+2+2, US-203 : 1+3+3, US-204 : 1+2+3). « Fiche » = fichier `backlog/user-stories/US-XXX.md`.

| ID | Titre | Pts | MoSCoW | Statut | Sacrifice |
|----|-------|-----|--------|--------|-----------|
| [US-208](../../backlog/user-stories/US-208.md) | Chaîne d'approvisionnement PHP : politique Composer, revue du verrou et durcissement des workflows | 5 | Should | ready-for-dev (sans maquette) | **protégée** |
| [US-203](../../backlog/user-stories/US-203.md) | Pages de compte des plugins (devis, paiements à terme, assistants IA) alignées sur la refonte « Mon compte » | 5 | Should | ready-for-dev (planche du relevé validée avant son Twig) | **n°2** |
| [US-204](../../backlog/user-stories/US-204.md) | Refonte de la page « Plugins » dans la continuité de la home | 3 | Should | ready-for-dev (maquette validée avant tout Twig) | **n°1** |

Total engagé : **13 points**.

- **US-208** durcit la chaîne d'approvisionnement de la démo : liste `allow-plugins` figée par un test, `config.policy` explicite, `--no-blocking` interdit, `bin/check-lock-diff` en CI (tag réécrit, nouveau plugin Composer), wishlist hors branche `dev-*`, workflows en `permissions: {}` avec `persist-credentials: false`, job zizmor obligatoire, Composer aligné entre la CI et l'image. Contrôle « si vous avez été touché » au début ; inventaire des secrets **hors du dépôt public**. Les dépôts de plugins sont hors périmètre (US-209).
- **US-203** reprend « Mes devis » (liste et détail), « Paiements à terme » et « Assistants IA » dans le cadre de « Mon compte » par **surcharge dans la démo** (plugins intacts), avec un test de dérive des templates. Une planche de maquette pour le relevé (tableau et tranches d'ancienneté), validée avant son Twig ; page de consentement du plugin MCP : cadre B2B seul, sans menu du compte.
- **US-204** refond la page « Plugins » à la charte de l'accueil (cartes par famille, badges lisibles), contenu inchangé, plugins en recoupement présentés comme aujourd'hui. Maquette Claude Design validée avant tout Twig.
- **Tâches techniques en tête du sprint** (actions 1 à 5 de la rétro du sprint 30) : parallélisme réglé sur la machine (T-TECH-31-01), suite complète locale avant le push (T-TECH-31-02), cohérence HT et TTC (T-TECH-31-03), outils de rendu et de console (T-TECH-31-04), échanges avec les workers (T-TECH-31-05, faite au planning).

**Hors sprint** : US-205, US-206 (autres pages vitrine), US-207 (indexation, après US-204 à US-206), US-209 (dépôts de plugins, après US-208), US-128, US-103, US-121 (conditionnées), US-154. Les vérifications de production des 2026-10-26 et 2026-10-27 (sprints 28 à 30) se font **en parallèle, dans une PR séparée, hors budget** : elles ne sont pas comptées dans les 34,25 h.

## Décisions de Thibaut (2026-10-27)

1. Périmètre : US-208 (5), US-203 (5), US-204 (3), soit 13 points.
2. Ordre de sacrifice : US-204 d'abord, puis T-TECH-31-11 (LCP de la home), puis US-203 ; US-208 protégée.
3. Maquettes dans l'unique projet Claude Design (canevas G2StRQigRKD6SqNRoyHpkW) : une planche pour le relevé d'US-203 et la page « Plugins » d'US-204 ; Twig 📅 jusqu'au GO de Thibaut.
4. Page de consentement du plugin MCP : cadre B2B seul, sans menu du compte.
5. US-207 après US-204 à US-206 ; US-209 après US-208 : pas dans ce sprint.

## Estimation du sprint

La fiche annonce 5 + 5 + 3 points. La décomposition donne 9,25 h (US-208), 10,5 h (US-203) et 6,75 h (US-204) pour Claude, plus 6,5 h de transverses, soit **33 h pour 35,3 h : les 13 points tiennent, avec 2,3 h de marge**. Franchement : la marge est meilleure qu'au sprint 30, mais deux inconnues pèsent : la dérive des templates des plugins (les copies sont à relire à chaque mise à jour) et les alertes de zizmor sur le workflow existant, dont le nombre n'est pas connu avant T-208-06. La page de consentement (0,75 h) est la première réserve interne d'US-203.

## Politique Sylius Plus

Aucun plugin qui recouvre Sylius Plus n'est promu. Ce sprint ne modifie aucun plugin : les pages de compte sont reprises par surcharge dans la démo. La page « Plugins » garde la présentation neutre des plugins en recoupement, sans mise en avant ni comparaison.

## Checklist de démarrage (2026-10-27)

### Avant le Sprint Planning

- [x] Sprint précédent terminé : sprint 30 clos le 2026-10-25 (13 points sur 13, revue et rétro écrites ; vérifications de production en point ouvert, hors budget)
- [x] Actions de la rétrospective du sprint 30 reprises :
  - action 1 : T-TECH-31-01 (parallélisme réglé) et règle 24 écrite au planning ;
  - action 2 : T-TECH-31-02 (suite complète locale avant le push) ;
  - action 3 : T-TECH-31-03 (cohérence HT et TTC) ;
  - action 4 : T-TECH-31-04 (vignettes pour URL relatives, `memory_limit`), avant la première capture ;
  - action 5 : ✅ règle 25 et règle 1 en tête de la consigne (T-TECH-31-05).
- [x] Backlog priorisé : périmètre décidé par Thibaut le 2026-10-27
- [x] Stories estimées et « Ready » : voir la Definition of Ready ci-dessous
- [x] Capacité calculée : 35,3 h pour 33 h de charge de Claude
- [x] Fiches US-208, US-203 et US-204 à jour (en-tête « Sprint 31 »)
- [ ] Branche `feature/sprint-31-dev` à créer depuis `main` après la fusion du planning
- [ ] Disque et mémoire : `df -h .`, `bin/check-disk`, mémoire libre et swap relevés avant le premier lot (T-TECH-31-01, T-TECH-31-07)

### Après le Sprint Planning

- [x] Sprint backlog visible : [task-board.md](./task-board.md), [tasks/README.md](./tasks/README.md)
- [x] Dépendances : [sprint-dependencies.md](./sprint-dependencies.md)
- [x] Consigne de worker et checklist du sprint : [consigne-worker.md](./consigne-worker.md), [checklist-integration.md](./checklist-integration.md)
- [ ] GO de la planche du relevé (T-203-04) et de la maquette « Plugins » (T-204-04) ; contrôles requis de `main` (T-208-11)

## Definition of Ready (contrôle du 2026-10-27)

| US | Fiche | Gherkin | Prérequis | État |
|----|-------|---------|-----------|------|
| US-208 | oui | oui (1+2+2) | CI existante (« Quality and smoke tests », « Docker image », « Dependency audit ») ; `bin/check-pinned-refs` et Dependabot en place (US-081) ; aucun accès à des secrets requis | prête (aucune porte, démarre au planning) |
| US-203 | oui | oui (1+3+3) | US-199 livrée (hooks et `nav.html.twig` du compte) ; comptes Claire, Paul, Isabelle, Léa ; devis et relevé dans les fixtures ; page de consentement tranchée (cadre B2B seul) ; planche du relevé validée avant son Twig | prête (porte : planche, puis GO) |
| US-204 | oui | oui (1+2+3) | classes `b2b-home-*` de la home ; quatre tests existants de la page verts ; GO de la maquette avant tout Twig | prête (porte : maquette, puis GO) |

## Definition of Done (rappel)

- [ ] Chaque scénario Gherkin relié à un test ou à une preuve dans le fichier de tâches
- [ ] Stories de code : commit de tests rouges cité dans la PR
- [ ] **Planche du relevé (US-203) et maquette « Plugins » (US-204) validées par Thibaut, GO noté au registre, avant le premier Twig concerné**
- [ ] US-203 : pa11y WCAG2AAA FR et EN pour les trois comptes, `bin/measure-mobile` à 320 et 375 px, droits inchangés, B2C inchangé, test de dérive rouge sur une modification simulée. US-204 : quatre tests existants verts et non modifiés, pa11y et `bin/measure-mobile`. US-208 : chaque garde vérifiée rouge sur une modification simulée, audit + revue du verrou + zizmor en moins de 2 minutes cumulées sur une PR sans changement de dépendances
- [ ] **Captures réelles** par `bin/render-page` (vignettes comprises) ; **capture à 1280 px de la home connectée et de chaque page touchée relue par l'orchestrateur avant la PR** (règle 23)
- [ ] **Tout état écrit hors base** (règle 16) : où il vit, ce qui le recrée au démarrage, vérifié dans un conteneur neuf avant le merge (aucun attendu ce sprint, à confirmer au cadrage)
- [ ] **Mesures de production consignées le jour du déploiement** pour ce qui est public (page « Plugins ») ; pages connectées vérifiées en local et sur l'image reconstruite
- [ ] PHPStan max sur **tout le dépôt**, tests compris, ECS et Rector verts, rejoués avant le push ; aucune nouvelle entrée `ignoreErrors` ; aucun compteur écrit en dur ; **suite complète locale avant le push ou attente explicite dite dans la PR** (T-TECH-31-02)
- [ ] Preuve publiée par `bin/proof-rebase` ; PR fusionnées en série, aucune avec une question ouverte à Thibaut
- [ ] Aucun nom de secret, aucune valeur ni détail d'infrastructure ou de sécurité dans le dépôt public (US-208 en tête) ; ton neutre, aucune comparaison avec Sylius Plus ; **aucun plugin modifié**
- [ ] Texte public relu contre sa source ; documentation à jour ; numéro de story des commits relu ; aucun commit embarquant le travail d'un autre worker (règle 21)
- [ ] CI verte (« Quality and smoke tests », « Docker image », « Dependency audit », zizmor), run terminé avant le merge ; `main` protégée (PR requise)
- [ ] Aucun identifiant saisi en production

## Actions de Thibaut

- Valider la planche du relevé de paiements à terme (T-203-04) et la maquette de la page « Plugins » (T-204-04), ou demander des retouches.
- Ajouter le job zizmor aux contrôles requis de `main` une fois vert (T-208-11).

## Risques

| Risque | Probabilité | Impact | Mitigation |
|--------|-------------|--------|------------|
| Dérive des templates de plugins : une mise à jour casse silencieusement une copie surchargée | Moyenne | Moyen | Test d'empreintes du `vendor/` versionnées (T-203-05), message d'échec nommant le template, `data-test` vérifiés dans le rendu |
| Alertes zizmor nombreuses sur le workflow existant | Moyenne | Moyen | Version figée ; alertes corrigées ou justifiées une à une dans la PR ; budget de 1,25 h (T-208-06) ; US-208 protégée |
| Passage du wishlist sur une version stable : `composer.lock` change pour tous | Moyenne | Moyen | Vérifié dans un répertoire propre avec son `vendor` ; `composer install` dans l'arbre partagé seulement après la fusion ; commit figé et raison notée si la version stable n'a pas le correctif |
| Tag réécrit ou plugin Composer légitime bloqué par `bin/check-lock-diff` (faux positif sur une mise à jour Dependabot) | Moyenne | Moyen | Fixtures de verrou (mise à jour ordinaire, tag réécrit, nouveau plugin) ; message qui nomme le paquet ; revue manuelle documentée |
| Fuite d'un détail d'infrastructure ou d'un nom de secret dans le dépôt public | Faible | Élevé | Règle 12 ; inventaire des secrets hors dépôt ; T-208-09 relit dépôt, PR et messages |
| Retouches de la planche ou de la maquette : tâches Twig 📅 trop longtemps | Moyenne | Moyen | Captures sur le rendu réel (T-TECH-31-04 d'abord) ; US-208 et les transverses avancent pendant l'attente |
| Page de consentement : changement de logique par erreur | Faible | Élevé | Cadre seul ; tests de consentement existants relus ; T-203-08 négociable |
| Accès élargi par une surcharge (page visible sans droit) | Faible | Élevé | Test de refus identique à celui d'avant ; droits relevés par rôle au cadrage |
| Disque et mémoire (trois workers, un `vendor` propre pour le wishlist) | Moyenne | Moyen | Règle 24 : au plus deux workers qui lancent des tests au-delà de 2 Go de swap ou sous 7 Go ; `bin/check-disk` ; aucune suite complète sous 6 Go |
| Vérifications de production en parallèle qui gênent le sprint | Faible | Faible | PR séparée, hors budget, fichiers des sprints 28 à 30 seulement |

## Cérémonies

Calées sur le travail réel : la revue et la rétro se tiennent dès que le périmètre est vérifié, au plus tard le 2026-11-09. Les fichiers `sprint-review.md` et `sprint-retro.md` sont créés à la clôture (T-TECH-31-10), pas au planning.

| Cérémonie | Quand | Durée |
|-----------|-------|-------|
| Sprint Planning (Parts 1 et 2) | 2026-10-27 (périmètre décidé par Thibaut, décomposition faite) | fait |
| Point d'étape | Après la planche du relevé et la maquette « Plugins » (T-203-03 et T-204-03) et les GO | 30 min |
| Sprint Review | Dès le périmètre vérifié, au plus tard le 2026-11-09 | 1 h |
| Rétrospective | Le même jour que la revue | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
