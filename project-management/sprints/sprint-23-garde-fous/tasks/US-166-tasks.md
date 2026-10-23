# Tâches : US-166 : Méthodes longues découpées et suppression des `usleep` des tests

## Informations US

- **Epic** : EPIC-10
- **Persona** : Thibaut (mainteneur)
- **Story Points** : 3 (Could, non sacrifiable (cause du test instable `RunTestsLockTest`))
- **Sprint** : sprint-23-garde-fous
- **Story** : [US-166](../../../backlog/user-stories/US-166.md)

## Résumé de la US

**En tant que** Thibaut
**Je veux** découper les méthodes de plus de 45 lignes et retirer les `usleep` des tests
**Afin de** rendre le code plus lisible et la suite de tests plus rapide et déterministe

## Règles et contraintes

- Relevé du 2026-10-23 : 6 méthodes de plus de 45 lignes dans `src` (`ProductCsvFixture::createOrUpdateVariant` 73, `OrderPoReferenceTypeExtension::buildForm` 63, `CsvProductReader::parseHandle` 57, `PunchoutDemoFixture::loadCxmlBuyer` 57, `ProductCsvFixture::importProduct` 54, `DemoResetCommand::reset` 47).
- 7 `usleep` dans 5 fichiers de tests : `RunTestsLockTest` (test instable de la PR #64), `VerifyPublicModeTest`, `McpOutboundGuardTest`, `WebhookDeliveryTest`, `HardeningTest`.
- Refactoring pur : aucun attendu de test modifié ; les tests existants servent de filet.
- Règles du sprint, consigne de worker et actions de la rétro du sprint 22 : voir [README.md](./README.md).

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- `src/Demo/DataFixtures/ProductCsvFixture.php`, `PunchoutDemoFixture.php`
- `src/Form/Extension/OrderPoReferenceTypeExtension.php`
- `src/Demo/Infrastructure/Csv/CsvProductReader.php`
- `src/Demo/Command/DemoResetCommand.php`
- `tests/Unit/Bin/`, `tests/Functional/Demo/` (5 fichiers à `usleep`)
- `.github/workflows/ci.yaml` ou test de garde (**partagé avec US-168**)

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-166-01 | [DOC] | Cadrage : relevé confirmé (6 méthodes, 7 `usleep`), horloge disponible (`symfony/clock` ou attente bornée sur condition), choix de la garde (test d'architecture ou étape CI) | 0,5 h | - | Claude | ✅ |
| T-166-02 | [TEST] | Gardes rouges, commitées seules et citées dans la PR : aucune méthode de `src` au-delà de 45 lignes, aucun `usleep` dans `tests/` | 1 h | T-166-01 | Claude | ✅ |
| T-166-03 | [BE] | `ProductCsvFixture` : `createOrUpdateVariant` et `importProduct` découpées | 1,25 h | T-166-02 | Claude | ✅ |
| T-166-04 | [BE] | `OrderPoReferenceTypeExtension::buildForm` et `CsvProductReader::parseHandle` découpées | 1 h | T-166-02 | Claude | ✅ |
| T-166-05 | [BE] | `PunchoutDemoFixture::loadCxmlBuyer` et `DemoResetCommand::reset` découpées | 1 h | T-166-02 | Claude | ✅ |
| T-166-06 | [TEST] | Les 7 `usleep` remplacés (horloge contrôlée ou attente sur condition), dont `RunTestsLockTest` ; 20 exécutions de suite de `RunTestsLockTest` sans échec | 1,5 h | T-166-02 | Claude | ✅ |
| T-166-07 | [TEST] | Suite complète via `bin/run-tests` (si le disque le permet, sinon CI), ECS, PHPStan max, Rector ; aucun attendu de test modifié (diff de `tests/` relu) | 0,75 h | T-166-03, T-166-04, T-166-05, T-166-06 | Claude | ✅ |
| T-166-08 | [REV] | Revue (comportement inchangé, noms des méthodes extraites) et table « scénario → test » remplie | 0,25 h | T-166-07 | Claude | ✅ |

**Total estimé : 7,25 h** (8 tâches, dont 0 h de Thibaut ; 7,25 h pour Claude et l'orchestrateur).

## Cadrage (T-166-01)

- **Relevé confirmé** le 2026-10-23 par la garde elle-même : les 6 méthodes de l'audit avec les mêmes longueurs (73, 63, 57, 57, 54, 47) et les 7 `usleep` (3 dans `RunTestsLockTest`, 1 dans chacun des 4 autres fichiers).
- **Garde = tests d'architecture**, pas d'étape CI (`ci.yaml` n'est pas touché) : `tests/Unit/Architecture/MethodLengthTest.php` (analyse `nikic/php-parser`, longueur = ligne de fin - ligne de début + 1, limite 45, message « fichier: Classe::méthode() has N lines ») et `tests/Unit/Architecture/NoSleepInTestsTest.php` (jetons `PhpToken`, appels de `sleep`/`usleep`/`time_nanosleep`/`time_sleep_until` en tant que fonction ; un mot dans une chaîne ou une méthode `->sleep()` ne compte pas). Chaque garde a un test de sa propre mesure.
- **Horloge** : `symfony/clock` est installé (dépendance transitive) ; les attentes passent par `tests/Support/Await.php` (`until` avec échéance, `listening` sur un port), dont l'horloge est injectable (`MockClock` dans son test).
- **Refactoring pur** : méthodes extraites privées, comportement identique, aucun attendu de test modifié.

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, document et section). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Méthodes découpées (nominal) | `MethodLengthTest::testNoMethodOfSrcIsLongerThanTheLimit` : rouge au commit 493701d (6 méthodes listées), vert après dd91e02, ece4e51 et 8ca1049 ; `bin/run-tests tests/Unit/Architecture` → `OK (4 tests, 4 assertions)` |
| Comportement inchangé (alternatif) | Suite complète `WORKER_ID=w166 TEST_DB_NAME=b2b_w166 TEST_CACHE_DIR=var/cache-w166 bin/run-tests` : 1874 tests, seuls échouent les 10 de `ArchitecturePageTest` (US-170, de l'orchestrateur, non commités au moment du run) ; `git diff 77014ec -- tests/` : aucun attendu modifié, seulement les fichiers à `usleep`, le helper, les gardes et leurs tests |
| Horloge injectée à la place des attentes (alternatif) | `AwaitTest` (`MockClock` : `testItPollsUntilTheConditionHoldsWithoutRealWaiting`, `testItFailsWithTheGivenMessageOnceTheDeadlineIsPassed`) ; `RunTestsLockTest::testWaitBlocksWhileARunHoldsTheLockThenReturnsWhenItEnds` attend deux lectures du verrou (journal du stub `cat`) ; 20 exécutions de `RunTestsLockTest` : 20 × `OK (12 tests, 89 assertions)` |
| Régression de comportement (erreur) | Le filet est la suite existante : `bin/run-tests tests/Unit/Demo tests/Functional/Demo/DemoResetCommandTest.php tests/Functional/FixturesConsistencyTest.php tests/Smoke/PunchOutSimulatorSmokeTest.php tests/Functional/Demo/PunchOutOciSimulatorTest.php` → `OK, but there were issues! Tests: 751` (avertissements d'obsolescence déjà présents) ; un découpage qui change une sortie fait échouer ces tests |
| Nouvel usleep ajouté (erreur) | `NoSleepInTestsTest::testNoTestCallsSleepOrUsleep` (rouge à 493701d avec les 7 appels, vert après fb9e3df) et `testTheScanFindsACallButNotAWordInAStringOrAMethodName` (la mesure détecte `usleep(` et `\sleep(`, ignore la chaîne, `->sleep()` et `Clock::sleep()`) |
