# Tâches : US-193 : Extrait réel du fichier plat sur `/echange-fichier`

## Informations US

- **Epic** : EPIC-14
- **Persona** : lead développeur d'une agence
- **Story Points** : 2 (Should, sacrifice : **n°2, validé par Thibaut**)
- **Sprint** : sprint-27-fiche-produit-et-erp-fictif
- **Worker proposé** : w193
- **Story** : [US-193](../../../backlog/user-stories/US-193.md)

## Résumé de la US

**En tant que** lead développeur d'une agence qui évalue la démo  
**Je veux** voir sur `/{locale}/echange-fichier` un extrait réel du fichier CSV produit par le plugin d'export en fichier plat  
**Afin de** juger le contrat sans installer le plugin

## Règles et contraintes

- Aujourd'hui `ExchangeFileSample` reconstruit l'extrait à partir des événements `order.placed` de l'outbox et, sans commande, de deux commandes fictives. US-193 remplace cela par **la lecture du dernier fichier réellement déposé** dans le répertoire d'échange (`var/erp-exchange`, paramètre `exchange_directory` du plugin).
- Les fixtures de démo passent quelques commandes (événements `order.placed` dans l'outbox) et lancent un export au chargement ; la remise à zéro nocturne produit le même état.
- Données fictives seulement (adresses `*.example`), e-mails masqués comme dans la boîte de démonstration (`MailTwigExtension::maskAddresses`) ; aucun ERP nommé ; **les tests n'écrivent jamais hors du répertoire d'échange configuré** (répertoire temporaire du test).
- Un répertoire absent, illisible, vide ou un fichier vide donne une page **200** avec un message neutre : ni erreur 500, ni chemin interne.
- Seules les lignes du canal courant sont montrées (canal choisi par cookie, ADR 0011) ; un fichier d'un autre canal donne un message neutre.
- Tests de la démo uniquement par `WORKER_ID=w193 TEST_DB_NAME=b2b_w193 TEST_CACHE_DIR=var/cache-w193 bin/run-tests` ; preuve : `php bin/proof-refresh` puis `php bin/proof-check`.
- Règles du sprint et consigne de worker : voir [README.md](./README.md) et [../consigne-worker.md](../consigne-worker.md).

## Lieu de vérification

**Production, après le déploiement automatique de `main` par Railway** (lecture seule, aucune commande Railway) : `/fr_FR/echange-fichier` et `/en_US/echange-fichier` montrent l'extrait ; pa11y WCAG2AAA ; 360 et 1280 px. Mesures consignées dans ce fichier **le jour du déploiement**. La story reste en review jusque-là.

## Fichiers principaux touchés

- `src/Demo/Erp/ExchangeFileSample.php` (remplacé par un lecteur du dernier fichier), `RecentPlacedOrders`, `DoctrineRecentPlacedOrders` (si devenus inutiles)
- `src/Demo/Controller/ErpFlatfileController.php`, `templates/demo/erp_flatfile.html.twig`, traductions de la page
- `src/Demo/DataFixtures/` (commandes de démo), `src/Demo/Command/DemoResetCommand.php` (export après le chargement)
- `tests/Functional/Demo/ErpFlatfilePageTest.php` (adapté), nouveaux tests du lecteur
- `config/demo/proof.json` (rafraîchi)

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-193-01 | [DOC] | Cadrage : `ExchangeFileSample`, `RecentPlacedOrders`, test `ErpFlatfilePageTest`, `ExportOrdersCommand` et son répertoire, fixtures et événements `order.placed` existants, `DemoResetCommand` ; plan des tests | 0,25 h | - | Claude | ✅ |
| T-193-02 | [TEST] | Tests rouges, commités seuls et cités dans la PR : dernier fichier (nominal), aucun fichier, fichier d'un autre canal, répertoire absent ou illisible (200, sans chemin), fichier vide ; aucune écriture hors du répertoire temporaire du test | 0,75 h | T-193-01 | Claude | ✅ |
| T-193-03 | [BE] | Lecteur du dernier fichier du répertoire d'échange : en-tête et trois lignes du canal courant, e-mails masqués, aucune exception remontée, aucun chemin dans la sortie ; contrôleur adapté | 1 h | T-193-02 | Claude | ✅ |
| T-193-04 | [BE] | Fixtures de démo : quelques commandes passées (événements `order.placed`) et export lancé après le chargement ; remise à zéro nocturne identique (`DemoResetCommand`) ; `*.example` seulement | 1 h | T-193-02 | Claude | ✅ |
| T-193-05 | [FE-WEB] | Gabarit `erp_flatfile.html.twig` : extrait, messages neutres (aucun fichier, canal sans ligne, fichier vide, répertoire indisponible), textes FR et EN | 0,25 h | T-193-03 | Claude | ✅ |
| T-193-06 | [TEST] | Suite ciblée par `bin/run-tests`, PHPStan max sur tout le dépôt (`src` et `tests`), ECS, Rector, lint Twig et YAML ; commande exacte et dernière ligne citées | 0,5 h | T-193-03, T-193-04, T-193-05 | Claude | ✅ |
| T-193-07 | [TEST] | Vérification en production après le déploiement (lecture seule) : extrait visible FR et EN, pa11y WCAG2AAA, 360 et 1280 px ; mesures consignées le jour du déploiement | 0,5 h | merge de la PR | Claude | 🔲 |
| T-193-08 | [REV] | Revue (ton neutre, aucun ERP nommé, e-mails masqués) et table « scénario → test ou preuve » remplie ; `php bin/proof-refresh` puis `php bin/proof-check` | 0,25 h | T-193-07 | Claude | 🔲 |

**Total estimé : 4,5 h** (8 tâches ; 4,5 h pour Claude).

## Scénarios et preuves

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, run de CI). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Extrait du dernier fichier (nominal) | `ExchangeFileSampleTest::testTheHeaderAndThreeLinesOfTheCurrentChannelOfTheLastFileAreShown`, `::testTheEmailsAreMasked` ; `ErpFlatfileSampleFixtureTest::testThePageShowsTheLinesOfTheB2bChannel` et `::testTheExportRunAtTheLoadWroteToTheExchangeDirectoryOfTheTestAndNotToVar` (fixtures et export au chargement) |
| Aucun fichier encore produit (alternatif) | `ExchangeFileSampleTest::testAnEmptyDirectoryHasNoFileYet`, `::testTheProvisionalFilesAreNeverShown` ; message : `ErpFlatfileStatusTranslationsTest` (FR et EN) |
| Fichier produit pour l'autre canal (alternatif) | `ExchangeFileSampleTest::testAFileOfTheOtherChannelGivesANeutralStatus` ; `ErpFlatfileSampleFixtureTest::testTheB2cChannelHasNoOrderAndGetsANeutralMessage` (cookie `demo_channel=B2C`) |
| Répertoire absent ou illisible (erreur) | `ExchangeFileSampleTest::testAMissingDirectoryGivesANeutralStatusWithoutPath`, `::testAnUnreadableDirectoryGivesANeutralStatus` (statut neutre, aucun chemin, aucune exception) ; `ErpFlatfileSampleFixtureTest` : aucun `erp-exchange` dans le HTML |
| Fichier vide (erreur) | `ExchangeFileSampleTest::testAnEmptyFileGivesANeutralStatus`, `::testAFileWithOnlyTheHeaderIsEmptyToo` ; aucune écriture : `::testNothingIsWrittenToTheDirectory` |

## Mesures de production (jour du déploiement)

À consigner le jour où `main` est déployé : date, URL, extrait visible, pa11y, `scrollWidth` à 360 et 1280 px.

| Page | Date | Extrait | pa11y WCAG2AAA | 360 px | 1280 px |
|------|------|---------|----------------|--------|---------|
| `/fr_FR/echange-fichier` | | | | | |
| `/en_US/echange-fichier` | | | | | |

**Preuves de l'implémentation (sprint 27)** : rouge `ea6df34`, code `e4cf42b`. `bin/run-tests` ciblé (tests/Unit/Demo/Erp, ErpFlatfilePageTest, ErpFlatfileExportTest, ErpFlatfileSampleFixtureTest, DemoResetCommandTest, FixturesLoadStateTest) : `OK, but there were issues! Tests: 39, Assertions: 292`. ECS `[OK] No errors found`, PHPStan (dépôt entier) `[OK] No errors`, Rector `[OK] Rector is done!`. Constat : les fixtures n'ont aucune commande B2C, la page B2C montre donc le message neutre « aucune commande de ce canal ». T-193-07 et T-193-08 restent ouvertes (production après déploiement).
