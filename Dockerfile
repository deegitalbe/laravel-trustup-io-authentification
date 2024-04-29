# ----------------------------------------

# We're forced to use php 8.2 till this issue is not resolved (imagick related)
# https://github.com/mlocati/docker-php-extension-installer/issues/857#issuecomment-1904396267

# Application CLI
FROM php:8.2-cli-alpine3.19 as cli

# Set current user
USER root

# Install node, chromium, shadow (usermod)
RUN apk add --update --no-cache shadow

# Host user data
ARG UID=1000
ARG GID=1000

# Match host user to avoid volumes permission issues
RUN usermod  --uid ${UID} www-data && \
    groupmod --gid ${GID} www-data

# Install composer
COPY --from=composer:2.5.8 /usr/bin/composer /usr/bin/composer

USER www-data

WORKDIR /opt/apps/app

USER www-data

COPY --chown=www-data:www-data composer.json ./

RUN composer install
