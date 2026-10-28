# Sprint 34 : Spikes copilote, DPP et packaging du thème ; thème tranche 1

> **Planifié le 2026-10-28**, après la clôture du code du sprint 33 (13 points sur 13, PR non encore fusionnée). Périmètre décidé par Thibaut le 2026-10-28 : **14 points** ([registre des décisions](./registre-decisions.md)). La décomposition en tâches est faite : voir [tasks/README.md](./tasks/README.md). Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-34`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 34 |
| Début | 2026-10-28 (planning) |
| Fin | **2026-11-11** (10 jours ouvrés) au plus tard ; clôture dès que le périmètre est vérifié |
| Capacité | **14 points** (écart d'un point sur la vélocité de 13 accepté) ; 43 tâches, 33 h (32,5 h pour Claude, 0,5 h d'actions de Thibaut) pour 31,4 h de capacité (14 × 2,1 h + 2 h transverses), soit −1,6 h d'écart |
| Vélocité de référence | S29 13, S30 13, S31 13, S32 9, S33 13 (moyenne 12,2) |
| Branche | `feature/sprint-34-spikes-et-theme` (arbre principal, déjà créée ; pas de checkout dans l'arbre partagé) |

Le périmètre revient à 14 points sur la feuille de route innovation : trois spikes (US-210, US-221, US-229) plus la tranche 1 du thème (US-222). Le disque est à 4 Go libres, la mémoire à 1,7 Go et le swap à 4 Go au lancement : aucun test ni rendu local sous 5 Go, aucune suite complète sous 6 Go (registre du 2026-10-28). Les portes du sprint : le GO de Thibaut sur l'ADR d'US-221 (format de packaging) avant de lancer US-222, et la relecture au fil de l'eau de chaque ADR de spike. L'ordre de sacrifice est **décidé** par Thibaut (registre) : US-222 d'abord, puis US-229 ; US-210 et US-221 protégées.

## Sprint Goal

> « Cadrer trois capacités innovantes de la démo par leurs spikes et livrer la tranche 1 du thème Industrie & Négoce : format de rejeu du copilote sans appel LLM payant, formulation prudente du passeport produit, packaging d'un thème Sylius 2 installable par Composer, et paquet du thème avec ses jetons de design et ses composants communs. »

## Périmètre engagé

Gherkin : au minimum 1 nominal, 2 alternatifs et 2 erreurs par story (US-210 : 1+2+2, US-221 : 1+2+2, US-222 : 1+2+2, US-229 : 1+2+2). « Fiche » = fichier `backlog/user-stories/US-XXX.md`.

| ID | Titre | Pts | MoSCoW | Statut | Sacrifice |
|----|-------|-----|--------|--------|-----------|
| [US-210](../../backlog/user-stories/US-210.md) | Spike copilote d'achat : format de rejeu, adaptateur LLM et recoupement Sylius Plus | 3 | Must | ready-for-dev | **protégée** (registre) |
| [US-221](../../backlog/user-stories/US-221.md) | Spike packaging d'un thème Sylius 2 : Twig hooks, assets et Flex | 3 | Must | ready-for-dev | **protégée** (registre) |
| [US-229](../../backlog/user-stories/US-229.md) | Spike passeport numérique produit : texte du règlement ESPR et formulation prudente | 3 | Must | ready-for-dev | **n°2** (registre) |
| [US-222](../../backlog/user-stories/US-222.md) | Thème Industrie & Négoce : paquet installable, jetons de design et composants communs (tranche 1) | 5 | Must | backlog | **n°1** (registre) ; attend GO de l'ADR d'US-221 |

Total engagé : **14 points**.

- **US-210** cadre le copilote d'achat (EPIC-16) : format de rejeu (démo en mode rejoué, aucun appel LLM payant), adaptateur LLM (interface minimale, adaptateur rejoué et adaptateur réel pour les intégrateurs), outils exposés (inventaire des outils b2b-mcp réutilisables, liste des interdits), recoupement Sylius Plus. Timebox 6 h, ADR court relu par Thibaut, note privée de sécurité hors dépôt public. Estimation d'US-211 à US-216 confirmée ou révisée.
- **US-221** cadre le packaging du thème (EPIC-18) : bundle de thème Sylius 2 vs paquet de gabarits, Twig hooks et surcharges, assets (Asset Mapper ou Encore, Stimulus), recette Flex, compatibilité Sylius 2.2/2.3, recoupement Sylius Plus. Timebox 6 h, ADR et prototype minimal (une page) installé dans un projet vierge. Estimation d'US-222 à US-228 confirmée ou révisée. **GO de Thibaut sur l'ADR avant de lancer US-222**.
- **US-229** cadre le passeport numérique produit (EPIC-19) : texte du règlement (UE) 2024/1781, calendrier batteries 2027, actes délégués par catégorie, formulation prudente (« base ouverte et versionnée », jamais « conforme » ni « certifié »), identifiants GS1 Digital Link, recoupement Sylius Plus. Timebox 6 h, ADR relu par Thibaut, sources citées, formulation validée avant tout texte public. Estimation d'US-230 à US-235 confirmée ou révisée.
- **US-222** extrait le thème de la démo vers `thibmonier/sylius-industry-trade-theme` : paquet Composer, recette Flex, jetons de design (couleurs, typographie, espacements, contrastes AAA), composants communs (en-tête, pied de page, boutons, tableaux, champs, messages) et gabarit de base. Haute densité d'information. Maquettes de référence : canevas Claude Design G2StRQigRKD6SqNRoyHpkW. Les pages complètes (US-223, US-224) viennent en tranche 2. Dépôt public créé par l'orchestrateur sur GO de Thibaut, worker pousse branche et PR, orchestrateur fusionne CI verte, aucun tag ni release.
- **Tâches techniques** (actions 1 à 5 de la rétro du sprint 33) : règle 30 « GO transmis avant consignation » écrite (T-TECH-34-01), workflows validés par analyseur YAML (T-TECH-34-02), disque libéré par Thibaut et scripts de tests en file d'attente (T-TECH-34-03), outils de rendu et de build (T-TECH-34-04), contrôles de production d'US-207 après déploiement (T-TECH-34-05).

**Hors sprint** : US-211 à US-216 (copilote MVP, sprints 37-38), US-223 à US-228 (thème tranches 2 et 3, sprints 39-41), US-230 à US-235 (DPP MVP, sprints 36 et 41).

## Décisions de Thibaut (2026-10-28, registre)

1. Périmètre : US-210 (3), US-221 (3), US-229 (3), US-222 (5), soit 14 points, plus les actions de rétro T-TECH-34-01 à 05.
2. **GO nouveau dépôt du thème (US-222)** : création du dépôt public durci comme US-209 (topics habituels), push du code par PR, fusion si la CI est verte, aucun tag ni release ; Packagist soumis plus tard par Thibaut. Règle d'exécution : **l'orchestrateur crée le dépôt et fusionne** ; le worker pousse sa branche et ouvre la PR, il ne crée aucun dépôt et ne fusionne pas.
3. Relecture des ADR **au fil de l'eau** : chaque ADR de spike (US-210, US-221, US-229) est présenté à Thibaut dès qu'il est prêt ; **US-222 ne démarre qu'après le GO de Thibaut sur l'ADR d'US-221**.
4. Ordre de sacrifice : US-222 d'abord, puis US-229 ; US-210 et US-221 protégées.
5. Disque à 4 Go libres, mémoire 1,7 Go, swap 4 Go : au plus deux workers qui testent en même temps (règle 24), aucun test ni rendu sous 5 Go, suite complète laissée à la CI sous 6 Go.

## Estimation du sprint

La fiche annonce 3 + 3 + 3 + 5 points. La décomposition donne 6,5 h (US-210), 6,5 h (US-221), 6,5 h (US-229), 12 h (US-222) et 1 h de transverses (dont 0,5 h de Thibaut), soit **32,5 h de Claude pour 31,4 h : les 14 points dépassent la capacité de 1,6 h**. Franchement : l'écart est couvert par trois hypothèses. D'abord, les spikes sont timboxés à 6 h chacun : s'ils tiennent dans leur timebox, les stories qui en dépendent (US-211 à US-216, US-222 à US-228, US-230 à US-235) gardent leur estimation, sinon les epics sont ré-estimés au planning suivant. Ensuite, US-222 suppose le format de packaging déjà tranché (ADR d'US-221) : l'extraction du thème de la démo vers le paquet suit l'approche décidée, sans tâtonnement. Enfin, pas de maquette ni de captures nouvelles pour US-222 (le rendu des composants ne change pas dans la démo, 0,25 h économisée). Si la charge dépasse en cours de sprint, l'ordre de sacrifice décidé s'applique (voir [tasks/README.md](./tasks/README.md)).

## Politique Sylius Plus

Ton neutre partout. Chaque spike vérifie l'absence de recoupement avec Sylius Plus dans la documentation officielle publique avant toute promotion ; en cas de recoupement, le plugin reste public mais n'est pas promu (politique du 2026-10-14). Aucun texte, titre, description, message de commit ou description de PR ne compare avec l'offre de l'éditeur Sylius ni ne la mentionne. Aucun détail de sécurité (garde-fous, seuils, routes d'admin) dans les dépôts publics (démo et plugins). Les ADR et les descriptions de PR restent factuels et neutres.

## Checklist de démarrage (2026-10-28)

### Avant le Sprint Planning

- [x] Sprint précédent terminé : code du sprint 33 fusionné (PR en attente), US-207 et US-209 en done
- [x] Actions de la rétrospective du sprint 33 reprises :
  - action 1 : ✅ règle 30 « GO transmis avant consignation » écrite au planning (T-TECH-34-01) ;
  - action 2 : T-TECH-34-02 (workflows validés par analyseur YAML) ;
  - action 3 : T-TECH-34-03 (Thibaut libère le disque, verrou en file d'attente) ;
  - action 4 : T-TECH-34-04 (rendu sans barre de débogage, script de mesure, avertissement de build périmé) ;
  - action 5 : T-TECH-34-05 (contrôles de production d'US-207, reliquats T-TECH-33-07 et -09).
- [x] Backlog priorisé : périmètre décidé par Thibaut le 2026-10-28 (roadmap-innovation, sprint 34)
- [x] Stories estimées et « Ready » : voir la Definition of Ready ci-dessous
- [x] Capacité calculée : 31,4 h pour 32,5 h de charge de Claude
- [x] Fiches US-210, US-221, US-222, US-229 à jour (en-tête « Sprint 34 »)
- [x] Branche `feature/sprint-34-spikes-et-theme` créée (arbre principal)
- [ ] Disque et mémoire : `df -h .`, `bin/check-disk`, mémoire libre et swap relevés avant le premier lot (T-TECH-34-06) ; **aucun test ni rendu avant 5 Go libres**

### Après le Sprint Planning

- [x] Sprint backlog visible : [task-board.md](./task-board.md), [tasks/README.md](./tasks/README.md)
- [x] Dépendances : [sprint-dependencies.md](./sprint-dependencies.md) (US-222 ← ADR d'US-221)
- [x] Consigne de worker et checklist du sprint : [consigne-worker.md](./consigne-worker.md), [checklist-integration.md](./checklist-integration.md)
- [ ] GO de l'ADR d'US-221 avant US-222 ; disque libéré (T-TECH-34-03)

## Definition of Ready (contrôle du 2026-10-28)

| US | Fiche | Gherkin | Prérequis | État |
|----|-------|---------|-----------|------|
| US-210 | oui | oui (1+2+2) | b2b-mcp, tier-pricing, purchase-approval existants ; documentation Sylius Plus accessible ; accès aux ADR 0001-0017 | prête (aucune porte) |
| US-221 | oui | oui (1+2+2) | projet vierge Sylius 2.3 disponible ; documentation Sylius 2 et Flex accessible ; maquettes dans G2StRQigRKD6SqNRoyHpkW | prête (aucune porte) |
| US-229 | oui | oui (1+2+2) | accès au texte du règlement (UE) 2024/1781 et aux calendriers officiels ; documentation Sylius Plus accessible | prête (aucune porte) |
| US-222 | oui | oui (1+2+2) | US-221 (ADR, GO de Thibaut), thème de la démo (EPIC-03, refontes récentes), maquettes dans G2StRQigRKD6SqNRoyHpkW | prête (porte : GO de l'ADR d'US-221) |

## Definition of Done (rappel)

- [ ] Chaque scénario Gherkin relié à un test ou à une preuve dans le fichier de tâches
- [ ] Stories de code (US-222) : commit de tests rouges cité dans la PR, **avec l'échec observé de chaque test cité dans le message du commit** (règle 27)
- [ ] US-210, US-221, US-229 : ADR relus par Thibaut, livrables dans `docs/adr/0018-*.md`, `0019-*.md`, `0020-*.md` ; estimations des epics confirmées ou révisées ; note privée de sécurité (US-210) hors dépôt public
- [ ] US-222 : dépôt durci comme US-209 (workflows aux actions figées par SHA, `permissions` minimales, `persist-credentials: false`, zizmor, `composer audit`, Dependabot) ; CI Sylius 2.2/2.3 verte (rendu de smoke test) ; README en français (installation, usage, lien vers la démo, lien vers le profil de l'auteur) ; thème installé dans un projet vierge de la CI ; ton neutre (politique Sylius Plus) ; Packagist soumis par Thibaut ; aucun tag sans GO
- [ ] **Tout état écrit hors base** (règle 16) : le dépôt du thème est créé une fois, à son URL définitive ; aucune variable d'environnement nouvelle dans la démo ce sprint
- [ ] PHPStan max sur **tout le dépôt**, tests compris, ECS et Rector verts, rejoués avant le push ; aucune nouvelle entrée `ignoreErrors` ; aucun compteur écrit en dur ; **suite complète locale avant le push ou attente explicite dite dans la PR** (T-TECH-34-13)
- [ ] Preuve publiée par `bin/proof-rebase` ; PR fusionnées en série, aucune avec une question ouverte à Thibaut
- [ ] Aucun détail de sécurité dans les dépôts publics (démo et thème) ; ton neutre, aucune comparaison avec Sylius Plus ; spikes vérifient l'absence de recoupement avant toute promotion
- [ ] Texte public relu contre sa source ; documentation à jour ; numéro de story des commits relu ; aucun commit embarquant le travail d'un autre worker (règle 21) ; dans chaque dépôt de plugin, `git fetch` et branche courante contrôlés avant tout commit (règle 29)
- [ ] CI verte (« Quality and smoke tests », « Docker image », « Dependency audit », zizmor), run terminé avant le merge ; `main` protégée (PR requise)
- [ ] Aucun identifiant saisi en production ; aucun compte de moteur de recherche touché par Claude

## Actions de Thibaut

- Relire chaque ADR de spike au fil de l'eau (US-210, US-221, US-229) ; donner le GO de l'ADR d'US-221 avant de lancer US-222 (T-221-03).
- Libérer le disque (cible 10 Go libres, commande en chemins absolus précédée d'un `du -sh` fournie par l'orchestrateur) avant le lancement des tests (T-TECH-34-03).
- Poser le réglage `DEMO_INDEXING=1` en production après le déploiement de `main` (T-TECH-34-05, US-207).
- Soumettre le thème à Packagist après validation du paquet (T-222-11) ; décider des tags et releases.

## Risques

| Risque | Probabilité | Impact | Mitigation |
|--------|-------------|--------|------------|
| Spike qui dépasse sa timebox (6 h) | Moyenne | Moyen | Timebox stricte ; s'arrêter à 6 h avec points ouverts à arbitrer ; ré-estimation de l'epic au planning suivant |
| Recoupement avéré avec Sylius Plus | Faible | Élevé | Vérification dans chaque spike ; plugin reste public mais non promu si recoupement ; décision portée à Thibaut |
| ADR d'US-221 non validé par Thibaut | Faible | Élevé | US-222 sacrifiée en premier ; autre spike protégé |
| Détail de sécurité dans un ADR public | Faible | Élevé | Note privée hors dépôt pour US-210 ; revue dédiée (T-210-04, T-221-04, T-229-04, T-222-14) ; ADR factuels et neutres |
| Prototype d'US-221 qui ne fonctionne pas dans un projet vierge | Moyenne | Moyen | Test d'installation dans la CI ; ADR consigne l'écart et propose une approche alternative |
| Thème extrait qui entre en conflit avec des surcharges existantes | Moyenne | Moyen | Ordre de priorité documenté et testé (T-222-08) ; ADR d'US-221 le cadre |
| Disque à 4 Go, swap à 4 Go | Élevée | Moyen | Règle 24 ; `bin/check-disk` ; aucune suite complète sous 6 Go ; spikes peu gourmands en disque |
| Estimation d'un epic fausse après le spike | Moyenne | Moyen | Ré-estimation au planning suivant ; roadmap ajustée |
| Worker crée le dépôt ou fusionne alors que le registre confie ça à l'orchestrateur | Faible | Moyen | Règle 30 « GO transmis avant consignation » ; consigne claire de qui fait quoi ; worker ne crée aucun dépôt |
| Promesse de conformité réglementaire (DPP) | Faible | Élevé | Formulation prudente validée avant tout texte public (T-229-03) ; test de contenu bannissant « conforme » et « certifié » (US-235, non ce sprint) |
| Machine saturée (disque, swap) | Élevée | Moyen | Règle 24 ; `bin/check-disk` ; US-222 peu gourmande en disque si les tests de la démo ne sont pas relancés |

## Cérémonies

Calées sur le travail réel : la revue et la rétro se tiennent dès que le périmètre est vérifié, au plus tard le 2026-11-11. Les fichiers `sprint-review.md` et `sprint-retro.md` sont créés à la clôture (T-TECH-34-15), pas au planning.

| Cérémonie | Quand | Durée |
|-----------|-------|-------|
| Sprint Planning (Parts 1 et 2) | 2026-10-28 (périmètre décidé par Thibaut, décomposition faite) | fait |
| Point d'étape | Après la relecture des trois ADR de spikes (T-210-03, T-221-03, T-229-03) | 30 min |
| Sprint Review | Dès le périmètre vérifié, au plus tard le 2026-11-11 | 1 h |
| Rétrospective | Le même jour que la revue | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
