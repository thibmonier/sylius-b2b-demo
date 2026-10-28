# Sprint 32 : Preuve technique, Architecture et script d'images (refonte des deux pages vitrine restantes, mineurs du script d'images)

> **Planifié le 2026-10-28**, après la clôture du code du sprint 31 (13 points sur 13 ; US-208 et US-204 passées à done après les contrôles de production du 2026-10-28, US-203 en review). Périmètre décidé par Thibaut le 2026-10-28 : **9 points** ([registre des décisions](./registre-decisions.md)). La décomposition en tâches est faite : voir [tasks/README.md](./tasks/README.md). Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-32`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 32 |
| Début | 2026-10-28 (planning) |
| Fin | **2026-11-10** (10 jours ouvrés) au plus tard ; clôture dès que le périmètre est vérifié |
| Capacité | **9 points** ; 41 tâches, 28 h (26,75 h pour Claude, 1,25 h d'actions de Thibaut) pour 26,9 h de capacité (9 × 2,1 h + 8 h transverses), soit 0,15 h de marge |
| Vélocité de référence | S27 13, S28 13, S29 13, S30 13, S31 13 (moyenne 13) |
| Branche | `feature/sprint-32-preuve-et-architecture` (arbre principal, déjà créée ; pas de checkout dans l'arbre partagé) |

Le périmètre (9 points) est plus petit que les cinq derniers sprints (13) : le disque est à 4,7 Go libres et le swap autour de 3,7 Go au lancement, ce qui interdit tout test et tout rendu local avant le retour au-dessus de 5 Go (6 Go pour une suite complète). Les deux refontes ont chacune une maquette à valider avant leur Twig ; US-062, sans maquette ni porte, avance dès le planning. L'ordre de sacrifice ci-dessous est **proposé** par l'orchestrateur le 2026-10-28 (registre) et reste à confirmer par Thibaut.

## Sprint Goal

> « Donner aux pages « Preuve technique » et « Architecture » la clarté et la charte de l'accueil, avec des maquettes validées avant le code, et solder les mineurs du script d'images, sans lancer de test ni de rendu tant que le disque ne le permet pas. »

## Périmètre engagé

Gherkin : au minimum 1 nominal, 2 alternatifs et 2 erreurs par story (US-205 : 1+2+3, US-206 : 1+2+3, US-062 : 1+2+2). « Fiche » = fichier `backlog/user-stories/US-XXX.md`.

| ID | Titre | Pts | MoSCoW | Statut | Sacrifice |
|----|-------|-----|--------|--------|-----------|
| [US-206](../../backlog/user-stories/US-206.md) | Refonte de la page « Architecture » dans la continuité de la home | 5 | Should | ready-for-dev (maquette validée avant tout Twig) | **protégée** (proposition) |
| [US-205](../../backlog/user-stories/US-205.md) | Refonte de la page « Preuve technique » dans la continuité de la home | 3 | Should | ready-for-dev (maquette validée avant tout Twig) | **n°2** (proposition) |
| [US-062](../../backlog/user-stories/US-062.md) | Mineurs du script d'images | 1 | Could | ready-for-dev (sans maquette) | **n°1** (proposition) |

Total engagé : **9 points**.

- **US-206** refond la page « Architecture » (`templates/demo/architecture.html.twig`, 166 lignes, six sections, un schéma SVG) à la charte de l'accueil : introduction courte, sommaire d'ancres, un visuel par section. Les invariants du schéma et de la région défilante, fixés par les tests existants d'`ArchitecturePageTest`, sont gardés. Prérequis d'US-207 (indexation).
- **US-205** refond la page « Preuve technique » (`templates/demo/proof.html.twig`, 103 lignes, un tableau de sept colonnes et la ligne de livraison) : chiffres clés en tête, faits groupés, pastilles d'état avec texte, lien de vérification pour chaque fait, état « non disponible » explicite. `ProofPageTest` reste vert et non modifié.
- **US-062** corrige les mineurs du script d'images du catalogue (`ProductImageFetcher`) : limite de pixels et `Content-Length`, code 429, image principale conservée si le manifeste change, corps en `chunked`. Aucun appel réseau ni rendu : tests unitaires avec des réponses simulées.
- **Tâches techniques** (actions 1 à 5 de la rétro du sprint 31, plus les reliquats du sprint 31) : machine et verrou des tests (T-TECH-32-01), disque libéré par Thibaut (T-TECH-32-02), build des assets frais (T-TECH-32-03), images produit locales et capture du panier (T-TECH-32-04), règle 26 (T-TECH-32-05, faite au planning), remesure de production (T-TECH-32-06, faite le 2026-10-28), contrôle connecté d'US-203 sur image reconstruite (T-TECH-32-07).

**Hors sprint** : US-207 (indexation des pages vitrine, après US-205 et US-206, sprint 33), US-209 (dépôts de plugins, sprint 33), US-128, US-103, US-121 (conditionnées), US-154.

## Décisions de Thibaut (2026-10-28)

1. Périmètre : US-205 (3), US-206 (5), US-062 (1), soit 9 points ; sprint 33 prévu : US-207 puis US-209.
2. Maquettes dans l'unique projet Claude Design (canevas G2StRQigRKD6SqNRoyHpkW) : une page par refonte, Twig 📅 jusqu'au GO de Thibaut noté au registre.
3. Clôture au planning : US-208 et US-204 passent à done (contrôles de production du 2026-10-28, PR #126) ; US-203 reste en review (contrôle connecté bloqué par le disque).
4. Disque et machine : aucun test ni rendu local avant le retour au-dessus de 5 Go (6 Go pour une suite complète), au plus deux workers qui testent en même temps.

L'ordre de sacrifice (US-062, puis US-205 ; US-206 protégée) est une **proposition de l'orchestrateur**, pas encore une décision de Thibaut.

## Estimation du sprint

La fiche annonce 3 + 5 + 1 points. La décomposition donne 7 h (US-205, dont 0,5 h de Thibaut), 11 h (US-206, dont 0,5 h de Thibaut) et 2 h (US-062), plus 8 h de transverses (dont 0,25 h de Thibaut), soit **26,75 h de Claude pour 26,9 h : les 9 points tiennent, avec 0,15 h de marge**. Franchement : la marge est quasi nulle, et le sprint dépend de deux inconnues. D'abord le disque : sans retour au-dessus de 5 Go, les tâches de test et de rendu (T-205-08, T-206-09, captures, suite complète) restent en attente et le sprint ne peut pas se terminer. Ensuite le canal B2C : la page « Architecture » est aussi servie sur le canal B2C (mot d'ordre d'US-170 : utilitaires Bootstrap seulement) ; le cadrage (T-206-01) doit vérifier que les classes `b2b-home-*` y sont chargées, sinon la story demande plus de styles. La maquette d'« Architecture » est la plus lourde (2 h pour six sections et un schéma). Deux économies déjà prises : l'état « avant » sans capture locale (0,25 h par story) et un avertissement de build des assets simple (0,5 h).

## Politique Sylius Plus

Aucun plugin qui recouvre Sylius Plus n'est promu. Ce sprint ne modifie aucun plugin et ne touche ni le texte ni les données de la page « Plugins ». Les deux pages refondues gardent leurs faits actuels, en ton neutre, sans mise en avant ni comparaison.

## Checklist de démarrage (2026-10-28)

### Avant le Sprint Planning

- [x] Sprint précédent terminé : code du sprint 31 fusionné (PR #125, `18c9038`) ; contrôles de production du 2026-10-28 consignés (PR #126) ; revue et rétro écrites ; US-208 et US-204 en done, US-203 en review (contrôle connecté bloqué par le disque)
- [x] Actions de la rétrospective du sprint 31 reprises :
  - action 1 : T-TECH-32-01 (`bin/check-disk` annonce les runs autorisés, `bin/run-tests` en file d'attente) et T-TECH-32-02 (Thibaut libère le disque) ;
  - action 2 : T-TECH-32-03 (avertissement quand `public/build` est périmé) ;
  - action 3 : T-TECH-32-04 (images produit locales, capture du panier) ;
  - action 4 : ✅ règle 26 écrite au planning (T-TECH-32-05) ;
  - action 5 : ✅ faite le 2026-10-28 (T-TECH-32-06, [vérification de production](../sprint-31-chaine-approvisionnement-et-comptes/verification-production-2026-10-28.md)).
- [x] Backlog priorisé : périmètre décidé par Thibaut le 2026-10-28
- [x] Stories estimées et « Ready » : voir la Definition of Ready ci-dessous
- [x] Capacité calculée : 26,9 h pour 26,75 h de charge de Claude
- [x] Fiches US-205, US-206 et US-062 à jour (en-tête « Sprint 32 »)
- [x] Branche `feature/sprint-32-preuve-et-architecture` créée (arbre principal)
- [ ] Disque et mémoire : `df -h .`, `bin/check-disk`, mémoire libre et swap relevés avant le premier lot (T-TECH-32-01, T-TECH-32-09) ; **aucun test ni rendu avant 5 Go libres**

### Après le Sprint Planning

- [x] Sprint backlog visible : [task-board.md](./task-board.md), [tasks/README.md](./tasks/README.md)
- [x] Dépendances : [sprint-dependencies.md](./sprint-dependencies.md)
- [x] Consigne de worker et checklist du sprint : [consigne-worker.md](./consigne-worker.md), [checklist-integration.md](./checklist-integration.md)
- [ ] GO de la maquette « Preuve technique » (T-205-04) et de la maquette « Architecture » (T-206-04) ; disque libéré (T-TECH-32-02)

## Definition of Ready (contrôle du 2026-10-28)

| US | Fiche | Gherkin | Prérequis | État |
|----|-------|---------|-----------|------|
| US-206 | oui | oui (1+2+3) | classes `b2b-home-*` de la home et partial `_plugins.scss` (US-204) comme référence de style ; `ArchitecturePageTest` (11 méthodes de test) vert ; canal B2C à vérifier au cadrage ; GO de la maquette avant tout Twig | prête (porte : maquette, puis GO) |
| US-205 | oui | oui (1+2+3) | idem ; `ProofPageTest` (10 méthodes de test) vert ; données de `config/demo/proof.json` lues par `ProofReader` ; GO de la maquette avant tout Twig | prête (porte : maquette, puis GO) |
| US-062 | oui (écrite au sprint 22, détail à confirmer au cadrage) | oui (1+2+2) | `ProductImageFetcher` et `ProductImageFetcherTest` existants ; aucun accès réseau requis (réponses simulées) | prête (aucune porte, démarre au planning) |

## Definition of Done (rappel)

- [ ] Chaque scénario Gherkin relié à un test ou à une preuve dans le fichier de tâches
- [ ] Stories de code : commit de tests rouges cité dans la PR
- [ ] **Maquettes « Preuve technique » et « Architecture » validées par Thibaut, GO noté au registre, avant le premier Twig concerné**
- [ ] US-205 et US-206 : pa11y WCAG2AAA FR et EN, `bin/measure-mobile` à 320 et 375 px, tests existants de la page verts et non modifiés. US-062 : chaque scénario vérifié rouge avant le correctif
- [ ] **Captures réelles** par `bin/render-page` (vignettes comprises) quand le disque le permet, sinon captures de production relues, dites dans la PR ; **capture à 1280 px de la home connectée et de chaque page touchée relue par l'orchestrateur avant la PR** (règle 23)
- [ ] **Tout état écrit hors base** (règle 16) : où il vit, ce qui le recrée au démarrage, vérifié dans un conteneur neuf avant le merge (aucun attendu ce sprint, à confirmer au cadrage)
- [ ] **Mesures de production consignées le jour du déploiement** pour ce qui est public (pages « Preuve technique » et « Architecture »)
- [ ] PHPStan max sur **tout le dépôt**, tests compris, ECS et Rector verts, rejoués avant le push ; aucune nouvelle entrée `ignoreErrors` ; aucun compteur écrit en dur ; **suite complète locale avant le push ou attente explicite dite dans la PR** (T-TECH-32-11)
- [ ] Preuve publiée par `bin/proof-rebase` ; PR fusionnées en série, aucune avec une question ouverte à Thibaut
- [ ] Aucun détail de sécurité dans le dépôt public (la section « sécurité » d'« Architecture » reste générale) ; ton neutre, aucune comparaison avec Sylius Plus ; **aucun plugin modifié**
- [ ] Texte public relu contre sa source ; documentation à jour ; numéro de story des commits relu ; aucun commit embarquant le travail d'un autre worker (règle 21)
- [ ] CI verte (« Quality and smoke tests », « Docker image », « Dependency audit », zizmor), run terminé avant le merge ; `main` protégée (PR requise)
- [ ] Aucun identifiant saisi en production

## Actions de Thibaut

- Valider la maquette de la page « Preuve technique » (T-205-04) et celle de la page « Architecture » (T-206-04), ou demander des retouches.
- Libérer le disque (cible 10 Go libres, commande en chemins absolus précédée d'un `du -sh` fournie par l'orchestrateur) avant le lancement des tests (T-TECH-32-02).
- Confirmer l'ordre de sacrifice proposé (US-062, puis US-205 ; US-206 protégée) au registre.

## Risques

| Risque | Probabilité | Impact | Mitigation |
|--------|-------------|--------|------------|
| Disque sous 5 Go : aucun test ni rendu, sprint qui ne peut pas se terminer | Élevée | Élevé | T-TECH-32-02 (Thibaut libère le disque) ; tâches de test 📅 « disque » ; pas de PR sans suite ciblée et complète, ou attente explicite dite dans la PR ; ordre de sacrifice |
| Tests existants cassés par la refonte (sélecteurs `data-proof-*`, `data-architecture-*`, SVG, région défilante) | Moyenne | Moyen | Invariants listés au cadrage (T-205-01, T-206-01) ; les tests existants ne sont pas modifiés ; tests rouges ajoutés avant le Twig |
| Page « Architecture » servie aussi sur le canal B2C : styles `b2b-home-*` absents | Moyenne | Moyen | Vérifié au cadrage (T-206-01) ; si absents, la maquette et le partial prévoient un chargement propre à la page |
| Maquette « Architecture » retouchée plusieurs fois (six sections, schéma) : Twig 📅 trop longtemps | Moyenne | Moyen | Maquette faite en premier ; US-062 et les transverses avancent pendant l'attente ; US-205 indépendante |
| Schéma SVG réhabillé qui viole les invariants (viewBox, taille de police, couleurs en `currentColor`) | Faible | Moyen | Les quatre tests du schéma restent verts et non modifiés ; nouveau style par classes CSS sans couleur en dur |
| Fait « non disponible » rendu comme un bloc vide, ou valeur estimée | Faible | Moyen | Scénario d'erreur dédié (US-205) ; aucune valeur écrite en dur ; `testAFigureWithoutSourceIsNotDisplayed` non modifié |
| Fuite d'un détail de sécurité dans la page « Architecture » ou la PR | Faible | Élevé | Section « sécurité » inchangée sur le fond ; `testTheSecuritySectionStaysGeneral` non modifié ; revue dédiée (T-206-12) |
| Correctif du script d'images qui change le comportement du catalogue | Faible | Moyen | Réponses simulées uniquement ; `ProductImageFetcherTest` existant non modifié ; `bin/fetch-product-images` non lancé |
| Parallélisme sur une machine saturée (disque, swap) | Moyenne | Moyen | Règle 24 ; `bin/check-disk` ; aucune suite complète sous 6 Go |

## Cérémonies

Calées sur le travail réel : la revue et la rétro se tiennent dès que le périmètre est vérifié, au plus tard le 2026-11-10. Les fichiers `sprint-review.md` et `sprint-retro.md` sont créés à la clôture (T-TECH-32-13), pas au planning.

| Cérémonie | Quand | Durée |
|-----------|-------|-------|
| Sprint Planning (Parts 1 et 2) | 2026-10-28 (périmètre décidé par Thibaut, décomposition faite) | fait |
| Point d'étape | Après les deux maquettes (T-205-03 et T-206-03) et les GO | 30 min |
| Sprint Review | Dès le périmètre vérifié, au plus tard le 2026-11-10 | 1 h |
| Rétrospective | Le même jour que la revue | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
