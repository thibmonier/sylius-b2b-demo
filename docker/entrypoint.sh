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
# US-048: the fixtures are loaded unless a finished load left its marker (exit code 0). Testing "the channel table is
# not empty" took a first load killed half-way for a finished one. Exit code 3 means "to load"; any other non-zero
# code is an error (database unreachable...) and stops the start, because the load purges the database first.
fixtures_state=0
php bin/console app:demo:fixtures-loaded --no-interaction || fixtures_state=$?
case "$fixtures_state" in
    0) ;;
    3)
        echo "Demo data missing or incomplete: loading it"
        php -d memory_limit=1G bin/console sylius:fixtures:load demo --no-interaction
        ;;
    *)
        echo "Cannot tell whether the demo data is loaded (exit code $fixtures_state)" >&2
        exit "$fixtures_state"
        ;;
esac

# Opt-in worker mode (ADR-0013, off by default): the kernel stays booted between requests. symfony/runtime
# detects the worker thread by itself and loops on frankenphp_handle_request() in public/index.php.
# The directive is spliced into the Caddyfile as raw text: it is only ever built here, never taken from the environment.
unset FRANKENPHP_WORKER_DIRECTIVE
if [ "${FRANKENPHP_WORKER_MODE:-0}" = "1" ]; then
    case "${FRANKENPHP_WORKERS:-4}" in
        ''|*[!0-9]*) echo "FRANKENPHP_WORKERS must be a positive integer" >&2; exit 1 ;;
    esac
    FRANKENPHP_WORKER_DIRECTIVE="worker /app/public/index.php ${FRANKENPHP_WORKERS:-4}"
    export FRANKENPHP_WORKER_DIRECTIVE
fi

supercronic -split-logs /app/docker/crontab &
exec "$@"
