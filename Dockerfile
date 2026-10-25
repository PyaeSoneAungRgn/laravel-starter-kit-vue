# syntax=docker/dockerfile:1

FROM serversideup/php:8.5-frankenphp

# Install Node.js 22.x
USER root
RUN apt-get update && apt-get install -y curl \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# The app runs as www-data, and these images are unprivileged, so the UID/GID
# has to be baked in at build time. Build with the IDs of the user that owns
# this checkout on the host:
#
#   PUID=$(id -u) PGID=$(id -g) docker compose build
#
# storage/logs is bind-mounted from the host (see compose.yml), and a mount
# keeps the host directory's owner, so a mismatched UID means Laravel cannot
# write laravel.log. Dockerfile.cli takes the same arguments: the workers must
# resolve to the same IDs or they cannot append to the log the web server owns.
ARG PUID=33
ARG PGID=33
RUN docker-php-serversideup-set-id www-data ${PUID}:${PGID} \
    && docker-php-serversideup-set-file-permissions --owner ${PUID}:${PGID} --service frankenphp

# Dependencies are installed as root so the BuildKit cache mounts below can use
# their default (root-owned) permissions; each step chowns its result back to
# www-data in the same layer. COMPOSER_ALLOW_SUPERUSER already defaults to 1 in
# this image.

# COPY NPM DEPENDENCY FILES & INSTALL
#
# This runs before app-modules is copied on purpose: npm does not depend on the
# modules, so a module-only change keeps this layer cached instead of
# re-downloading the whole dependency tree on every deploy. HUSKY=0 keeps the
# `prepare` hook from failing now that .git is not part of the build context.
COPY package.json package-lock.json* ./
RUN --mount=type=cache,target=/tmp/npm-cache,sharing=locked \
    HUSKY=0 npm ci --cache /tmp/npm-cache --prefer-offline --no-audit --no-fund \
    && chown -R www-data:www-data node_modules

# Copy root composer files
COPY composer.json composer.lock ./

# Copy the entire app-modules directory so Composer can find all local paths
COPY app-modules/ ./app-modules/

# Install dependencies (Composer will successfully link your modules now)
RUN --mount=type=cache,target=/tmp/composer-cache,sharing=locked \
    COMPOSER_CACHE_DIR=/tmp/composer-cache \
    composer install --no-dev --no-autoloader --no-scripts \
    && chown -R www-data:www-data vendor

# Copy the rest of the application source code
USER www-data
COPY --chown=www-data:www-data . .

# FrankenPHP's worker bootstrap is gitignored upstream and no install step ran
# in the image, so generate it from the framework stub. Without this file the
# Caddyfile that Octane generates cannot boot.
RUN cp vendor/laravel/octane/src/Commands/stubs/frankenphp-worker.php \
       public/frankenphp-worker.php

# RUN NPM BUILD
RUN npm run build

# Finalize autoloader and run framework post-install tasks.
#
# Not --classmap-authoritative: application source is bind-mounted at deploy
# time, so a class added by `git pull` must still resolve through PSR-4 without
# an image rebuild.
RUN composer dump-autoload --no-dev -o

EXPOSE 8000
EXPOSE 8080
