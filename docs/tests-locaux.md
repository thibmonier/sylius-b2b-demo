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
- **seuil disque** : sous 5 Go libres (`MIN_FREE_GB`, 5 par défaut), il s'arrête avec un message avant de créer quoi que ce soit ; ce seuil n'est jamais abaissé. Avant, il appelle `bin/check-disk` (voir plus bas), qui affiche l'espace libre et alerte à 15 Go  ;
- **suite complète** : un lancement sans chemin ni filtre (`--filter`, `--testsuite`, `--group`) est la suite complète ; il exige 6 Go libres (`FULL_SUITE_MIN_GB`, 6 par défaut) et sinon s'arrête avant de créer quoi que ce soit. Les lancements ciblés gardent le seuil de 5 Go. Pour essayer le refus sans toucher au disque : `FULL_SUITE_MIN_GB=999 bin/run-tests` ;
- la base reste `sylius_b2b_demo_test` (ou `TEST_DB_NAME`) ; la base de développement `sylius_b2b_demo` est refusée ;
- le code de sortie est celui de PHPUnit ;
- **verrou** (`var/run-tests.lock`, ou `RUN_TESTS_LOCK=`) : `bin/run-tests` crée à l'entrée un répertoire de verrou (`mkdir`, atomique) qui contient son PID, et le retire à la sortie, y compris sur échec, Ctrl-C ou `kill`. Un second run lancé pendant le premier s'arrête aussitôt (code de sortie **4**, message « already running » avec le PID). Un verrou dont le processus n'existe plus (`kill -0`) est périmé : il est repris **de façon atomique** (le répertoire est renommé vers un nom propre au run, puis recréé par `mkdir` ; un seul des deux runs concurrents l'obtient, l'autre sort avec le code 4) avec un message et une ligne dans `var/run-tests.log`. Le verrou contient aussi `WORKER_ID` quand il est défini (fichier `worker`). `bin/clean-test-caches` s'appuie sur le même verrou. Tests : `tests/Unit/Bin/RunTestsLockTest.php` (reprise concurrente rejouée 20 fois, attente, délai).

### Attendre la fin d'un run : `--wait`

```bash
bin/run-tests --wait                              # sort 0 dès que plus aucun run vivant ne tient le verrou
RUN_TESTS_WAIT_TIMEOUT=600 bin/run-tests --wait   # délai en secondes (défaut 1800 = 30 min)
```

`--wait` ne lance aucun test : il lit le verrou (jamais `pgrep -f bin/run-tests`, la recherche se détecte elle-même), sort aussitôt avec 0 si le verrou est absent ou périmé, et sort avec le code **5** et un rapport (PID, verrou) quand le délai est dépassé. L'intervalle de lecture est `RUN_TESTS_WAIT_INTERVAL` (5 s). Un worker qui attend un run n'attend jamais plus de 30 minutes : il sort avec ce rapport.

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
- **Lecture filtrée par RTK** : le proxy RTK réécrit `cat`, `sed`, `ls` et masque les commentaires et les docblocks. Pour lire un fichier à modifier : outil Read ou `rtk proxy cat`.
- **`rm` aliasé en `rm -i`** : dans une commande de fond, il attend une confirmation qui ne vient jamais. Utiliser `/bin/rm` sur ses propres fichiers.
- **`sed` de macOS** : l'alternance (`a|b`) exige `sed -E` ; en expression de base, `\|` ne fait rien, sans erreur.
- **Sortie de test coupée** : ne pas filtrer la sortie de `bin/run-tests` par `head` ou `sed -n 1,N` (on perd le message d'erreur) ; l'écrire dans un fichier, puis la filtrer.
- **Erreur 500 dans un test fonctionnel** : `$client->catchExceptions(false)` dans un test jetable fait remonter l'exception et sa pile dans la sortie de PHPUnit.
- **Twig et méthodes statiques** : `objet.verified` appelle une méthode statique `verified()` si elle existe, avant `isVerified()` ; écrire `objet.isVerified()`.

## Plusieurs workers en parallèle : une identité, une base, un cache

Chaque worker (session Claude, copie de travail) a **son identifiant** `WORKER_ID` (lettres et chiffres, sans `_`), **sa base** (`TEST_DB_NAME=b2b_<worker>`, ou `sylius_test_<worker>`), **son cache** (`TEST_CACHE_DIR=var/cache-<worker>`) et **son sous-dossier de scratchpad** (`<scratchpad>/<worker>/`, jamais de fichier de travail à la racine). Le gabarit de consigne est dans [project-management/templates/consigne-worker.md](../project-management/templates/consigne-worker.md).

```bash
WORKER_ID=wf TEST_DB_NAME=b2b_wf TEST_CACHE_DIR=var/cache-wf bin/run-tests --filter Quote
WORKER_ID=wf bin/clean-test-caches      # ne touche que var/cache-wf*, b2b_wf*, sylius_test_wf*
```

Les tests restent lancés **un à la fois** (verrou) ; le nettoyage, lui, est limité à ce qui porte l'identifiant du worker.

## Contrôler le disque : `bin/check-disk`

```bash
bin/check-disk                        # espace libre ; alerte sous 15 Go (CHECK_DISK_WARN_GB)
bin/check-disk --full                 # pour la suite complète : gelé sous 6 Go (FULL_SUITE_MIN_GB), sortie 1
```

Le script affiche l'espace libre, la **mémoire libre** (pages libres et inactives) et le **swap** utilisé (un swap qui dépasse la moitié de l'espace libre est le signe de quitter des applications avant un lot). Sous le seuil d'alerte, il demande de **prévenir Thibaut sans attendre**, chiffre l'empreinte du projet (caches `var/cache-*`, bases de test du conteneur MySQL ; les volumes Docker se lisent avec `docker system df`) et dit si le manque vient du **projet** (ses caches et ses bases couvrent l'espace manquant : nettoyer ses propres ressources) ou de la **machine** (l'espace est utilisé hors du projet : nettoyer le projet ne suffira pas). Codes de sortie : 0 assez de place, 3 alerte, 1 gel (sous `MIN_FREE_GB`). `bin/run-tests` l'appelle à chaque lancement, pour l'affichage seulement : son gel à 5 Go reste le seul blocage. Test : `tests/Unit/Bin/CheckDiskTest.php`.

## Libérer le disque : `bin/clean-test-caches`

Chaque exécution laisse des caches (`var/cache-*`), des bases de test (`sylius_test_*`, `b2b_*`, `sylius_b2b_demo_test_*`) et des journaux binaires MySQL (plusieurs centaines de Mo). `bin/run-tests` refuse de démarrer sous `MIN_FREE_GB` (5 Go) : **ce seuil n'est jamais abaissé** ; on libère de l'espace, ou on demande de l'aide.

```bash
df -h .                                        # contrôle au démarrage de la session et avant chaque lot
WORKER_ID=wf bin/clean-test-caches --dry-run   # liste ce que le worker wf supprimerait, ne supprime rien
WORKER_ID=wf bin/clean-test-caches             # supprime ses caches et ses bases, purge les journaux binaires, df -h . avant et après
bin/clean-test-caches --all                    # orchestrateur seul, hors lots : toutes les ressources de tous les workers
```

Sans `WORKER_ID` ni `--all`, le script refuse (code 2) ; `--all` avec `WORKER_ID` défini est refusé (code 5) : un worker ne nettoie jamais pour les autres. Un worker est aussi refusé (code 3) tant que **son propre** run tient le verrou. La base par défaut (`sylius_b2b_demo_test`) et la base de développement (`sylius_b2b_demo`) sont conservées. `--all` supprime les bases de tous les workers : ne le lancer que lorsque personne d'autre n'utilise le MySQL local. Conteneur visé : `sylius-b2b-demo-mysql-1` (`MYSQL_CONTAINER=` pour un autre). Test : `tests/Unit/Bin/CleanTestCachesTest.php`.

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

## Rector et seuil de couverture dans la CI (US-084)

- **Rector** : `composer rector-dry-run` doit afficher 0 fichier modifiable (la CI l'exécute après ECS et PHPStan et échoue sinon) ; `composer rector-apply` applique. `rector.php` garde les classes globales en nom pleinement qualifié (`\DateTimeImmutable`), comme l'impose ECS, et exclut `ParamAndEnvAttributeRector` pour deux `#[Autowire('/%sylius_admin.path_name%')]` dont le préfixe `/` serait perdu.
- **Couverture** : pcov n'existe qu'en CI. `bin/check-coverage build/coverage.xml 90` lit le Clover et échoue sous le seuil (`COVERAGE_MIN_LINES` dans `.github/workflows/ci.yaml`, 90 % de lignes pour 92,73 % mesurés sur `main` le 2026-10-23). Le taux et le seuil sont dans le résumé du job. Remonter le seuil avec le taux, ne jamais l'abaisser pour faire passer un build.

## Preuve publiée à jour : `bin/proof-check` (US-168)

`php bin/proof-check` dit si `config/demo/proof.json` est en retard sur le dépôt (fichiers de tests de la démo, liste des plugins), sans réseau et sans rien écrire. Si un test est ajouté ou supprimé dans la PR, lancer `php bin/proof-refresh` (réseau, via `gh`) puis committer `config/demo/proof.json` : le contrôle repasse au vert. En CI, l'écart est affiché dans le résumé du job, sans faire échouer une PR ; il fait échouer le build de `main`. Détails : [theme-b2b.md](theme-b2b.md), « Bloc de preuve technique ».

## Serveur local en mode prod et rendu des pages (T-TECH-27-04 et T-TECH-27-05)

**Le 503 « the demo is not configured safely » sur un serveur local venait de `variables_order`.** Le `php.ini` du poste (celui de PHP en ligne de commande, `php -i | grep variables_order`) a `variables_order = GPCS` : le serveur intégré de PHP ne met pas l'environnement du shell dans `$_SERVER`. Dotenv retombe alors sur `.env.local` (`APP_ENV=dev`, un `APP_SECRET` court) et `DemoConfigurationGuard` refuse de servir (« APP_SECRET must be at least 32 characters long »). Les commandes `bin/console` et le rendu en ligne de commande n'ont pas ce défaut : le CLI remplit `$_SERVER`. Le correctif est `-d variables_order=EGPCS`, que fait `bin/local-server`. Trouvé en faisant afficher à un routeur de débogage ce que le garde voit dans le processus du serveur (`APP_ENV='dev'`, violations non vides) puis, avec `EGPCS`, `APP_ENV='prod'` et aucune violation.

**Jeu minimal de variables** (à exporter dans le shell, jamais dans un fichier du dépôt) :

| Variable | Valeur locale | Pourquoi |
|----------|---------------|----------|
| `APP_ENV`, `APP_DEBUG` | `prod`, `0` | le rendu de la production (`test` ajoute les attributs `data-test-*`) |
| `APP_SECRET` | 32 caractères au moins (`openssl rand -hex 24`) | garde de la démo |
| `DATABASE_URL` | une base du MySQL local qui contient les données de démonstration et les migrations à jour | sinon `/healthz` répond 503 « migrations pending » (`bin/console doctrine:migrations:migrate`) |
| `APP_CACHE_DIR` | **un seul** dossier hors du dépôt, réutilisé (150 Mo) | pas un dossier par rendu |
| `DEMO_MODE`, `DEMO_BASIC_AUTH` | `1`, `0` | démo ouverte, sans mot de passe |
| `DEMO_REGISTRATION_OPEN`, `DEMO_PUNCHOUT_PUBLIC_ENDPOINT` | `0`, `0` | exigé par `app:demo:preflight` quand la barrière est ouverte |
| `TRUSTED_HOSTS` | `'^127\.0\.0\.1$'` | refus d'un motif qui accepte tout hôte |
| `MAILER_DSN` | `null://null` (ou le Mailpit local) | aucun e-mail réel |
| `DEFAULT_URI`, `WKHTMLTOPDF_BINARY` | `http://127.0.0.1:<port>`, `tests/Fixtures/bin/fake-wkhtmltopdf` | liens absolus, PDF factices |

- **Démarrer** : `bin/local-server [PORT]` (127.0.0.1 seulement, 8095 par défaut). Il lance d'abord `app:demo:preflight`, qui liste ce qui manque, puis le serveur avec son routeur `bin/local-router.php` (les fichiers de `public/` sont servis tels quels, le reste passe par `public/index.php`). `bin/measure-mobile` se lance ensuite sur cette adresse.
- **Rendre des pages sans serveur** : `bin/render-page --out=DIR [--channel=B2B|B2C] [--login=EMAIL] PATH...` écrit un fichier HTML par page ; les vignettes des produits sont générées et liées en `/media/cache/...`. Pour les afficher ou les photographier : un dossier avec des liens symboliques vers `public/build` et `public/media`, les fichiers HTML, et `python3 -m http.server`. `--login` exige `APP_ENV=test` (le client de test ouvre la session ; les pages portent alors les attributs `data-test-*`).
- **Pièges** : `curl` est un alias de `curlie` dans le shell de travail, qui répond des `405` parasites avec `-o /dev/null -w` : utiliser `/usr/bin/curl`. La première requête d'un cache vide compile le conteneur (quelques dizaines de secondes). `/healthz` répond 503 tant que la base a une migration en attente.
- **À dire dans une PR** quand les captures viennent de ce rendu : « rendu du noyau en ligne de commande, `APP_ENV=prod` » (ou `test` avec `--login`).

## Vérifier le rendu en production (lecture seule)

Quand l'image de la démo ne peut pas être construite en local (moins de 10 Go libres), le rendu d'une story d'interface se vérifie après le déploiement, sur la page publique, sans se connecter ; la story reste en review jusque-là (action 4 de la rétro du sprint 23).

- **pa11y** : installé dans un dossier jetable sans son Chromium (`PUPPETEER_SKIP_DOWNLOAD=1 npm install pa11y@9`), lancé avec `--config` pointant `chromeLaunchConfig.executablePath` vers un Chromium local et `"standard": "WCAG2AAA"`. Attendu : « No issues found! » en FR et en EN.
- **360 px** : script `puppeteer-core` (viewport 360, `isMobile`), qui compare `document.documentElement.scrollWidth` à la largeur visible et liste les éléments qui dépassent. Équivalent de `bin/measure-mobile`, qui vise la démo locale.
