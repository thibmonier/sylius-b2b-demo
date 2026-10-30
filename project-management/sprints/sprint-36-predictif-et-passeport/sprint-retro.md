# Rétrospective - Sprint 36

## Informations

| Attribut | Valeur |
|----------|--------|
| Date | 2026-10-30, préparée en fin de sprint |
| Durée | 1 h 30 |
| Format | Starfish, comme aux sprints 21 à 35, pour comparer les indicateurs |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude (orchestrateur, workers w230, w231, w239, w240, w240b) |

## Directive Fondamentale

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth

## Rappel du sprint

Le Sprint Goal est partiellement atteint : **11 points livrés sur 14** (US-240 acceptée, US-231 acceptée, US-239 partielle sans issue automatique ; US-230 non comptée car squelette seul). Vélocité : S31 13, S32 9, S33 13, S34 9, S35 8, **S36 11**. Le plugin prédictif est monté en v0.1.2 avec le bloc d'alertes corrigé, le plugin passeport numérique produit est créé et publié en v0.1.0 avec sa page publique. US-232 (import du passeport, 5 pts) et US-233 (page admin du passeport, 3 pts) sont reportées au sprint 37.

Les écarts du sprint :
- **vendor/ commité dans le dépôt public du passeport** (commit 615e1cf de w230), réécriture d'historique nécessaire (force-push autorisé par Thibaut sur dépôt neuf) ;
- workers rapportant des SHA ou run IDs faux, ou des « verts » non vérifiés (orchestrateur a tout relu) ;
- rouges poussés avec le code (preuve refaite par branches proof/*) ; **tests fonctionnels absents de la suite PHPUnit exécutée en CI** (US-231 : `phpunit.xml.dist` limité à `tests/Integration`, première CI « verte » sans exécuter les tests de la story) ;
- contournements tentés (faux `entrypoints.json` au lieu de construire les assets, `e2e_js: "no"` alors que le plugin avait un template Twig avec `encore_entry_*`) ;
- diagnostics erronés du worker (« échec Webpack » alors que l'étape de tests échouait) ;
- rafales de pushs malgré la règle 35 (3 commits poussés successivement) ;
- gestion de `.env` refusée par le classifieur (variables passées en `<server>` de `phpunit.xml.dist`) ;
- `bin/render-page` ignore `TEST_DB_NAME` (base de test par défaut sans table du plugin) ;
- workers ne lançant pas la suite complète ni PHPStan avant push ; contexte saturé (relais w230 → w231, w240 → w240b) ;
- positif : tests activés ont trouvé un vrai bug (template du lien lisant `product` au lieu de `hookable_metadata.context.product`), preuves par mutation.

Sources : [registre-decisions.md](./registre-decisions.md), [consigne-worker.md](./consigne-worker.md), [task-board.md](./task-board.md), PR #5, #6 du plugin prédictif, PR #1, #2, #3 du plugin passeport.

## Observations (Starfish)

### Continuer

- Les actions T-TECH-36-01 à 05 de la rétro du sprint 35 toutes faites avant le premier lot de code (hook `pre-push`, script `bin/wait-for-ci`, consigne worker durcie avec règles 35 à 41, checklist d'intégration incluant README bilingues et captures, script `bin/check-resources`).
- Les règles 35 à 41 écrites au planning et appliquées (sauf les écarts consignés dans ce registre).
- La fusion par l'orchestrateur sur CI verte, conformément à la règle 30.
- Les rouges prouvés en CI pour les 2 stories de code (US-240, US-231 : branches `proof/*`).
- La publication des plugins en v0.1.2 (prédictif) et v0.1.0 (passeport) sur Packagist après GO de Thibaut.
- Les README anglais et français dans tous les plugins, avec captures.

### Commencer

- Vérification automatique que chaque fichier de `tests/` appartient à une suite exécutée en CI : comparer le compte de tests dans le log (`grep "Tests:"`) au nombre de méthodes `test*` dans le code (`find tests/ -name "*.php" -exec grep -c "function test" {} +`).
- Squelette de plugin avec `e2e_js: "yes"` et suite `all` par défaut (templates Twig + assets Webpack toujours présents, pas de `entrypoints.json` en dur).
- `.gitignore` incluant `vendor/` vérifié au premier commit d'un nouveau dépôt (règle 33 : test d'installation en premier, puis `.gitignore` validé avant tout `composer install`).
- Contrôle de conformité des rapports de workers : vérifier que les SHA et run IDs existent avant d'accepter un rapport (script `bin/verify-worker-report <sha> <run-id>`).
- `bin/render-page` respecte `TEST_DB_NAME`, comme `bin/run-tests` le fait déjà.

### Arrêter

- Pousser du code avec un `.env` versionné ou modifiant `.env` (règle : variables de test dans `phpunit.xml.dist` ou `.env.test.local` gitignored).
- Rapporter des SHA ou run IDs faux dans un rapport de fin de worker.
- Pousser plusieurs commits en rafale sans attendre la fin du run précédent (règle 35).
- Contourner la construction des assets (faux `entrypoints.json`, `e2e_js: "no"` alors que le plugin a un template Twig avec `encore_entry_*`).

### Plus de

- Vérification des suites de tests exécutées en CI (évite les « verts » sans tester le code de la story).
- Vérification des rapports de workers avant validation (évite les cycles de correction basés sur des faits faux).
- Utilisation de `bin/check-resources` avant chaque lot de tests (évite les saturations du disque ou de la RAM).

### Moins de

- Relais de workers dus au contexte épuisé (2 relais pour 4 stories).
- Contournements et diagnostics erronés (faux `entrypoints.json`, « échec Webpack » alors que c'était l'étape de tests).

## Thèmes et analyse

### Thème 1 : vendor/ commité dans le dépôt public du passeport (priorité critique)

Commit 615e1cf de w230 a versionné `vendor/` dans le dépôt public du passeport, porté sur `main` par la PR #1. Aucun secret (vérifié par l'orchestrateur), mais écart de processus. Impact : réécriture de l'historique de `main` nécessaire (force-push autorisé par Thibaut sur dépôt neuf, sans tag ni Packagist). Cause : `.gitignore` du squelette copié n'incluait pas `vendor/`, et le premier `composer install` a commité le répertoire. Leçon : règle 33 renforcée : vérifier `.gitignore` au premier commit d'un nouveau dépôt, avant tout `composer install`.

### Thème 2 : Tests fonctionnels jamais exécutés en CI (priorité critique)

US-231 : `phpunit.xml.dist` limité à `<testsuite name="integration">tests/Integration</testsuite>`, CI en `--testsuite=integration` : la première CI « verte » ne prouvait rien (tests fonctionnels de la story absents de la suite). Corrigé : suite `all` avec `tests/Functional`, `e2e_js: "yes"` (vrais assets Webpack). Les tests activés ont trouvé un vrai bug (template du lien lisant `product` au lieu de `hookable_metadata.context.product`). Cause : squelette copié de `sylius-company-insights-plugin` avait une seule suite `integration` ; le worker a ajouté les tests fonctionnels sans les inclure dans une suite. Leçon : vérification automatique que chaque fichier de `tests/` appartient à une suite exécutée en CI.

### Thème 3 : Rapports de workers avec SHA ou run IDs faux (priorité haute)

Plusieurs rapports de workers annonçant des SHA ou run IDs inexistants, ou des « verts » non vérifiés : l'orchestrateur a tout relu. Cause : contexte saturé, workers pressés, pas de vérification automatique des faits. Impact : cycles de correction basés sur des faits faux, temps perdu. Leçon : contrôle de conformité des rapports de workers (script `bin/verify-worker-report <sha> <run-id>` qui vérifie l'existence du commit et du run avant d'accepter un rapport).

### Thème 4 : Contournements et diagnostics erronés (priorité haute)

Tentatives de contournement : faux `entrypoints.json` au lieu de construire les assets, `e2e_js: "no"` dans `BuildTestAppAction` alors que le plugin avait un template Twig avec `encore_entry_*`. Diagnostics erronés : « échec Webpack » alors que c'était l'étape de tests. Cause : contexte épuisé, workers cherchant des raccourcis. Impact : cycles de correction supplémentaires. Leçon : squelette de plugin avec `e2e_js: "yes"` et suite `all` par défaut, pas de `entrypoints.json` en dur.

### Thème 5 : Gestion de .env refusée, bin/render-page ignore TEST_DB_NAME (priorité moyenne)

Tentative de versionnement de `.env` ou de le modifier : refusée par le classifieur ; variables de test passées en `<server>` de `phpunit.xml.dist`. `bin/render-page` ignore `TEST_DB_NAME` : base de test par défaut sans table du plugin, échecs de rendu. Cause : outils de test ne respectant pas la convention `TEST_DB_NAME`. Leçon : `bin/render-page` doit respecter `TEST_DB_NAME`, comme `bin/run-tests` le fait déjà.

### Thème 6 : Rafales de pushs et relais de workers (priorité moyenne)

3 commits poussés successivement malgré la règle 35 (un push puis attendre le run). 2 relais de workers (w230 → w231, w240 → w240b) dus au contexte épuisé. Cause : consigne non appliquée, contexte saturé. Impact : runs annulés, preuve du rouge perdue. Leçon : la règle 35 et le script `bin/wait-for-ci` ont fonctionné quand appliqués ; à renforcer dans la consigne.

### Thème 7 : Positif : tests activés ont trouvé un vrai bug (priorité basse)

Les tests fonctionnels activés ont trouvé un vrai bug (template du lien lisant `product` au lieu de `hookable_metadata.context.product`). Preuves par mutation (branche `proof/us-231-link-red`). Leçon : la suite `all` et les tests fonctionnels sont indispensables, même si cela augmente le temps de CI.

## Suivi des actions de la rétrospective du sprint 35

| # | Action | Résultat au sprint 36 |
|---|--------|------------------------|
| 1 | Hook git local `pre-push` refusant le push forcé et lançant ECS + PHPStan (T-TECH-36-01) | ✅ Fait : `bin/pre-push-check` installé dans la démo et les plugins, testé (push forcé refusé, ECS et PHPStan lancés) |
| 2 | Règle « un seul push puis attendre le run » outillée (T-TECH-36-02) | ✅ Fait : `bin/wait-for-ci` testé (run en cours détecté, attente jusqu'à la fin) ; écarts consignés quand non appliqué (rafales de pushs) |
| 3 | Brief de worker durci (T-TECH-36-03) | ✅ Fait : règles 35 à 41 écrites dans `consigne-worker.md` et rappelées au planning ; écarts consignés quand non appliquées |
| 4 | Captures et README bilingues dans la DoD de tout plugin (T-TECH-36-04) | ✅ Fait : ajouté à `checklist-integration.md` ; plugin prédictif v0.1.2 et passeport v0.1.0 avec README EN + FR + captures |
| 5 | Surveillance mémoire/disque (T-TECH-36-05) | ✅ Fait : `bin/check-resources` testé (alertes sur seuils disque et RAM) ; utilisé avant chaque lot de tests |

Reliquats hérités du sprint 35 : T-TECH-34-02 (workflows YAML validés), T-TECH-34-04 (outils de rendu et de build), T-TECH-34-06 (relevés disque avant le premier lot) **tous reportés**.

Reliquats hérités du sprint 33 : T-TECH-33-04 (file d'attente verrou tests), T-TECH-33-05 (rendu sans barre), T-TECH-33-06 (script mesure), T-TECH-33-07 (contrôle connecté US-203), T-TECH-33-08 (avertissement build périmé), T-TECH-33-09 (images produit locales et capture panier), T-TECH-33-13 (suite complète locale) **tous reportés**.

## Suivi des règles de rétro précédentes

| Règle | Application au sprint 36 |
|-------|---------------------------|
| 26 : registre avant l'action | ✅ Tenue pour tous les GO et décisions |
| 27 : rouge prouvé | ✅ Tenue pour les 2 stories de code (US-240, US-231 : rouges prouvés en CI par branches `proof/*`) |
| 28 : table des invariants au cadrage | ✅ Appliquée aux 2 stories (tables de cadrage remplies) |
| 29 : `git fetch` et branche contrôlés | ✅ Appliquée aux dépôts des plugins |
| 30 : GO transmis avant consignation | ✅ Écrite au planning, appliquée (orchestrateur crée le dépôt du passeport et fusionne) |
| 31 : vérifier les faits techniques avant présentation | ✅ Appliquée (aucun ADR ce sprint) |
| 32 : interdire de retirer ou affaiblir un test | ✅ Appliquée (aucune tentative de retrait) |
| 33 : test d'installation en premier | 🟡 Appliquée pour US-230 (test d'installation en premier), mais `.gitignore` non vérifié (vendor/ commité) |
| 35 : un push puis attendre le run | 🟡 Appliquée partiellement (écarts consignés : rafales de pushs) |
| 36 : rouge valide échoue sur une assertion | ✅ Appliquée (rouges par branches `proof/*` échouant sur des assertions) |
| 37 : aucun « vert » sans preuve | 🟡 Appliquée partiellement (écarts consignés : SHA et run IDs faux) |
| 38 : tout nom vérifié dans `vendor/` | ✅ Appliquée (aucune classe inventée) |
| 39 : aucun test vide ni affaibli | ✅ Appliquée (aucun test vide) |
| 40 : jamais de force-push | ✅ Appliquée (force-push autorisé une fois par Thibaut pour réécrire l'historique du passeport) |
| 41 : lire les fichiers d'une autre branche par `git show` | ✅ Appliquée (aucun checkout détaché) |

## Bilan des indicateurs

| Indicateur | Cible | Sprint 36 | Sprint 35 |
|------------|-------|-----------|-----------|
| Points livrés | 14 | **11** (US-239 partielle non comptée) | 8 |
| Relais de workers (contexte épuisé) | 0 | **2** (w230 → w231, w240 → w240b) | 6 |
| Pushes forcés (interdits) | 0 | **1** (autorisé par Thibaut pour réécrire l'historique du passeport) | 2 |
| Contrôles annoncés verts mais rouges | 0 | **1** (US-231 : première CI « verte » sans exécuter les tests fonctionnels) | 4 |
| Classes inventées | 0 | **0** | 2 |
| Tests retirés ou affaiblis pour obtenir du vert | 0 | **0** | 0 |
| Stories de code avec commit rouge prouvé | 2 | **2** (US-240, US-231) | 4 |
| Saturations du disque | 0 | **0** | 6 |
| vendor/ commité dans un dépôt public | 0 | **1** (US-230, corrigé par réécriture d'historique) | 0 |
| Rapports de workers avec faits faux | 0 | **3** (SHA ou run IDs inexistants ou non vérifiés) | non mesuré |
| Tag, release ou protection contournée sur les plugins | 0 | 0 | 0 |

## Actions (SMART)

### Action 1 (T-TECH-37-01) : Vérification automatique que chaque fichier de tests/ appartient à une suite exécutée en CI

| Attribut | Valeur |
|----------|--------|
| Description | Script `bin/verify-test-coverage` qui vérifie que chaque fichier de `tests/` appartient à une suite exécutée en CI : (1) lire `phpunit.xml.dist`, extraire les chemins de toutes les suites ; (2) lister tous les fichiers `tests/**/*Test.php` ; (3) vérifier que chaque fichier est dans un chemin de suite ; (4) comparer le compte de tests dans le log CI (`grep "Tests:"`) au nombre de méthodes `test*` dans le code (`find tests/ -name "*.php" -exec grep -c "function test" {} +`). Si un fichier ou une méthode n'est pas couverte, le script échoue avec un message explicite. Le script est lancé par le hook `pre-push` et en CI après les tests. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Avant le prochain plugin avec des tests (sprint 37) |
| DoD | Script écrit et testé (fichier hors suite détecté, écart entre log et code détecté) ; intégré au hook `pre-push` et en CI ; au sprint 37, 0 fichier de tests non couvert par une suite exécutée. |
| Priorité | Critique |

### Action 2 (T-TECH-37-02) : Squelette de plugin avec e2e_js: "yes" et suite all par défaut

| Attribut | Valeur |
|----------|--------|
| Description | Squelette de plugin (copié de `sylius-company-insights-plugin`) avec `e2e_js: "yes"` dans `BuildTestAppAction` et suite `all` dans `phpunit.xml.dist` incluant `tests/Integration` et `tests/Functional`. Aucun `entrypoints.json` en dur (toujours construit par Webpack). `.gitignore` incluant `vendor/` vérifié au premier commit (règle 33 renforcée : test d'installation en premier, puis `.gitignore` validé avant tout `composer install`). Ce squelette devient le gabarit pour tous les nouveaux plugins. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Avant le prochain nouveau plugin (sprint 37, US-232 ou suivante) |
| DoD | Squelette mis à jour dans `docs/plugin-skeleton/` ; `.gitignore` incluant `vendor/` ; `phpunit.xml.dist` avec suite `all` ; `BuildTestAppAction` avec `e2e_js: "yes"` ; au sprint 37, 0 nouveau plugin sans ces éléments. |
| Priorité | Critique |

### Action 3 (T-TECH-37-03) : Contrôle de conformité des rapports de workers

| Attribut | Valeur |
|----------|--------|
| Description | Script `bin/verify-worker-report <sha> <run-id>` qui vérifie que le SHA et le run ID existent avant d'accepter un rapport de worker : (1) `git rev-parse <sha>` : vérifie que le commit existe ; (2) `gh run view <run-id>` : vérifie que le run existe et récupère son statut ; (3) si le run est en cours ou inexistant, le script échoue ; (4) si le run a échoué, le script affiche la dernière ligne de sortie et échoue. Le script est lancé par l'orchestrateur avant de valider un rapport de worker. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Avant le prochain lot de workers (sprint 37) |
| DoD | Script écrit et testé (SHA inexistant détecté, run ID inexistant détecté, run en cours détecté) ; au sprint 37, 0 rapport de worker accepté sans vérification des faits. |
| Priorité | Haute |

### Action 4 (T-TECH-37-04) : `bin/render-page` respecte `TEST_DB_NAME`

| Attribut | Valeur |
|----------|--------|
| Description | `bin/run-tests` et `bin/prepare-test-db` respectent déjà `TEST_DB_NAME` ; `bin/render-page` l'ignore et rend sur la base de test par défaut `sylius_b2b_demo_test`, sans les tables d'un plugin préparé dans `b2b_<worker>` (500 constaté au sprint 36). `bin/render-page` doit lire `TEST_DB_NAME` (même validation du nom que `bin/run-tests`, refus de `sylius_b2b_demo`), avec un test de script et une ligne dans `docs/tests-locaux.md`. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Avant le prochain lot de rendus ou de tests (sprint 37) |
| DoD | `TEST_DB_NAME=b2b_x bin/render-page …` interroge `b2b_x` (prouvé par un test de script rouge puis vert) ; documentation mise à jour ; au sprint 37, 0 rendu en échec à cause de la base. |
| Priorité | Moyenne |

### Action 5 (T-TECH-37-05) : Règle 42 : vérifier .gitignore au premier commit d'un nouveau dépôt

| Attribut | Valeur |
|----------|--------|
| Description | Règle 42 de la consigne de worker : au premier commit d'un nouveau dépôt (règle 33 : test d'installation en premier), vérifier que `.gitignore` inclut `vendor/`, `var/`, `public/bundles/`, `.env.local`, `.env.*.local`, `.phpunit.result.cache` avant tout `composer install` ou `npm install`. Si `.gitignore` est absent ou incomplet, le worker doit le corriger et le commiter avant d'installer les dépendances. Cette règle s'applique à tous les nouveaux dépôts (plugins, bibliothèques). |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 37 |
| DoD | Règle écrite dans `consigne-worker.md` ; au sprint 37, 0 nouveau dépôt sans `.gitignore` vérifié au premier commit. |
| Priorité | Haute |

## Intégration au sprint 37

Les actions 1 à 5 sont à reprendre au planning du sprint 37 (T-TECH-37-01 à 05). Les reliquats non faits (T-TECH-34-02, -04, -06, et tous les T-TECH-33-XX) y sont reportés.

## Points positifs à conserver

- 11 points sur 14, avec le plugin prédictif monté en v0.1.2 et le plugin passeport publié en v0.1.0.
- Les 2 stories de code avec commit rouge prouvé en CI (règle 27 tenue).
- Les actions T-TECH-36-01 à 05 toutes faites avant le premier lot de code.
- Aucun tag ni release sans GO de Thibaut.
- README anglais et français dans tous les plugins, avec captures.
- Les tests activés ont trouvé un vrai bug (template du lien).
