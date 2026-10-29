# Sprint 35 : Commerce prédictif MVP — squelette, alertes et thème suite

> **Planifié le 2026-10-28**, après la clôture du sprint 34 (PR en cours). Périmètre décidé par Thibaut le 2026-10-28 : **16 points** ([registre des décisions](./registre-decisions.md)). La décomposition en tâches est faite : voir [tasks/README.md](./tasks/README.md). Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-35`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 35 |
| Début | 2026-10-28 (planning) |
| Fin | **2026-11-13** (10 jours ouvrés) au plus tard ; clôture dès que le périmètre est vérifié |
| Capacité | **16 points** (écart de 3 points sur la vélocité de 13 accepté) ; 46 tâches, 37,5 h (37 h pour Claude, 0,5 h d'actions de Thibaut) pour 35,6 h de capacité (16 × 2,1 h + 2 h transverses), soit −1,9 h d'écart |
| Vélocité de référence | S30 13, S31 13, S32 9, S33 13, S34 9 (moyenne 11,4) |
| Branche | `feature/sprint-35-commerce-predictif` (arbre principal, déjà créée ; pas de checkout dans l'arbre partagé) |

Le périmètre revient à 16 points sur la feuille de route innovation : EPIC-17 MVP (réassort prédictif) et suite du thème (US-238). Le disque est à 5,6 Go libres au lancement (registre du 2026-10-28) : aucun test ni rendu local sous 5 Go, aucune suite complète sous 6 Go, au plus deux workers qui testent en même temps. Les portes du sprint : le GO de Thibaut pour le tag v0.1.0 du plugin prédictif avant l'installation dans la démo (US-219), demandé quand le plugin est fusionné. L'ordre de sacrifice est **décidé** par Thibaut (registre) : US-219 d'abord, puis US-238 ; US-217 et US-218 protégées.

## Sprint Goal

> « Livrer le MVP du commerce prédictif avec des règles statistiques explicables : squelette du plugin avec intervalle médian de réassort, alertes dans « Mon compte » et panier pré-rempli, fixtures de démo crédibles, et compléter le thème Industrie & Négoce par son test de rendu et ses composants restants. »

## Périmètre engagé

Gherkin : au minimum 1 nominal, 2 alternatifs et 2 erreurs par story (US-217 : 1+2+2, US-218 : 1+2+2, US-219 : 1+2+2, US-238 : 1+2+2). « Fiche » = fichier `backlog/user-stories/US-XXX.md`.

| ID | Titre | Pts | MoSCoW | Statut | Sacrifice |
|----|-------|-----|--------|--------|-----------|
| [US-217](../../backlog/user-stories/US-217.md) | Commerce prédictif : squelette du plugin et intervalle médian de réassort par référence | 5 | Must | ready-for-dev | **protégée** (registre) |
| [US-218](../../backlog/user-stories/US-218.md) | Commerce prédictif : alerte de réassort dans « Mon compte » et panier pré-rempli | 5 | Must | ready-for-dev | **protégée** (registre) |
| [US-219](../../backlog/user-stories/US-219.md) | Commerce prédictif : fixtures de rythmes d'achat réalistes et intégration à la démo | 3 | Must | ready-for-dev | **n°1** (registre) |
| [US-238](../../backlog/user-stories/US-238.md) | Thème Industrie & Négoce : thème actif prouvé par un rendu et composants restants (suite d'US-222) | 3 | Must | ready-for-dev | **n°2** (registre) |

Total engagé : **16 points**.

- **US-217** crée le dépôt `thibmonier/sylius-b2b-predictive-analytics-plugin` (paquet Composer `tmonier/sylius-b2b-predictive-analytics-plugin`), public, durci comme US-209 ; calcule l'**intervalle médian** entre commandes successives par entreprise et par référence (au moins 3 commandes), la date de dernière commande et la date de réassort attendue ; règles statistiques explicables, pas de ML opaque ; calcul en SQL puis service de domaine testé ; recalcul planifié. **Règle 30 (registre du 2026-10-28)** : orchestrateur crée le dépôt et fusionne ; worker pousse branche et PR seulement ; **aucun tag ni release** sans GO de Thibaut.
- **US-218** affiche un bloc « À réassortir bientôt » dans l'espace client : références dont la date attendue tombe dans les 7 jours, avec l'explication (« vous en commandez tous les 28 jours, dernière commande il y a 25 jours ») ; bouton « Préparer le panier » utilise le lien de réassort (reorder) existant ; l'acheteuse valide, rien n'est commandé automatiquement ; une alerte peut être masquée (« Pas maintenant », « Ne plus proposer »).
- **US-219** intègre le plugin dans la démo : fixtures avec rythmes d'achat distincts sur 12 mois (gants tous les 14 jours, lunettes tous les 60 jours, visserie irrégulière), une alerte garantie à l'ouverture de la démo pour Claire, dates relatives à « aujourd'hui » pour garder les alertes cohérentes après la remise à zéro nocturne ; ajout à la page « Plugins » et « Preuve technique ». **GO de Thibaut pour le tag v0.1.0** demandé quand le plugin est fusionné (registre du 2026-10-28) ; Thibaut soumet le paquet à Packagist ; démo installe `^0.1`, pas de `dev-main`.
- **US-238** complète le thème (suite d'US-222, fusionnée partielle le 2026-10-28, PR #1 du dépôt `thibmonier/sylius-industry-trade-theme`) : test de rendu avec thème actif vert sur Sylius 2.2 et 2.3 (PHP 8.3 et 8.5), rouge prouvé cité (règle 27) ; composants boutons, tableaux et champs livrés avec leurs tests ; CI du dépôt verte, fusion par l'orchestrateur, aucun tag ni release.
- **Tâches techniques** (actions 1 à 5 de la rétro du sprint 34) : règle 31 « vérifier les faits techniques d'un ADR avant présentation » écrite (T-TECH-35-01), règle 32 « interdire de retirer ou d'affaiblir un test pour obtenir du vert » écrite (T-TECH-35-02), règle 33 « nouveau paquet commence par le test d'installation » écrite (T-TECH-35-03), validation YAML de `sprint-status.yaml` (T-TECH-35-04, faite au planning), disque et rendu (T-TECH-35-05, contrôle avant lancement).

**Hors sprint** : US-220 (attrition, sprint 39), reste du thème (US-223 à US-228, sprints 40-41), copilote (US-211 à US-216, sprints 37-38, 42), DPP (US-230 à US-235, sprints 36 et 41).

## Décisions de Thibaut (2026-10-28, registre)

1. Périmètre : US-217 (5), US-218 (5), US-219 (3), US-238 (3), soit 16 points, plus les actions de rétro T-TECH-35-01 à 05.
2. **GO nouveau dépôt du plugin prédictif (US-217)** : création du dépôt public durci comme US-209 (topics habituels), push du code par PR, fusion si la CI est verte, aucun tag ni release sans GO de Thibaut. Règle d'exécution : **l'orchestrateur crée le dépôt et fusionne** ; le worker pousse sa branche et ouvre la PR, il ne crée aucun dépôt et ne fusionne pas.
3. Installation dans la démo (US-219) : **tag v0.1.0 sur GO explicite de Thibaut**, demandé quand le plugin est fusionné ; Thibaut soumet le paquet à Packagist ; la démo installe `^0.1` ; pas de `dev-main` en production.
4. Ordre de sacrifice : US-219 d'abord, puis US-238 ; US-217 et US-218 protégées.
5. Disque à 5,6 Go libres au lancement : Thibaut prévenu (cible 10 Go) ; aucun test ni rendu sous 5 Go, suite complète laissée à la CI sous 6 Go, au plus deux workers qui testent en même temps.

## Estimation du sprint

La fiche annonce 5 + 5 + 3 + 3 points. La décomposition donne 11,5 h (US-217), 10,5 h (US-218), 8,5 h (US-219), 5,5 h (US-238) et 1 h de transverses (dont 0,5 h de Thibaut), soit **37 h de Claude pour 35,6 h : les 16 points dépassent la capacité de 1,9 h**. Franchement : l'écart est couvert par trois hypothèses. D'abord, US-217 et US-218 réutilisent le plugin company-insights déjà publié : les requêtes de calcul d'intervalle s'appuient sur le modèle de commandes complétées connu, sans tâtonnement. Ensuite, US-238 complète le thème sans modifier le rendu de la démo (composants déjà présents en substance, extraction finie), donc aucune capture nouvelle. Enfin, pas de maquette ce sprint (0,25 h économisée). Si la charge dépasse en cours de sprint, l'ordre de sacrifice décidé s'applique (voir [tasks/README.md](./tasks/README.md)).

## Politique Sylius Plus

Ton neutre partout. Le spike US-210 (sprint 34) a vérifié l'absence de recoupement avec Sylius Plus pour le copilote ; le commerce prédictif (EPIC-17) et le thème (EPIC-18) sont vérifiés par leurs propres spikes (US-221 et roadmap) : aucun recoupement connu. En cas de recoupement avéré pendant le sprint, le plugin reste public mais n'est pas promu (politique du 2026-10-14). Aucun texte, titre, description, message de commit ou description de PR ne compare avec l'offre de l'éditeur Sylius ni ne la mentionne. Aucun détail de sécurité (garde-fous, seuils, routes d'admin) dans les dépôts publics (démo et plugin). Les descriptions de PR restent factuelles et neutres.

## Checklist de démarrage (2026-10-28)

### Avant le Sprint Planning

- [x] Sprint précédent terminé : code du sprint 34 fusionné (PR en cours), US-210, US-221, US-222, US-229 en done
- [x] Actions de la rétrospective du sprint 34 reprises :
  - action 1 : T-TECH-35-01 (règle 31 « vérifier les faits techniques ») ;
  - action 2 : T-TECH-35-02 (règle 32 « interdire de retirer un test ») ;
  - action 3 : T-TECH-35-03 (règle 33 « test d'installation en premier ») ;
  - action 4 : T-TECH-35-04 (validation YAML de `sprint-status.yaml`, faite au planning) ;
  - action 5 : T-TECH-35-05 (disque, rendu, reliquats).
- [x] Backlog priorisé : périmètre décidé par Thibaut le 2026-10-28 (roadmap-innovation, sprint 35)
- [x] Stories estimées et « Ready » : voir la Definition of Ready ci-dessous
- [x] Capacité calculée : 35,6 h pour 37 h de charge de Claude
- [x] Fiches US-217, US-218, US-219, US-238 à jour (en-tête « Sprint 35 »)
- [x] Branche `feature/sprint-35-commerce-predictif` créée (arbre principal)
- [ ] Disque et mémoire : `df -h .`, `bin/check-disk`, mémoire libre et swap relevés avant le premier lot (T-TECH-35-05) ; **aucun test ni rendu avant 5 Go libres**

### Après le Sprint Planning

- [x] Sprint backlog visible : [task-board.md](./task-board.md), [tasks/README.md](./tasks/README.md)
- [x] Dépendances : [sprint-dependencies.md](./sprint-dependencies.md) (US-218 ← US-217, US-219 ← US-217 + US-218)
- [x] Consigne de worker et checklist du sprint : [consigne-worker.md](./consigne-worker.md), [checklist-integration.md](./checklist-integration.md)
- [ ] GO du tag v0.1.0 (US-219) demandé quand le plugin est fusionné ; disque libéré (T-TECH-35-05)

## Definition of Ready (contrôle du 2026-10-28)

| US | Fiche | Gherkin | Prérequis | État |
|----|-------|---------|-----------|------|
| US-217 | oui | oui (1+2+2) | company-insights existant ; nouveau dépôt créé par l'orchestrateur sur GO de Thibaut ; squelette de CI copié d'un plugin existant | prête (porte : GO création dépôt, donné au registre) |
| US-218 | oui | oui (1+2+2) | US-217 fusionnée ; lien de réassort (reorder) existant dans la démo | prête (dépendance US-217) |
| US-219 | oui | oui (1+2+2) | US-217 et US-218 fusionnées ; historique de 12 mois (US-155, done) ; page « Plugins » et « Preuve technique » existantes | prête (dépendances US-217, US-218 ; porte : GO tag v0.1.0) |
| US-238 | oui | oui (1+2+2) | US-222 fusionnée (PR #1 du dépôt `thibmonier/sylius-industry-trade-theme`, CI verte le 2026-10-28) | prête (dépendance US-222, done) |

## Definition of Done (rappel)

- [ ] Chaque scénario Gherkin relié à un test ou à une preuve dans le fichier de tâches
- [ ] Stories de code (US-217, US-218, US-219, US-238) : commit de tests rouges cité dans la PR, **avec l'échec observé de chaque test cité dans le message du commit** (règle 27)
- [ ] US-217, US-218 : dépôt durci comme US-209 (workflows aux actions figées par SHA, `permissions` minimales, `persist-credentials: false`, zizmor, `composer audit`, Dependabot) ; CI Sylius 2.2/2.3 verte ; README en français (installation, usage, lien vers la démo, lien vers le profil de l'auteur) ; ton neutre (politique Sylius Plus) ; Packagist soumis par Thibaut ; aucun tag sans GO
- [ ] US-219 : intégration dans la démo, ajout à la page « Plugins » et « Preuve technique » ; fixtures vraisemblables (intervalles entre 7 et 120 jours) ; rendu réel vérifié en FR et EN
- [ ] US-238 : test de rendu avec thème actif vert sur Sylius 2.2 et 2.3 (rouge prouvé cité) ; composants boutons, tableaux et champs livrés ; CI du dépôt verte ; fusion par l'orchestrateur ; aucun tag ni release
- [ ] **Tout état écrit hors base** (règle 16) : le dépôt du plugin prédictif est créé une fois, à son URL définitive ; aucune variable d'environnement nouvelle dans la démo ce sprint
- [ ] PHPStan max sur **tout le dépôt**, tests compris, ECS et Rector verts, rejoués avant le push ; aucune nouvelle entrée `ignoreErrors` ; aucun compteur écrit en dur ; **suite complète locale avant le push ou attente explicite dite dans la PR** (T-TECH-35-13)
- [ ] Preuve publiée par `bin/proof-rebase` ; PR fusionnées en série, aucune avec une question ouverte à Thibaut
- [ ] Aucun détail de sécurité dans les dépôts publics (démo et plugin) ; ton neutre, aucune comparaison avec Sylius Plus
- [ ] Texte public relu contre sa source ; documentation à jour ; numéro de story des commits relu ; aucun commit embarquant le travail d'un autre worker (règle 21) ; dans chaque dépôt de plugin, `git fetch` et branche courante contrôlés avant tout commit (règle 29)
- [ ] CI verte (« Quality and smoke tests », « Docker image », « Dependency audit », zizmor), run terminé avant le merge ; `main` protégée (PR requise)
- [ ] Aucun identifiant saisi en production ; aucun compte de moteur de recherche touché par Claude

## Actions de Thibaut

- Libérer le disque (cible 10 Go libres, commande en chemins absolus précédée d'un `du -sh` fournie par l'orchestrateur) avant le lancement des tests (T-TECH-35-05).
- Donner le GO pour le tag v0.1.0 du plugin prédictif après fusion (T-219-03) ; soumettre le paquet à Packagist (T-219-04).

## Risques

| Risque | Probabilité | Impact | Mitigation |
|--------|-------------|--------|------------|
| Calcul d'intervalle médian qui dépasse sa timebox (5 h) | Moyenne | Moyen | Réutilisation de company-insights ; SQL testé d'abord (T-217-03) ; s'arrêter à 5 h avec écart à arbitrer |
| Recoupement avéré avec Sylius Plus | Faible | Élevé | Vérification du spike US-210 ; plugin reste public mais non promu si recoupement ; décision portée à Thibaut |
| Détail de sécurité dans une PR publique | Faible | Élevé | Revue dédiée (T-217-09, T-218-08, T-219-05, T-238-06) ; PR factuelles et neutres |
| Fixtures de rythmes d'achat irréalistes | Moyenne | Moyen | Test de vraisemblance (T-219-02) ; intervalles entre 7 et 120 jours ; 0 alerte factice inventée |
| Thème qui ne rend pas dans un projet vierge | Moyenne | Moyen | Test d'installation dans la CI (T-238-02) ; rouge prouvé (règle 27) |
| Disque à 5,6 Go, swap variable | Élevée | Moyen | Règle 24 ; `bin/check-disk` ; aucune suite complète sous 6 Go ; au plus deux workers en test |
| Worker crée le dépôt ou fusionne alors que le registre confie ça à l'orchestrateur | Faible | Moyen | Règle 30 « GO transmis avant consignation » ; consigne claire de qui fait quoi ; worker ne crée aucun dépôt |
| Machine saturée (disque, swap) | Élevée | Moyen | Règle 24 ; `bin/check-disk` ; tests et code de US-217/US-218 peu gourmands en disque |
| Estimation du plugin prédictif fausse | Moyenne | Moyen | Calcul SQL d'abord (T-217-03) ; service de domaine testé (T-217-04) ; si charge dépasse, ordre de sacrifice décidé |

## Cérémonies

Calées sur le travail réel : la revue et la rétro se tiennent dès que le périmètre est vérifié, au plus tard le 2026-11-13. Les fichiers `sprint-review.md` et `sprint-retro.md` sont créés à la clôture (T-TECH-35-15), pas au planning.

| Cérémonie | Quand | Durée |
|-----------|-------|-------|
| Sprint Planning (Parts 1 et 2) | 2026-10-28 (périmètre décidé par Thibaut, décomposition faite) | fait |
| Point d'étape | Après la fusion du plugin prédictif (US-217, US-218) | 30 min |
| Sprint Review | Dès le périmètre vérifié, au plus tard le 2026-11-13 | 1 h |
| Rétrospective | Le même jour que la revue | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
