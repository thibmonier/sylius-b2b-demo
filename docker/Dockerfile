# syntax=docker/dockerfile:1
# Demo image (ADR-0002): FrankenPHP (Caddy) serves the shop, supercronic runs the scheduled tasks.

ARG PHP_IMAGE=dunglas/frankenphp:1-php8.4-bookworm

FROM ${PHP_IMAGE} AS base
ARG TARGETARCH
ARG WKHTMLTOX_VERSION=0.12.6.1-3
ARG SUPERCRONIC_VERSION=v0.2.33
WORKDIR /app
RUN install-php-extensions intl gd zip exif pdo_mysql opcache apcu bcmath \
 && apt-get update \
 && apt-get install -y --no-install-recommends ca-certificates curl fontconfig libjpeg62-turbo libxrender1 libxext6 xfonts-75dpi xfonts-base \
 && curl -fsSL -o /tmp/wkhtmltox.deb "https://github.com/wkhtmltopdf/packaging/releases/download/${WKHTMLTOX_VERSION}/wkhtmltox_${WKHTMLTOX_VERSION}.bookworm_${TARGETARCH}.deb" \
 && apt-get install -y --no-install-recommends /tmp/wkhtmltox.deb \
 && curl -fsSL -o /usr/local/bin/supercronic "https://github.com/aptible/supercronic/releases/download/${SUPERCRONIC_VERSION}/supercronic-linux-${TARGETARCH}" \
 && chmod +x /usr/local/bin/supercronic \
 && rm -rf /tmp/wkhtmltox.deb /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
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
