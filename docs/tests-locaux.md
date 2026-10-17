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

## Pièges connus

- **Répertoire des factures partagé** : `private/invoices/` est commun à toutes les bases de la copie de travail, et `demo_orders` y supprime les PDF au chargement. Deux chargements en parallèle sur la même copie se marchent dessus (test Factur-X rouge sans raison). Un chargement à la fois.
- **Cache de test périmé** : après un `git pull` ou un changement de service, un `sylius:fixtures:load` lancé à la main peut échouer sur un constructeur ou un service inconnu : `bin/console cache:clear` en `APP_ENV=test` (le script le fait).
- **Base partagée** : plusieurs copies de travail peuvent utiliser le même MySQL ; leur donner chacune sa `TEST_DB_NAME` et son `TEST_CACHE_DIR`.
- Les tests qui lancent `app:demo:reset` rechargent la suite entière dans la base (elle en ressort identique).

## Profiler le reset

```bash
TEST_DB_NAME=ma_base bin/profile-reset 3
```

Requêtes SQL et durée de chaque fixture, sur 3 passes ; résultats et objectifs : [remise-a-zero-demo.md](./remise-a-zero-demo.md).
