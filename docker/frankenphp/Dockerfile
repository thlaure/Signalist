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
COPY docker/frankenphp/Caddyfile /etc/caddy/Caddyfile

# Install dependencies and set permissions
RUN composer install --no-dev --optimize-autoloader --classmap-authoritative --no-interaction \
    && chown -R www-data:www-data /app/var

# Expose ports (HTTP/HTTPS/HTTP3)
EXPOSE 80 443 443/udp

# FrankenPHP server
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
