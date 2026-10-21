# Tests en local : préparer la base de test (US-048)

Procédure du 2026-10-17. Une commande crée, migre et charge la base de test ; les tests de `tests/Functional` et `tests/Smoke` tournent contre cette base chargée avec la suite de fixtures `demo`.

## Prérequis

- PHP 8.5 en local avec les extensions de l'image (dont `intl`), dépendances installées (`composer install`).
- MySQL 8.4 de `compose.yaml`, port `127.0.0.1:3340` : `docker compose up -d --wait mysql` (ou `bin/prepare-test-db --start-mysql`).

## Une commande

```bash
bin/prepare-test-db
```

Ce que fait le script (`bin/prepare-test-db`) :

1. vide le cache de test (le conteneur n'est pas reconstruit quand le code change : `APP_DEBUG=0`) ;
2. crée la base `sylius_b2b_demo_test` si elle n'existe pas ;
3. applique les migrations ;
4. charge les fixtures `demo`, qui vident d'abord la base.

Il pose seul les réglages que les tests exigent : `APP_ENV=test`, `php -d memory_limit=-1` (le conteneur compilé et les fixtures dépassent les 128 Mo par défaut : sans cela, l'erreur est « Allowed memory size exhausted » sans autre message) et `WKHTMLTOPDF_BINARY` vers un faux binaire versionné. Il est **rejouable** : un deuxième lancement recharge la même base. Il **refuse** la base de développement `sylius_b2b_demo`.

Variables (toutes facultatives) :

| Variable | Rôle | Défaut |
|---|---|---|
| `TEST_DB_NAME` | Nom de la base (lettres, chiffres, `_`) | `sylius_b2b_demo_test` |
| `TEST_DATABASE_URL` | URL complète si la base n'est pas celle de `compose.yaml` | `mysql://root@127.0.0.1:${MYSQL_PORT:-3340}/<base>` |
| `TEST_CACHE_DIR` | Répertoire de cache (`APP_CACHE_DIR`), pour ne pas se marcher dessus entre deux copies de travail | `var/cache` |
| `MYSQL_PORT` | Port MySQL local | `3340` |

Le script termine en affichant la ligne de commande des tests avec les mêmes variables.

## Une commande pour tout : `bin/run-tests`

```bash
bin/run-tests                                   # suite complète
bin/run-tests tests/Functional --filter Quote   # les arguments vont à PHPUnit
```

`bin/run-tests` appelle `bin/prepare-test-db` puis PHPUnit avec le même environnement. Il règle le problème du cache de test périmé (conteneur compilé avant un changement de constructeur, faux échecs de service inconnu) :

- chaque exécution a **son propre cache**, créé vide dans un dossier temporaire (`mktemp`), et **supprimé à la fin** (aussi sur Ctrl-C ou en cas d'échec) : rien n'est écrit dans le vrai `var/cache` ;
- journaux (`APP_LOG_DIR`), pools partagés (`APP_SHARE_DIR`), compteurs des limiteurs (`demo-limiter-test`) et images de test (`media-test`) vivent dans ce même dossier : plus rien de test dans le vrai `var/`, et deux exécutions parallèles ne vident plus les compteurs l'une de l'autre (faux échec de `PublicOpeningHardeningTest`, constaté le 2026-10-17) ;
- `TEST_CACHE_DIR=var/cache-mon-nom` impose un dossier : il est vidé avant et supprimé après ; le script refuse `var/cache`, `var/` et la racine du dépôt ;
- **seuil disque** : sous 5 Go libres (`MIN_FREE_GB`, 5 par défaut), il s'arrête avec un message avant de créer quoi que ce soit ;
- la base reste `sylius_b2b_demo_test` (ou `TEST_DB_NAME`) ; la base de développement `sylius_b2b_demo` est refusée ;
- le code de sortie est celui de PHPUnit ;
- **verrou** (`var/run-tests.lock`, ou `RUN_TESTS_LOCK=`) : `bin/run-tests` crée à l'entrée un répertoire de verrou (`mkdir`, atomique) qui contient son PID, et le retire à la sortie, y compris sur échec, Ctrl-C ou `kill`. Un second run lancé pendant le premier s'arrête aussitôt (code de sortie **4**, message « already running » avec le PID). Un verrou dont le processus n'existe plus (`kill -0`) est périmé : il est repris avec un message. Pour attendre la fin d'un run, attendre la disparition de ce répertoire, **jamais** `pgrep -f bin/run-tests` (la recherche se détecte elle-même). `bin/clean-test-caches` s'appuie sur le même verrou. Test : `tests/Unit/Bin/RunTestsLockTest.php`.

## Lancer les tests

```bash
APP_ENV=test DATABASE_URL='mysql://root@127.0.0.1:3340/sylius_b2b_demo_test?serverVersion=8.4&charset=utf8mb4' \
WKHTMLTOPDF_BINARY="$PWD/tests/Fixtures/bin/fake-wkhtmltopdf" \
php -d memory_limit=-1 vendor/bin/phpunit
```

`phpunit.xml.dist` ne fixe ni la base ni le binaire : sans ces variables, les tests visent la base de `.env.test` et le vrai `wkhtmltopdf`. Qualité :

```bash
vendor/bin/ecs check src tests
vendor/bin/phpstan analyse -c phpstan.dist.neon --no-progress --memory-limit=1G
```

## Le faux wkhtmltopdf et l'assertion Factur-X

`tests/Fixtures/bin/fake-wkhtmltopdf` copie `blank.pdf` (une page vierge valide) vers le fichier de sortie : ni moteur de navigateur, ni bibliothèque système. Le plugin Factur-X embarque `factur-x.xml` dans ce PDF au moment de la génération de la facture, donc **l'assertion Factur-X d'`OrderHistorySmokeTest` passe en local** (décision du 2026-10-17 ; l'ancien faux binaire de 14 octets la faisait échouer, le PDF n'étant pas lisible par le plugin). Elle n'est pas exclue.

Les PDF des factures sont écrits au chargement des fixtures : après un changement de faux binaire, **recharger la base** (`bin/prepare-test-db`).

## Comptes de démo partagés : isolation des tests

Les comptes de la démo (panier de Claire, panier de Paul, listes partagées d'Acme…) sont communs à tous les tests, qui tournent contre la même base. **Un test qui modifie un compte partagé l'isole ou annule ses effets**, sinon les tests qui suivent dépendent de l'ordre.

- Isolation par transaction : `use App\Tests\Support\IsolatesSharedAccounts;` puis `$this->isolateSharedAccounts($client)` juste après `createClient()`. Le client garde un seul noyau (`disableReboot()`), une transaction s'ouvre et le `tearDown` l'annule (modèle : `ContainerFillDemoTest`, `PunchOutSimulatorSmokeTest`).
- Un test qui ne peut pas passer en transaction annule lui-même ses effets en fin de test.
- Les assertions sur les données vivantes sont **strictes** (quantités exactes, un seul panier) : ne pas les assouplir (« au moins ») pour absorber le désordre d'un autre test, corriger le test qui salit.
- Contrôle : la suite doit rester verte en ordre aléatoire, `bin/run-tests --order-by=random --random-order-seed=<graine>` (deux graines différentes avant une revue).

## Pièges connus

- **Répertoire des factures partagé** : `private/invoices/` est commun à toutes les bases de la copie de travail, et `demo_orders` y supprime les PDF au chargement. Deux chargements en parallèle sur la même copie se marchent dessus (test Factur-X rouge sans raison). Un chargement à la fois.
- **Cache de test périmé** : après un `git pull` ou un changement de service, un `sylius:fixtures:load` lancé à la main peut échouer sur un constructeur ou un service inconnu : `bin/console cache:clear` en `APP_ENV=test` (le script le fait).
- **Base partagée** : plusieurs copies de travail peuvent utiliser le même MySQL ; leur donner chacune sa `TEST_DB_NAME` et son `TEST_CACHE_DIR`.
- Les tests qui lancent `app:demo:reset` rechargent la suite entière dans la base (elle en ressort identique).

## Libérer le disque : `bin/clean-test-caches`

Chaque exécution laisse des caches (`var/cache-*`), des bases de test (`sylius_test_*`, `b2b_*`, `sylius_b2b_demo_test_*`) et des journaux binaires MySQL (plusieurs centaines de Mo). `bin/run-tests` refuse de démarrer sous `MIN_FREE_GB` (5 Go) : **ce seuil n'est jamais abaissé** ; on libère de l'espace, ou on demande de l'aide.

```bash
df -h .                         # contrôle au démarrage de la session
bin/clean-test-caches --dry-run # liste ce qui serait supprimé, ne supprime rien
bin/clean-test-caches           # supprime, purge les journaux binaires, affiche df -h . avant et après
```

La base par défaut (`sylius_b2b_demo_test`) et la base de développement (`sylius_b2b_demo`) sont conservées. Le script supprime aussi les bases d'une autre session en cours : ne le lancer que lorsque personne d'autre n'utilise le MySQL local. Conteneur visé : `sylius-b2b-demo-mysql-1` (`MYSQL_CONTAINER=` pour un autre). Test : `tests/Unit/Bin/CleanTestCachesTest.php`.

## Mesure mobile à froid : `bin/measure-mobile`

Le script a besoin de Puppeteer et d'un Chromium, que l'on indique par deux variables :

| Variable | Rôle | Défaut |
|----------|------|--------|
| `PUPPETEER_MODULE` | chemin du module `puppeteer` s'il n'est pas installé à côté du projet | `puppeteer` |
| `PUPPETEER_EXECUTABLE_PATH` | binaire Chromium / Chrome à lancer | celui téléchargé par Puppeteer |

```bash
docker compose up -d --wait            # la démo répond sur http://localhost:8080 (mode démo actif : connexion en un clic)
PUPPETEER_MODULE="$HOME/outils/node_modules/puppeteer" \
PUPPETEER_EXECUTABLE_PATH="/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" \
bin/measure-mobile --targets
```

Sans module : code de sortie 2 et le message `puppeteer not found`. Code 1 : une page est KO (la ligne nomme la page et les éléments trop larges).

## Profiler le reset

```bash
TEST_DB_NAME=ma_base bin/profile-reset 3
```

Requêtes SQL et durée de chaque fixture, sur 3 passes ; résultats et objectifs : [remise-a-zero-demo.md](./remise-a-zero-demo.md).
