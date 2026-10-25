# Sprint 29 : Liste, panier et photos (refonte de la liste des produits et du panier, photos mieux cadrées et de la bonne couleur)

> **Planifié le 2026-10-25**, au lendemain de la clôture du sprint 28 (13 points sur 13). Périmètre décidé par Thibaut le 2026-10-25 : **13 points** ([registre des décisions](./registre-decisions.md)). La décomposition en tâches est faite : voir [tasks/README.md](./tasks/README.md). Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-29`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 29 |
| Début | 2026-10-25 (planning) |
| Fin | au plus tard le 2026-11-21 ; clôture dès que le périmètre est vérifié |
| Capacité | **13 points** ; 39 tâches, 35 h (33,5 h pour Claude, 1,5 h d'actions de Thibaut) pour 35,3 h de capacité (13 × 2,1 h + 8 h transverses), soit 1,8 h de marge |
| Vélocité de référence | S24 19, S25 17, S26 29, S27 13, S28 13 (moyenne 18,2) |
| Branche | `feature/sprint-29-dev` (branche partagée, créée depuis `main` ; pas de checkout dans l'arbre partagé) |

Le périmètre (13 points) est sous la moyenne (18,2) et égal aux deux derniers sprints, mais la marge est étroite (5 %) : les deux refontes (5 points chacune) sont estimées à leur capacité exacte, trois portes de Thibaut jalonnent le sprint (deux maquettes, une planche) et le disque est sous le seuil des suites complètes. L'ordre de sacrifice ci-dessous est **décidé par Thibaut le 2026-10-25**.

## Sprint Goal

> « Donner à la liste des produits et au panier la clarté de la home et de la fiche produit, avec des maquettes validées avant le code, et des photos de catalogue mieux cadrées et de la bonne couleur. »

## Périmètre engagé

Gherkin : au minimum 1 nominal, 2 alternatifs et 2 erreurs par story (US-198 : 1+3+3, US-200 : 1+3+3, US-197 : 1+2+2). « Fiche » = fichier `backlog/user-stories/US-XXX.md`.

| ID | Titre | Pts | MoSCoW | Statut | Sacrifice |
|----|-------|-----|--------|--------|-----------|
| [US-198](../../backlog/user-stories/US-198.md) | Refonte de la page de liste des produits | 5 | Should | ready-for-dev (maquette validée avant tout Twig) | **protégée** |
| [US-200](../../backlog/user-stories/US-200.md) | Refonte de la page panier | 5 | Should | ready-for-dev (maquette validée avant tout Twig) | **n°2** |
| [US-197](../../backlog/user-stories/US-197.md) | Photos du catalogue : cadrage des cartes et variantes de couleur | 3 | Should | ready-for-dev (planche et GO de Thibaut avant tout téléchargement) | **n°1** |

Total engagé : **13 points**.

- **US-198** refond la liste (catégorie et recherche) dans la charte de la home et de la fiche : cartes, filtres et tri, pagination, état vide, visiteur anonyme. Maquette Claude Design (bureau et téléphone), **validée par Thibaut avant tout Twig** ; LCP mobile mesuré avant et après.
- **US-200** refond le panier : tableau des lignes, remises de palier, récapitulatif collant, messages d'approbation, actions secondaires, panier vide. Même démarche ; **le tunnel de commande reste vert** ; page connectée vérifiée en local et sur l'image reconstruite.
- **US-197** a deux tranches : le **point focal** (cadrage des cartes et de la fiche, sans téléchargement, qui avance pendant l'attente des maquettes), puis les **variantes de couleur** et les visuels neutres, derrière la planche et le GO de Thibaut.
- **Trois tâches reportées** de la rétro du sprint 27, en tête du sprint : un seul cache de rendu et seuil disque (T-TECH-29-01), `bin/proof-rebase` (T-TECH-29-02), LCP de la home (T-TECH-29-03) ; plus les actions 1, 3 et 4 de la rétro du sprint 28 (règles 18 à 20 et registre des décisions, écrits au planning : T-TECH-29-04).

**Hors sprint** : US-199 (Mon compte) et US-201 (tableau de bord des achats), sans maquette ce sprint (décision de Thibaut) ; US-128, US-103, US-121, US-154, US-062. Les contrôles de production du sprint 28 (T-195-06, T-196-09, T-196-10, après la remise à zéro de 03:00 du 2026-10-26) restent dans les fichiers du sprint 28 et **ne sont pas comptés** dans les 35 h.

## Décisions de Thibaut (2026-10-25)

1. Périmètre : US-198, US-200 et US-197 (13 points) et les tâches techniques reportées du sprint 28 (actions 1, 2 et 5 de la rétro du sprint 27).
2. Ordre de sacrifice : US-197 d'abord, puis US-200 ; US-198 protégée.
3. Maquettes Claude Design pour la liste et le panier seulement (pas Mon compte ni le tableau de bord) ; maquette validée avant tout Twig, tâches de code 📅 jusqu'au GO.
4. US-197 : planche de photos présentée et GO explicite avant tout téléchargement ; aucune retouche ; licences relues.

## Estimation du sprint

La fiche annonce 5 + 5 + 3 points. La décomposition donne 10,5 h (US-198), 10,25 h (US-200) et 6,25 h (US-197) pour Claude, soit **33,5 h pour 35,3 h : les 13 points tiennent, avec 1,8 h de marge**. Franchement : c'est tenable parce que 13 points ont été livrés aux sprints 27 et 28 avec une marge comparable, mais la marge ne couvre pas deux retouches importantes de maquette. Inconnues : le nombre d'allers-retours sur les maquettes (la fiche produit du sprint 27 avait été resserrée après des captures réelles), la densité des blocs de plugins dans le panier (PO, approbation, devis, liste) et le disque.

## Politique Sylius Plus

Aucun plugin qui recouvre Sylius Plus n'est promu. Ce sprint ne touche aucun plugin : les blocs des plugins du panier sont repris par leurs hooks existants, sans modification. Ton neutre, aucune comparaison avec Plus.

## Checklist de démarrage (2026-10-25)

### Avant le Sprint Planning

- [x] Sprint précédent terminé : sprint 28 clos le 2026-10-25 (13 points sur 13, revue et rétro écrites ; contrôles de production en point ouvert)
- [x] Actions de la rétrospective du sprint 28 reprises :
  - action 1 : ✅ règle 18 « garde sur les commandes récursives » écrite au planning (consigne, checklist et gabarit) ;
  - action 2 : ✅ règle 19 « séquence exacte de la CI rejouée avant le push » écrite au planning (s'applique d'abord au plugin, aucun plugin ce sprint) ;
  - action 3 : ✅ [registre des décisions](./registre-decisions.md) créé, avec l'auteur de chaque décision ;
  - action 4 : ✅ règle 20 « aucune fusion avec une question ouverte » écrite au planning ;
  - action 5 : T-TECH-29-01, 02 et 03 en tête du sprint, avant les maquettes.
- [x] Backlog priorisé : périmètre décidé par Thibaut le 2026-10-25
- [x] Stories estimées et « Ready » : voir la Definition of Ready ci-dessous
- [x] Capacité calculée : 35,3 h pour 33,5 h de charge de Claude
- [x] Fiches US-197, US-198 et US-200 à jour (en-tête « Sprint 29 ») ; EPIC-12 à jour
- [ ] Branche `feature/sprint-29-dev` à créer depuis `main` ; `.bmad/sprint-status.yaml` mis à jour (fait au planning)
- [ ] **Disque : 5,5 Go libres au planning, sous le seuil de 6 Go des suites complètes** : relever `df -h .` et `bin/check-disk` avant le premier lot ; libérer de la place avant la première suite complète (T-TECH-29-06)

### Après le Sprint Planning

- [x] Sprint backlog visible : [task-board.md](./task-board.md), [tasks/README.md](./tasks/README.md)
- [x] Dépendances : [sprint-dependencies.md](./sprint-dependencies.md)
- [x] Consigne de worker et checklist du sprint : [consigne-worker.md](./consigne-worker.md), [checklist-integration.md](./checklist-integration.md)
- [ ] GO de la maquette de la liste (T-198-04) et du panier (T-200-04)
- [ ] Planche de photos présentée à Thibaut et GO de téléchargement (T-197-05)

## Definition of Ready (contrôle du 2026-10-25)

| US | Fiche | Gherkin (1+2+2 min.) | Prérequis | État |
|----|-------|----------------------|-----------|------|
| US-198 | oui | oui (1+3+3) | US-186 et US-189 mergées ; `bin/render-page` avec un seul cache (T-TECH-29-01) ; mesure d'avant en production ; GO de la maquette avant tout Twig ; 6 Go libres pour une suite complète | prête (porte : maquette, puis GO) |
| US-200 | oui | oui (1+3+3) | US-189 mergée ; fixtures avec un produit à paliers et une limite de commande pour Acme Industries (faites) ; captures connectées en local seulement ; GO de la maquette avant tout Twig ; 6 Go libres | prête (porte : maquette, puis GO) |
| US-197 | oui | oui (1+2+2) | US-196 livrée ; `bin/fetch-product-images` à sélection explicite ; tranche 2 : planche et GO de Thibaut avant téléchargement ; aucune retouche ; un seul cache de rendu | prête (tranche 1 libre, tranche 2 derrière la porte) |

## Definition of Done (rappel)

- [ ] Chaque scénario Gherkin relié à un test ou à une preuve dans le fichier de tâches
- [ ] Stories de code : commit de tests rouges cité dans la PR
- [ ] **Maquette validée par Thibaut, GO noté au registre, avant le premier gabarit Twig** (US-198, US-200) ; **GO de la planche noté avant tout téléchargement** (US-197)
- [ ] US-198 : pa11y WCAG2AAA FR et EN, 360 et 1280 px, LCP mobile avant/après en production (seuil d'US-189). US-200 : pa11y AAA et 360 px en local et sur l'image reconstruite, tunnel de commande vert. US-197 : captures avant/après, contrôle en production après la remise à zéro de 03:00
- [ ] **Tout état écrit hors base** (règle 16) : où il vit, ce qui le recrée au démarrage, vérifié dans un conteneur neuf avant le merge (aucun attendu ce sprint, à confirmer au cadrage)
- [ ] **Mesures de production consignées dans le fichier de tâches le jour du déploiement** ; 0 tâche de contrôle en production ouverte à la clôture, sauf imposée par la remise à zéro nocturne et dite à l'avance
- [ ] PHPStan max sur **tout le dépôt**, tests compris, ECS et Rector verts, rejoués avant le push ; aucune nouvelle entrée `ignoreErrors` ; aucun compteur écrit en dur
- [ ] PR de la démo qui ajoute ou supprime un fichier de tests : `bin/proof-rebase` (ou `php bin/proof-refresh` puis `php bin/proof-check`) ; PR fusionnées en série, aucune avec une question ouverte à Thibaut
- [ ] Aucun secret ni détail de sécurité exploitable dans un dépôt public ; ton neutre, aucune comparaison avec Sylius Plus
- [ ] Texte public relu contre sa source ; documentation à jour ; numéro de story des commits relu
- [ ] CI verte (« Quality and smoke tests », « Docker image »), run terminé avant le merge ; `main` protégée (PR requise)
- [ ] **Aucun téléchargement de photo avant le GO de Thibaut** ; aucun identifiant saisi en production

## Actions de Thibaut

- Valider la maquette de la liste (T-198-04) et celle du panier (T-200-04), ou demander des retouches.
- Choisir les photos sur la planche et donner le GO de téléchargement (T-197-05).
- **Libérer de l'espace disque** avant la première suite complète (5,5 Go libres, seuil de 6 Go) ; quitter les applications lourdes si le swap dépasse la moitié de l'espace libre.

## Risques

| Risque | Probabilité | Impact | Mitigation |
|--------|-------------|--------|------------|
| Marge de 1,8 h (5 %) : les deux refontes sont à leur capacité exacte | Moyenne | Élevé | Ordre de sacrifice décidé (US-197, puis US-200) ; vue liste d'US-198 et commande rapide d'US-200 négociables ; point d'étape après les deux maquettes |
| Maquettes retouchées plusieurs fois : tâches de code 📅 trop longtemps | Moyenne | Moyen | Captures sur le rendu réel du noyau (T-TECH-29-01 d'abord), jamais de HTML jetable ; US-197 tranche 1 et les transverses avancent pendant l'attente |
| Disque : 5,5 Go libres, sous le seuil de 6 Go des suites complètes | Certaine | Élevé | Thibaut libère de la place avant la première suite complète ; un seul cache de rendu (T-TECH-29-01), `bin/check-disk` avant chaque rendu, relevé avant et après chaque lot, `clean-test-caches --all` hors lots ; ciblé seulement tant que < 6 Go |
| Commande récursive sur un chemin non vérifié (incident du 2026-10-25) | Faible | Élevé | Règle 18 : `set -u`, `[ -n "$D" ] && [ -d "$D" ]`, chemins absolus ; l'orchestrateur n'approuve aucune commande sans garde |
| Panier : tunnel de commande cassé par la refonte des hooks | Moyenne | Élevé | Test de commande de bout en bout dans les tests rouges (T-200-05) et à l'image reconstruite (T-200-10) ; plugins non modifiés |
| Régression du LCP de la liste (photos plus nombreuses, cartes plus riches) | Moyenne | Moyen | Mesure avant (T-198-02) et après (T-198-10) au même protocole ; seuil d'US-189 ; la home déjà 19 % plus lente au sprint 27 (T-TECH-29-03) |
| Planche d'US-197 refusée ou photos sous licence douteuse | Moyenne | Moyen | Aucune image dans le dépôt avant le GO ; licence relue sur le site ; tranche 1 (point focal) livrable seule ; US-197 sacrifiée la première |
| Conflits de `proof.json` entre PR parallèles | Moyenne | Faible | `bin/proof-rebase` (T-TECH-29-02), PR fusionnées en série |
| Conflit entre US-197 (cadrage des cartes) et US-198 (cartes) | Moyenne | Moyen | Point focal par un composant partagé, mergé avant T-198-06 ; sinon la carte garde le cadrage actuel ; sections séparées dans `b2b.scss` |
| Page connectée non vérifiable en production (aucun identifiant saisi) | Certaine | Faible | Vérification locale et sur l'image reconstruite ; DoD écrite en conséquence |
| Commit au mauvais numéro de story ; décision mal attribuée ; fusion avec une question ouverte | Moyenne | Faible | Règle 13 ; registre des décisions avec auteur ; règle 20 |

## Cérémonies

Calées sur le travail réel : la revue et la rétro se tiennent dès que le périmètre est vérifié, au plus tard le 2026-11-21. Les fichiers `sprint-review.md` et `sprint-retro.md` sont créés à la clôture (T-TECH-29-08), pas au planning.

| Cérémonie | Quand | Durée |
|-----------|-------|-------|
| Sprint Planning (Parts 1 et 2) | 2026-10-25 (périmètre décidé par Thibaut, décomposition faite) | fait |
| Point d'étape | Après les deux maquettes (T-198-03 et T-200-03) et les GO | 30 min |
| Sprint Review | Dès le périmètre vérifié, au plus tard le 2026-11-21 | 1 h |
| Rétrospective | Le même jour que la revue | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
