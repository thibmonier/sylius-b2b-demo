# Rétrospective du sprint 56

**Directive fondamentale** : « Quoi que nous découvrions, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »

Entrée principale : [sprint-review.md](./sprint-review.md). 13 points de code livrés sur 13, mais seule US-286 (3 points) est « done » ; US-287 et US-288 sont en review, PR brouillon non fusionnées.

## Suivi des actions du sprint 55

| ID | Appliquée ? | Preuve |
|----|-------------|--------|
| A-55-1 (liste de contrôle du squelette) | Oui | Citée dans `tasks/US-287-tasks.md` ; aucun rouge invalide de build sur le nouveau dépôt de santé |
| A-55-2 (stub rouge qui passe PHPStan) | Partiellement | Citée dans `US-287-tasks.md` (T-287-02) et tenue pour US-287 et US-288 ; non tenue pour T-TECH-56-02 : la CI s'est arrêtée à PHPStan sur le SHA rouge (run 45021551169) |
| A-55-3 (essai contre l'API réelle) | Sans objet | Aucun client HTTP externe dans ce sprint |
| A-55-4 (pas de PHPUnit direct, chercher les lecteurs d'un compteur) | Non évaluable | La revue ne signale aucun contournement ; elle ne dit pas non plus que la règle a été vérifiée |
| A-55-5 (lire la CI sur le SHA poussé) | Oui | La revue cite SHA de tête et numéros de run pour chaque PR |

## Ce qui a bien marché

- Le spike (US-286) a tranché avant d'écrire : « on écrit le contrat », avec un GO daté de Thibaut et une timebox tenue (7 h, +1 h confirmée).
- Trois rouges sur quatre prouvés en CI par des échecs d'assertion (US-287 unitaire, US-288 sur les deux suites).
- La leçon de la PR #31 du thème a été traduite en critère d'acceptation vert (sonde qui lève une exception).
- Captures réelles FR et EN de la page, relues avant la revue.
- Le blocage de T-TECH-56-02 a été levé par deux PR du thème (#31, #33, tag v0.3.2) sans fusion avec une question ouverte.
- Les refus de force-push ont été contournés sans perte, par des commits de fusion.

## Ce qui a coûté

- **Rouge non prouvé en CI, deux fois** : US-287 (doublon d'identifiant, non unitaire : la CI s'arrête au pas unitaire) et T-TECH-56-02 (PHPStan en échec sur le SHA rouge). La règle « rouge valide = échec d'assertion vu en CI » a été contournée par la structure de la CI, pas par négligence.
- **PR empilées** : #199 après la fusion de #197, thème #32 après la fusion en squash de #33. Chaque fois un conflit, résolu par un commit de fusion parce que le force-push est refusé ; les SHA de #199 ont changé.
- **Commit mêlant tests et squelettes** sur US-288 (force-push refusé) : le rouge reste lisible mais le commit n'est plus « seul ».
- **Outils de qualité avec PHP local** (T-TECH-56-02 ; supply-chain d'US-287 avec `--allowed-root`) au lieu de Docker, contre la consigne du dépôt.
- **Tests instables de la démo** : `CleanTestCachesTest::testAStaleLockDoesNotBlockARealRun`, `CheckDiskTest` (virgule décimale en locale française), `WebhookDeliveryTest` et `RenderPageTest` (délais sous charge). L'exécuteur de santé en hériterait.
- **Écart de worker** : `register.html.twig` conservé (des canaux n'ont pas le thème). Raison valable, mais découverte au rapport de fin, donc à confirmer après coup (écart 5 de la revue).
- **Glissades de l'orchestrateur** : boucle d'attente de CI arrêtée après le premier check terminé ; amend d'un commit déjà poussé (récupéré sans force-push). Pas d'action dédiée : couverts par A-56-2 (pas de réécriture d'historique poussé) et par la lecture des checks sur le SHA (A-55-5, à tenir).
- La CI de `main` de la démo est rouge depuis le 2026-11-20 sans que personne ne l'ait signalé avant ce sprint.

## Actions (SMART)

| ID | Action | Responsable | Échéance | Mesure |
|----|--------|-------------|----------|--------|
| A-56-1 | Dans le workflow CI du plugin de santé (puis dans le gabarit de nouveau plugin), lancer la suite non unitaire même si l'unitaire échoue (`if: !cancelled()` sur le pas non unitaire). Règle 26 du gabarit de consigne : un commit rouge doit passer PHPStan et ECS ; le rouge est lu dans le run de ce SHA, suites unitaire et non unitaire | orchestrateur | avant le premier commit rouge du sprint 57 | zéro rouge « vu en local seulement » dans la revue du sprint 57 |
| A-56-2 | Règle 27 : pas de PR empilée sur une PR qui sera fusionnée en squash ou rebase ; sinon la fiche prévoit la procédure (fusion de `main` dans la branche empilée par commit de fusion, jamais amend ni force-push d'un commit poussé) et l'ordre de fusion | orchestrateur | planning du sprint 57 | zéro conflit d'empilement imprévu ; PR empilées listées au planning |
| A-56-3 | Règle 28 : PHPStan, ECS, Rector et supply-chain se lancent dans Docker (`docker compose run` ou `make`) ; un passage en PHP local est noté dans le rapport avec sa raison. Le script supply-chain documente son `--allowed-root` dans le README du dépôt | orchestrateur | consignes du sprint 57 | zéro mention « PHP local » non justifiée dans les rapports de workers |
| A-56-4 | Corriger les 4 tests instables de la démo avant la première sonde (sprint 57) : locale fixée dans `CheckDiskTest`, horloge injectée dans `CleanTestCachesTest`, délais de `WebhookDeliveryTest` et `RenderPageTest` indépendants de la charge ; PR de la démo, hors périmètre des sondes | worker démo | avant la première story de sonde du sprint 57 | 5 exécutions consécutives de la suite non unitaire sans échec, dont une avec `LC_ALL=fr_FR.UTF-8` |
| A-56-5 | Règle 29 : tout écart à une tâche (fichier conservé, périmètre changé) est signalé à l'orchestrateur **dès qu'il est décidé**, avec la raison, avant de poursuivre ; il est inscrit au registre des décisions et ne se découvre pas au rapport de fin. Décision de Thibaut attendue sur `register.html.twig` | worker, orchestrateur | consignes du sprint 57 | tout écart de la revue du sprint 57 a une entrée datée au registre avant la fin du lot |

## Règles proposées pour `templates/consigne-worker.md` (non modifié ici)

26. **Rouge prouvé en CI sur toutes les suites** (A-56-1, A-55-2).
27. **Pas d'empilement sans procédure de fusion** (A-56-2).
28. **Outils de qualité par Docker** (A-56-3).
29. **Écart à la tâche signalé tout de suite** (A-56-5).
