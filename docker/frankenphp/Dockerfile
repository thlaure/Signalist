FROM node:24-alpine AS frontend
WORKDIR /build/frontend
COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci --ignore-scripts
COPY frontend/ ./
RUN npm run build

FROM dunglas/frankenphp:1-php8.5-alpine

# Install mlocati/docker-php-extension-installer for easier extension management
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Install system dependencies and PHP extensions
# hadolint ignore=DL3018
RUN apk add --no-cache \
    git \
    curl \
    zip \
    unzip \
    nodejs \
    npm \
    && install-php-extensions \
    pdo_pgsql \
    intl \
    opcache \
    zip \
    bcmath \
    redis

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application
COPY . .

# Copy PHP configuration
COPY docker/frankenphp/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/frankenphp/Caddyfile.prod /etc/caddy/Caddyfile
COPY --from=frontend /build/frontend/dist /app/frontend/dist

ENV APP_ENV=prod

# Install dependencies without running post-install scripts (cache:clear requires
# env vars and services that are not available at image build time)
RUN composer install --no-dev --optimize-autoloader --classmap-authoritative --no-interaction --no-scripts \
    && cd /app/integrations/bluesky && npm ci --omit=dev --ignore-scripts \
    && touch /app/.env \
    && mkdir -p /app/var \
    && chown -R www-data:www-data /app/var

# Expose ports (HTTP/HTTPS/HTTP3)
EXPOSE 80 443 443/udp

# FrankenPHP server
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
