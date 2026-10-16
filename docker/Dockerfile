# syntax=docker/dockerfile:1
# Demo image (ADR-0002): FrankenPHP (Caddy) serves the shop, supercronic runs the scheduled tasks.

ARG PHP_IMAGE=dunglas/frankenphp:1-php8.4-bookworm

FROM ${PHP_IMAGE} AS base
ARG TARGETARCH
ARG WKHTMLTOX_VERSION=0.12.6.1-3
ARG SUPERCRONIC_VERSION=v0.2.33
# SHA256 of the pinned downloads; supercronic matches the SHA1 of its release notes,
# wkhtmltox publishes no checksum (recorded at first download). Update them with the versions.
ARG WKHTMLTOX_SHA256_AMD64=98ba0d157b50d36f23bd0dedf4c0aa28c7b0c50fcdcdc54aa5b6bbba81a3941d
ARG WKHTMLTOX_SHA256_ARM64=b6606157b27c13e044d0abbe670301f88de4e1782afca4f9c06a5817f3e03a9c
ARG SUPERCRONIC_SHA256_AMD64=feefa310da569c81b99e1027b86b27b51e6ee9ab647747b49099645120cfc671
ARG SUPERCRONIC_SHA256_ARM64=f1f8585c66de020fef494dd636058f99949d108f569fef00016a1c8b9eb145b3
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
# Pinned (supply chain). Composer runs as root during the build only: without COMPOSER_ALLOW_SUPERUSER, recent
# versions disable plugins in non-interactive sessions, so symfony/runtime never writes vendor/autoload_runtime.php.
COPY --from=composer:2.10.3 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-demo.ini

FROM base AS vendor
COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress

FROM node:22-bookworm-slim AS assets
WORKDIR /app
COPY --from=vendor /app/vendor vendor
COPY package.json yarn.lock webpack.config.mjs .babelrc ./
COPY assets assets
RUN yarn install --frozen-lockfile --network-timeout 600000 && yarn build:prod

FROM base AS final
ENV APP_ENV=prod APP_DEBUG=0 TZ=Europe/Paris
COPY --from=vendor /app/vendor vendor
COPY . .
COPY --from=assets /app/public/build public/build
RUN composer dump-autoload --classmap-authoritative --no-dev \
 && DATABASE_URL="mysql://build:build@127.0.0.1:3306/build?serverVersion=8.4" php bin/console cache:warmup \
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
