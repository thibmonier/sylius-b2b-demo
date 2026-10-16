#!/bin/sh
# Starts the demo: waits for the database, installs it on the first start, runs the scheduled tasks
# (supercronic) next to FrankenPHP.
set -e
cd /app

# Secrets come from the platform (Railway variables). Without them (local run), ephemeral values are
# generated: sessions and API tokens do not survive a restart, which suits a demo.
if [ -z "${APP_SECRET:-}" ]; then
    APP_SECRET="$(php -r 'echo bin2hex(random_bytes(32));')"
    export APP_SECRET
fi
php bin/console lexik:jwt:generate-keypair --skip-if-exists --no-interaction >/dev/null

# Fail closed (US-032 / US-036): no start with the private gate on but without credentials, or with a real mailer.
php bin/console app:demo:preflight --no-interaction

until php bin/console dbal:run-sql "SELECT 1" >/dev/null 2>&1; do
    echo "Waiting for the database..."
    sleep 3
done

php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
if [ "$(php bin/console dbal:run-sql 'SELECT COUNT(*) AS n FROM sylius_channel' --force-fetch 2>/dev/null | grep -Eo '[0-9]+' | tail -1)" = "0" ]; then
    echo "Empty database: loading the demo data"
    php -d memory_limit=1G bin/console sylius:fixtures:load demo --no-interaction
fi

# Opt-in worker mode (ADR-0013, off by default): the kernel stays booted between requests. symfony/runtime
# detects the worker thread by itself and loops on frankenphp_handle_request() in public/index.php.
if [ "${FRANKENPHP_WORKER_MODE:-0}" = "1" ]; then
    FRANKENPHP_WORKER_DIRECTIVE="worker /app/public/index.php ${FRANKENPHP_WORKERS:-4}"
    export FRANKENPHP_WORKER_DIRECTIVE
fi

supercronic -split-logs /app/docker/crontab &
exec "$@"
