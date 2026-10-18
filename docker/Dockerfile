# syntax=docker/dockerfile:1
# Demo image (ADR-0002): FrankenPHP (Caddy) serves the shop, supercronic runs the scheduled tasks.

# Base images are pinned by digest (US-081); Dependabot proposes the updates (tag and digest move together).
ARG PHP_IMAGE=dunglas/frankenphp:1-php8.4-bookworm@sha256:14007e6207a57651cb402970a44e5f2df15bfeffcbdeb5bbb322271949878b07

FROM ${PHP_IMAGE} AS base
ARG TARGETARCH
ARG WKHTMLTOX_VERSION=0.12.6.1-3
ARG SUPERCRONIC_VERSION=v0.2.49
# SHA256 of the pinned downloads; supercronic matches the SHA1 of its release notes,
# wkhtmltox publishes no checksum (recorded at first download). Update them with the versions.
ARG WKHTMLTOX_SHA256_AMD64=98ba0d157b50d36f23bd0dedf4c0aa28c7b0c50fcdcdc54aa5b6bbba81a3941d
ARG WKHTMLTOX_SHA256_ARM64=b6606157b27c13e044d0abbe670301f88de4e1782afca4f9c06a5817f3e03a9c
ARG SUPERCRONIC_SHA256_AMD64=a53ae236602c7338aba3fbaff40bda6300eae3b9fedb8261eb06cfe3724430c1
ARG SUPERCRONIC_SHA256_ARM64=02aa0cb229ba09050cba6638059dadb9eedc2276632ea43d6a57a2f8c1629dd5
WORKDIR /app
RUN install-php-extensions intl gd zip exif pdo_mysql opcache apcu bcmath \
 && apt-get update \
 && apt-get install -y --no-install-recommends ca-certificates curl fontconfig libjpeg62-turbo libxrender1 libxext6 xfonts-75dpi xfonts-base \
 && case "${TARGETARCH}" in \
      amd64) WKHTMLTOX_SHA256="${WKHTMLTOX_SHA256_AMD64}"; SUPERCRONIC_SHA256="${SUPERCRONIC_SHA256_AMD64}" ;; \
      arm64) WKHTMLTOX_SHA256="${WKHTMLTOX_SHA256_ARM64}"; SUPERCRONIC_SHA256="${SUPERCRONIC_SHA256_ARM64}" ;; \
      *) echo "Unsupported architecture: ${TARGETARCH}" >&2; exit 1 ;; \
    esac \
 && curl -fsSL -o /tmp/wkhtmltox.deb "https://github.com/wkhtmltopdf/packaging/releases/download/${WKHTMLTOX_VERSION}/wkhtmltox_${WKHTMLTOX_VERSION}.bookworm_${TARGETARCH}.deb" \
 && echo "${WKHTMLTOX_SHA256}  /tmp/wkhtmltox.deb" | sha256sum -c - \
 && apt-get install -y --no-install-recommends /tmp/wkhtmltox.deb \
 && curl -fsSL -o /usr/local/bin/supercronic "https://github.com/aptible/supercronic/releases/download/${SUPERCRONIC_VERSION}/supercronic-linux-${TARGETARCH}" \
 && echo "${SUPERCRONIC_SHA256}  /usr/local/bin/supercronic" | sha256sum -c - \
 && chmod +x /usr/local/bin/supercronic \
 && rm -rf /tmp/wkhtmltox.deb /var/lib/apt/lists/*
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-demo.ini

# Composer lives in this stage and the next one only: the final image does not ship it (US-082). Pinned (supply chain).
# It runs as root during the build: without COMPOSER_ALLOW_SUPERUSER, recent versions disable plugins in
# non-interactive sessions, so symfony/runtime never writes vendor/autoload_runtime.php.
FROM base AS vendor
COPY --from=composer:2.10.3@sha256:af98f42dfff7c68ba8d53c2164fd9fde1087b7d449514baa38c418b1f6bc4bac /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress

FROM vendor AS autoload
COPY . .
RUN composer dump-autoload --classmap-authoritative --no-dev

FROM node:26-bookworm-slim@sha256:298992629d69bbd5b2b03ad57094d8e4fcb3a8eeb7c4e25d29de656519568913 AS assets
WORKDIR /app
COPY --from=vendor /app/vendor vendor
COPY package.json yarn.lock webpack.config.mjs .babelrc ./
COPY assets assets
RUN yarn install --frozen-lockfile --network-timeout 600000 && yarn build:prod

FROM base AS final
ENV APP_ENV=prod APP_DEBUG=0 TZ=Europe/Paris
COPY --from=autoload /app/vendor vendor
COPY . .
COPY --from=assets /app/public/build public/build
RUN DATABASE_URL="mysql://build:build@127.0.0.1:3306/build?serverVersion=8.4" php bin/console cache:warmup \
 && DATABASE_URL="mysql://build:build@127.0.0.1:3306/build?serverVersion=8.4" php bin/console assets:install public \
 && mkdir -p var public/media private config/jwt \
 && chown -R www-data:www-data var public/media private config/jwt /config/caddy /data/caddy \
 && chmod +x docker/entrypoint.sh
COPY docker/Caddyfile /etc/frankenphp/Caddyfile
USER www-data
EXPOSE 8080
HEALTHCHECK --interval=30s --timeout=5s --start-period=120s CMD curl -fsS "http://127.0.0.1:${PORT:-8080}/healthz" || exit 1
ENTRYPOINT ["/app/docker/entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
