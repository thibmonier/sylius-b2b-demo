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

until php bin/console dbal:run-sql "SELECT 1" >/dev/null 2>&1; do
    echo "Waiting for the database..."
    sleep 3
done

php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
if [ "$(php bin/console dbal:run-sql 'SELECT COUNT(*) AS n FROM sylius_channel' --force-fetch 2>/dev/null | grep -Eo '[0-9]+' | tail -1)" = "0" ]; then
    echo "Empty database: loading the demo data"
    php -d memory_limit=1G bin/console sylius:fixtures:load demo --no-interaction
fi

supercronic /app/docker/crontab &
exec "$@"
