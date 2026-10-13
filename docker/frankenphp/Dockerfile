FROM dunglas/frankenphp:1.13.0-builder-php8.5-alpine AS patched-frankenphp
WORKDIR /go/src/app/caddy/frankenphp
# Keep upstream modules and PHP linkage; patch the embedded HTTP/2 dependency.
RUN go get golang.org/x/net@v0.60.0 \
    && GOBIN=/usr/local/bin ../../go.sh install \
        -ldflags "-w -s -extldflags '-Wl,-z,stack-size=0x80000' -X 'github.com/caddyserver/caddy/v2.CustomVersion=FrankenPHP 1.13.0 PHP $PHP_VERSION Caddy' -X 'github.com/caddyserver/caddy/v2.CustomBinaryName=frankenphp' -X 'github.com/caddyserver/caddy/v2/modules/caddyhttp.ServerHeader=FrankenPHP Caddy'" -buildvcs=true \
    && frankenphp version \
    && frankenphp build-info

FROM node:24-alpine AS frontend
WORKDIR /build/frontend
COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci --ignore-scripts
COPY frontend/ ./
RUN npm run build

FROM dunglas/frankenphp:1.13.0-php8.5-alpine
COPY --from=patched-frankenphp /usr/local/bin/frankenphp /usr/local/bin/frankenphp
RUN setcap cap_net_bind_service=+ep /usr/local/bin/frankenphp

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
