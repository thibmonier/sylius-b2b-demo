# Tâches : US-174 : Montée de la démo à PHPUnit 13

## Informations US

- **Epic** : EPIC-10
- **Persona** : Thibaut (mainteneur)
- **Story Points** : 3 (Could, non sacrifiable (décision de Thibaut du 2026-10-23 après le spike US-169))
- **Sprint** : sprint-24-integrateurs
- **Story** : [US-174](../../../backlog/user-stories/US-174.md)

## Résumé de la US

**En tant que** Thibaut
**Je veux** que la démo passe de PHPUnit 10.5 à PHPUnit 13
**Afin de** garder des outils de test à jour et des tests mieux typés, sans perdre la compatibilité avec Sylius

## Règles et contraintes

- Travail sur la branche de la PR Dependabot #54 (`dependabot/composer/phpunit/phpunit-13.4.3`), rebasée sur `main`, **dans une PR séparée de celle du sprint**. Aucun changement dans `src/`.
- Point de départ (rapport du spike, [rapport.md](../../../audits/2026-10-23-spike-phpunit-13/rapport.md)) : aucun paquet ne borne `phpunit/phpunit` ; la CI de la PR #54 échoue sur 59 erreurs PHPStan dans 17 fichiers de `tests/` (58 de typage, 1 rupture d'API) ; la suite n'a jamais tourné sous PHPUnit 13 (risques d'exécution estimés, pas mesurés).
- **Interdit** : une ligne d'ignorance (`ignoreErrors`) ajoutée à la configuration de PHPStan ; chaque erreur est corrigée par un type réel ou une assertion.
- **Repli** : plus de 10 échecs de comportement au premier run, hors typage, alors la story s'arrête, la PR #54 est fermée et Dependabot ignore les versions majeures de `phpunit/phpunit`, **avec l'accord de Thibaut** (T-174-11).
- **Mesure par la CI** : pas de second `vendor/` local (disque d'environ 6 Go, seuil de 5 Go) ; PHPStan et la suite sous PHPUnit 13 se lisent dans les runs de CI de la branche. Le push de la branche de la PR #54 suit la règle du sprint : aucun push sans GO de Thibaut (le GO permanent « fusionner les PR dès qu'elles sont vertes » couvre le merge, pas un push non prévu).
- Règles du sprint, consigne de worker et actions de la rétro du sprint 23 : voir [README.md](./README.md).

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- `composer.json`, `composer.lock` (déjà modifiés par la PR #54 ; rebase sur `main`)
- `tests/Support/TestArrays.php` (nouveau) et son test
- `tests/Unit/Architecture/` (garde sur les annotations en docblock)
- 14 fichiers de `tests/` pour le typage, dont `tests/Functional/Demo/McpAccess.php`, `ContainerFillFixturesTest.php`, `tests/Smoke/PluginsAndHomepageTest.php`, `tests/Unit/Demo/Integration/IntegrationMapTest.php`, `tests/Unit/Demo/Plugin/DemoPluginCatalogTest.php`, `tests/Unit/Demo/Proof/ProofDataFileTest.php`, `tests/Unit/Theme/ThemeHooksTest.php`
- `tests/Unit/Order/OrderPoReferenceTest.php` (rupture d'API), `tests/Unit/Demo/PunchOutSimulator/` (mocks sans attente)
- 5 fichiers à `@dataProvider` en docblock (`tests/Smoke/PagesTest.php`, `AccountsSmokeTest.php`, `ContainerCapacityCalculatorTest`, `BasicAuthGateTest`, …)
- `config/demo/proof.json` (rafraîchi : de nouveaux fichiers de tests)

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-174-01 | [OPS] | Branche de la PR #54 récupérée dans une branche d'essai (`gh pr checkout 54`) et rebasée sur `main` (`composer.lock` régénéré, pas fusionné à la main) ; nombre de tests exécutés par la CI de `main` relevé (« avant ») ; ressources relevées | 0,5 h | - | Claude | ✅ |
| T-174-02 | [TEST] | Garde rouge, commitée seule et citée dans la PR : `NoDocblockAnnotationsInTestsTest` (aucun `@dataProvider` en docblock, 16 occurrences listées) ; sortie PHPStan de la branche (59 erreurs) conservée comme état rouge | 0,75 h | T-174-01 | Claude | 🚫 |
| T-174-03 | [TEST] | Assistant `tests/Support/TestArrays` (`map()`, `list()`) et son test d'abord (types réels, échec explicite sur une valeur non conforme), sur le modèle de `BadgeRegistryTest::map()` | 0,75 h | T-174-02 | Claude | ✅ |
| T-174-04 | [TEST] | Typage, lot 1 (tests fonctionnels et de fumée : trait `McpAccess`, `ContainerFillFixturesTest`, `PluginsAndHomepageTest`, …) : accès par clé via l'assistant, PHPStan max sans nouvelle ignorance | 1,25 h | T-174-03 | Claude | ✅ |
| T-174-05 | [TEST] | Typage, lot 2 (tests unitaires : `IntegrationMapTest`, `DemoPluginCatalogTest`, `ProofDataFileTest`, `ThemeHooksTest`, `AuditLogIgnoresDemoResetTest`, `BadgeRegistryTest`, `RailwayTrustedHostsTest`) | 1,25 h | T-174-03 | Claude | ✅ |
| T-174-06 | [TEST] | API de PHPUnit 13 : `OrderPoReferenceTest` (`with()` sans `expects()` devient un stub avec `willReturnMap`), 16 `@dataProvider` passés en `#[DataProvider]` (la garde de T-174-02 devient verte), `DemoResetCommandTest:181` vérifié | 1,25 h | T-174-02 | Claude | ✅ |
| T-174-07 | [TEST] | Passe de CI sur la branche : suite complète sous PHPUnit 13 avec `failOnNotice` ; mocks sans attente convertis en `createStub()` (simulateurs PunchOut et autres) ; nombre de tests exécutés relevé (« après »). Plus de 10 échecs de comportement hors typage : arrêt, repli (T-174-11) | 1 h | T-174-04, T-174-05, T-174-06 | Claude | ✅ |
| T-174-08 | [TEST] | `php bin/proof-refresh` puis `php bin/proof-check` (nouveaux fichiers de tests), ECS (`src tests`), PHPStan max et Rector rejoués sur l'arbre exact ; diff de `phpstan.dist.neon` relu : aucune entrée `ignoreErrors` ajoutée | 0,5 h | T-174-07 | Claude | ✅ |
| T-174-09 | [REV] | Revue (aucun attendu de test modifié hors typage, aucun commentaire supprimé par erreur, règle 9) et table « scénario → test » remplie | 0,25 h | T-174-08 | Claude | ✅ |
| T-174-10 | [OPS] | CI verte de la PR #54 (« Quality and smoke tests », « Docker image »), run terminé, puis merge **en dernier** de la liste des PR du sprint (voir la matrice des chevauchements) ; rebase et `proof-refresh` rejoués si `main` a bougé | 0,25 h | T-174-09 | Orchestrateur | ✅ |
| T-174-11 | [REV] | 📅 Thibaut donne son accord seulement en cas de repli (fermeture de la PR #54 et Dependabot réglé pour ignorer les majeures de `phpunit/phpunit`) ; sans objet si la montée réussit | 0,25 h | T-174-07 | Thibaut | ✅ |

**Total estimé : 8 h** (11 tâches, dont 0,25 h de Thibaut ; 7,75 h pour Claude et l'orchestrateur).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, document et section). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Montée réussie (nominal) | PR #76 fusionnée (0ce1199) : CI du commit 85701b1 verte, `Tests: 1976, Assertions: 16292` sous PHPUnit 13.4.3, couverture de lignes 93,31 % ; PHPStan max `[OK] No errors`, ECS et Rector verts |
| Fournisseurs de données conservés (alternatif) | Dernier build de `main` sous PHPUnit 10 : 1 968 tests ; run local sous PHPUnit 13 : 1 975 (les 1 968 + 7 de `TypedTest`) ; CI : 1 976 (+ `DataSetsTest`) : aucun cas perdu. 16 `@dataProvider` passés en attributs (937769c) |
| Doubles sans attente (alternatif) | 38 notices dans 7 classes (CI du commit fb5003d) → 0 : stubs (8d0f882), `#[AllowMockObjectsWithoutExpectations]` justifié sur `DemoResetCommandTest` ; `bin/run-tests tests/Unit` : 1 070 tests, code 0, aucune notice |
| Échecs de comportement nombreux (erreur) | Repli non déclenché : premier run complet sous PHPUnit 13, 0 échec et 0 erreur de comportement (20 avertissements et 38 notices de PHPUnit, corrigés ensuite) |
| Typage contourné (erreur) | `phpstan.dist.neon` inchangé, aucune entrée `ignoreErrors` ni `@phpstan-ignore` ; lectures typées par `tests/Support/Typed.php` et `DemoCatalogs` (72e11e3 rouge, ec63645) |

## Réalisation (2026-10-23, orchestrateur)

- La PR Dependabot #54 a été fermée le 2026-10-23 avant le sprint et sa branche supprimée : la montée est faite sur `chore/phpunit-13` depuis `main`, PR #76 (accord de Thibaut pour une réalisation par l'orchestrateur, après le refus de `git` dans le worktree isolé du worker w174).
- Commit rouge : f8c4d2c (la montée seule, PHPStan max à 59 erreurs) ; tests rouges des assistants : 72e11e3 ; test rouge du workflow : 27c5c6c.
- **Écart T-174-02 (🚫)** : la garde `NoDocblockAnnotationsInTestsTest` n'a pas été écrite. PHPUnit 13 ignore une annotation `@dataProvider` en docblock et la méthode, qui attend ses arguments, échoue : la régression est déjà détectée par la suite.
- Le helper prévu `TestArrays` s'appelle `Typed` ; s'y ajoutent `DemoCatalogs` (catalogues construits à un seul endroit au lieu de trois) et `DataSets` (fournisseur partagé réduit aux N premiers arguments, 20 avertissements de PHPUnit levés).
- Trouvé en CI : PHPUnit 13 colore le rapport de couverture texte ; le résumé du job ne trouvait plus ses lignes et l'étape échouait alors que tous les tests passaient. Correction : `--colors=never` dans `ci.yaml` (27c5c6c rouge, 85701b1).
- Écarts de processus : une exécution directe de `phpunit` (rejouée ensuite par `bin/run-tests`) ; le test de `DataSets` commité avec son code et non seul.
