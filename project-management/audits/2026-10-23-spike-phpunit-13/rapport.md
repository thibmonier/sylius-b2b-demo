# Spike US-169 : passage de la démo à PHPUnit 13

> Date : 2026-10-23 · Périmètre : PR Dependabot #54 (`phpunit/phpunit` 10.5.66 vers 13.4.3, branche `dependabot/composer/phpunit/phpunit-13.4.3`) · Méthode : lecture seule (`gh`, `git fetch` sans checkout, sources officielles de PHPUnit), aucune installation, aucun run local, aucun push.

## 1. Synthèse

| Question | Réponse |
|----------|---------|
| Un paquet interdit-il PHPUnit 13 ? | **Non.** Aucun paquet du `composer.lock` de la PR ne porte de borne sur `phpunit/phpunit`. |
| Pourquoi la CI est rouge ? | 59 erreurs PHPStan dans `tests/` (17 fichiers), dues au **typage des assertions** de PHPUnit 13 et à du code de test qui s'appuyait sur l'ancien narrowing ; 1 erreur est une vraie rupture d'API (`with()` sur un double). |
| La suite a-t-elle tourné ? | **Non.** Le job `Quality and smoke tests` échoue à l'étape « Coding standard and static analysis » ; les tests ne sont pas lancés. Les risques d'exécution (section 4) sont donc estimés, pas mesurés. |
| Coût estimé | **3 points** (fourchette 2 à 4). |
| Recommandation | Monter, dans une story du sprint suivant (section 6). |

## 2. T-169-01 : blocages de dépendances

Source : `composer.lock` de la branche de la PR (`git fetch origin <branche>` puis `git show FETCH_HEAD:composer.lock`). Le diff de la PR ne touche que `composer.json` (`"phpunit/phpunit": "^13.4.3"`, ligne 71) et `composer.lock`.

- Paquets du lock dont le `require` cite `phpunit/phpunit` : **aucun**.
- Paquets avec un `conflict` sur `phpunit/phpunit` : **aucun** (seuls `doctrine/deprecations` et `oskarstark/enum-helper` portent un `conflict`, sans rapport).
- `sylius/sylius` v2.3.2 (en `require`, pas en dev) ne dépend pas de PHPUnit : la compatibilité avec Sylius n'est pas en jeu.
- Absents du lock, donc sans effet : `phpstan/phpstan-phpunit`, `dama/doctrine-test-bundle`, `zenstruck/*`, `symfony/phpunit-bridge`, `paratest`, `mockery`, `prophecy`.
- Paquets de dev présents : `phpstan/phpstan` 2.3.1, `phpstan-doctrine` 2.0.28, `phpstan-webmozart-assert` 2.0.0, `sylius/sylius-rector` 3.9.0, `symplify/easy-coding-standard`, `rector/rector`, `symfony/browser-kit`, `dom-crawler`, `css-selector`, `web-profiler-bundle`, `debug-bundle` : aucun ne contraint PHPUnit.
- Plateforme : `config.platform.php = ^8.5` ; PHPUnit 13 exige PHP 8.4 ou plus : compatible.
- Satellites montés avec PHPUnit 13.4.3 : `php-code-coverage` 14.4.1, `php-file-iterator` 7.0.2, `php-timer` 9.0.0, etc. Aucun conflit de résolution (le lock est généré).

Conclusion : pas de blocage. Le scénario « Blocage de dépendance » ne se déclenche pas. Les plugins publiés gardent la matrice PHPUnit de leur propre CI (hors périmètre, voir US-169).

## 3. T-169-02 : les 59 erreurs PHPStan par cause

Source : `gh run view 39952504413 --log-failed` (run CI du 2026-10-23, étape « Coding standard and static analysis », `[ERROR] Found 59 errors`). Aucun fichier de `src/` n'est en cause.

| Cause | Identifiants PHPStan | Nombre | Fichiers |
|-------|----------------------|--------|----------|
| A. Narrowing perdu après `assertIsArray()` / `assertNotEmpty()` : les valeurs de `json_decode()` / `Yaml::parseFile()` restent `mixed` ou `array<mixed>`, donc accès par clé impossible | `offsetAccess.nonOffsetAccessible` (35), `cast.string` (3), `offsetAccess.notFound` (1), `foreach.nonIterable` (1) | 40 | 14 fichiers de tests |
| B. Valeur `mixed` passée à un constructeur ou retournée là où un type précis est attendu (conséquence directe de A) | `argument.type` (15), `return.type` (3) | 18 | `Smoke/PluginsAndHomepageTest`, `Unit/Demo/Integration/IntegrationMapTest`, `Unit/Demo/Plugin/DemoPluginCatalogTest`, `Unit/Demo/Proof/ProofDataFileTest`, `Unit/Theme/ThemeHooksTest`, `Functional/Demo/ContainerFillFixturesTest` |
| C. Rupture d'API de PHPUnit (vraie) | `method.notFound` (1) | 1 | `Unit/Order/OrderPoReferenceTest.php:136` |
| D. Extension PHPStan de PHPUnit | n/a | 0 | `phpstan-phpunit` n'est pas installée ; aucune erreur ne vient d'elle |

Total : 40 + 18 + 1 = 59. (A + B = 58 erreurs de typage ; C = 1.)

Exemples :
- A : `tests/Functional/Demo/McpAccess.php:93` « Cannot access offset 'content' on mixed » (trait de test `McpAccess`, signalé une fois par classe utilisatrice : `McpGuideTest`, `RegistrationApprovalDemoTest`) ; `tests/Unit/Demo/AuditLogIgnoresDemoResetTest.php:18` après `self::assertIsArray($config['tmonier_sylius_audit_log']['ignored_commands'] ?? null)`.
- B : `tests/Unit/Demo/Integration/IntegrationMapTest.php:104` et `DemoPluginCatalogTest.php:171` : `DemoAccountCatalog::__construct()` reçoit `mixed` pour `$password`, `$companies`, `$consumers`, `$adminUsers` (4 erreurs `argument.type` répétées dans 3 fichiers).
- C : `Call to an undefined method PHPUnit\Framework\MockObject\InvocationStubber::with()` sur `$form->method('get')->with('poReference')->willReturn(...)` : `with()` n'existe plus sur un double configuré sans `expects()` (voir aussi la dépréciation dure #6507 en section 4).

Mécanisme (vérifié dans les sources, non rejoué) : `Framework/Assert.php` de 10.5.66 annote `assertIsArray` avec `@psalm-assert array $actual` et `assertNotEmpty` avec `@psalm-assert !empty $actual` ; celui de 13.4.3 annote `assertIsArray` avec `@phpstan-assert array<mixed> $actual` et ne porte plus d'assertion de type sur `assertNotEmpty`. PHPStan en niveau max signale alors tout accès par clé sur le résultat. Je n'ai pas pu rejouer PHPStan en local (consigne) : la correspondance exacte entre chaque erreur et chaque annotation reste à confirmer lors de la correction.

## 4. T-169-03 : ce que PHPUnit 13 casse, et usages dans `tests/`

Sources : annonce PHPUnit 13 (phpunit.de/announcements/phpunit-13.html), `DEPRECATIONS.md` et `ChangeLog-13.4.md` de la branche 13.4. Règle de PHPUnit : ce qui était déprécié « dur » en 12 est retiré en 13 ; ce qui était déprécié « mou » en 12 devient « dur » en 13. PHP 8.4 minimum.

| Changement PHPUnit 13 | Usages dans `tests/` (grep) | Risque |
|-----------------------|-----------------------------|--------|
| `with*()` sans `expects()` : déprécié dur (13.0.2), et `with()` absent du type de retour de `method()` | 8 `->with(`, 28 `->expects(` (11 fichiers) ; 1 cas sans `expects()` : `OrderPoReferenceTest.php:136` | 1 correction certaine (cause C). `DemoResetCommandTest.php:181` à vérifier. |
| `any()` déprécié dur (12.5.5) | 0 | Aucun |
| `withConsecutive()` retiré (11) | 0 | Aucun |
| `atLeast()` avec argument non positif | non recherché | Faible |
| `id()` / `after()` sur les attentes de mock (déprécié mou 13.1) | 0 trouvé | Aucun |
| `expectExceptionMessage()` déprécié mou (13.2), remplacé par `expectExceptionMessageIsOrContains()` | 25 occurrences, 13 fichiers | Pas de rupture en 13 ; la dépréciation mole n'est pas signalée par défaut |
| Mock sans attente (`createMock` sans `expects()`) signalé en notice, et `phpunit.xml.dist` a `failOnNotice="true"` | 59 `createMock(`, 13 fichiers ; 2 fichiers sans aucun `expects()` : `Unit/Demo/PunchOutSimulator/PunchOutSimulatorControllerTest.php`, `PunchOutOciSimulatorControllerTest.php` | À mesurer au premier run ; remède : `createStub()` |
| Annotations docblock (`@dataProvider`, `@covers`, etc.) retirées depuis 12 | 16 occurrences dans 5 fichiers | **Réel** : les annotations (`@dataProvider`) ne sont plus lues depuis PHPUnit 12 ; vues dans `Smoke/PagesTest`, `Smoke/AccountsSmokeTest`, `ContainerCapacityCalculatorTest`, `BasicAuthGateTest` : le test concerné échouerait faute de données. À migrer en `#[DataProvider]` |
| Attributs `#[DataProvider]`, `#[CoversClass]`, etc. | 121 occurrences, 61 fichiers | Déjà au bon format |
| Fournisseurs de données statiques | 28 `static function ...Provider` dans 12 fichiers | Conforme (non statiques interdits depuis 11) |
| `createStub(` | 86 occurrences, 20 fichiers | Conforme |
| `getMockBuilder`, `createPartialMock`, `createConfiguredMock` | 0 | Aucun |
| Config XML : `<source ignoreSuppressionOfDeprecations restrictNotices restrictWarnings>` | présent, format PHPUnit 10+ | À valider avec `phpunit --migrate-configuration` sur la branche d'essai ; `cacheResult` / `--cache-result` déprécié dur, non utilisé dans la config lue |

Non mesuré : exécution réelle de la suite (échecs, notices, dépréciations). La suite compte 192 classes de test dérivant de `TestCase`, `KernelTestCase` ou `WebTestCase`. Un run de CI sur une branche d'essai serait nécessaire pour lever l'incertitude ; **je ne l'ai pas lancé** (consigne : rien n'est poussé). Proposition : l'orchestrateur ou Thibaut pousse une branche jetable avec les correctifs de la section 5, et la CI fait office de mesure.

## 5. Changements de code nécessaires

1. **Typage des tests (58 erreurs)** : dans 14 fichiers, remplacer les accès directs sur `json_decode()` / `Yaml::parseFile()` par un petit assistant de test typé (par exemple `TestArrays::map(mixed): array<string, mixed>` et `::list(mixed): list<...>`), sur le modèle de `self::map()` déjà utilisé dans `BadgeRegistryTest`. Alternative moins chère mais moins propre : `@var` locaux. Aucun changement dans `src/`.
2. **`OrderPoReferenceTest.php:136`** : transformer `$form->method('get')->with(...)` en `$form->expects(self::any()...)` n'est pas possible (`any()` déprécié) ; utiliser `createStub(FormInterface::class)` avec `->method('get')->willReturnMap([['poReference', $poRefField]])` ou `expects(self::once())`.
3. **Mocks sans attente** : convertir en `createStub()` les doubles qui ne vérifient rien (probable pour les 2 contrôleurs de simulateur PunchOut et d'autres), après le premier run.
4. **Annotations docblock** (16 occurrences, 5 fichiers) : migrer en attributs (`#[DataProvider]`), sans attendre le run.
5. **`BadgeRegistryTest.php:32` et `RailwayTrustedHostsTest.php:25`** : `glob()` retourne `list<string>|false` et `preg_match` laisse `array{}` : tester explicitement (`assertIsArray`, `assertArrayHasKey(0, $line)`).
6. `composer.json` et `composer.lock` : déjà modifiés par la PR (aucun autre paquet à monter).

## 6. Estimation et recommandation

| Lot | Estimation |
|-----|------------|
| Assistants de test typés + 14 fichiers (58 erreurs) | 3 h |
| `with()` / mocks sans attente / annotations | 1,5 h |
| 2 à 3 passes CI (PHPStan, suite, notices de PHPUnit) | 1,5 h |
| **Total** | **~6 h = 3 points** (fourchette 2 à 4 selon ce que le premier run de la suite révèle) |

**Recommandation : monter maintenant, sans fermer la PR #54.**
- Aucun blocage de dépendance ; la montée reste compatible avec Sylius 2.3 (la décision du 2026-10-23 est respectée : Sylius ne contraint pas PHPUnit).
- Estimation de 3 points, donc le scénario « Montée possible à faible coût » s'applique : créer une story de montée pour le sprint suivant (rebaser la PR #54, y ajouter les correctifs, mesurer par CI).
- Condition de repli : si le premier run de la suite sur la branche corrigée révèle plus de 10 échecs de comportement (hors typage), classer l'estimation au-dessus de 3 points, fermer la PR #54 et régler Dependabot (`ignore: dependency-name: phpunit/phpunit, update-types: version-update:semver-major`).
- Gain attendu : modeste (versions alignées, `assertIsArray` plus honnête) ; le coût principal est le typage des tests, utile en soi.

## 7. Limites et questions ouvertes

- La suite n'a pas tourné ; section 4 = dénombrement par grep et notes de version, pas un run.
- Pas de `phpunit --migrate-configuration` ni de PHPStan local (disque et consigne).
- Les 16 annotations docblock sont à passer en revue une à une (4 fichiers vérifiés).
- Temps consommé : sous le budget de 4 h.
