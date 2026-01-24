FROM dunglas/frankenphp:latest-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    icu-dev \
    linux-headers \
    postgresql-dev \
    $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        intl \
        opcache \
        zip \
        bcmath

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application
COPY . .

# Copy PHP configuration
COPY docker/frankenphp/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/frankenphp/Caddyfile /etc/caddy/Caddyfile

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set proper permissions
RUN chown -R www-data:www-data /app/var

# Expose ports (HTTP/HTTPS/HTTP3)
EXPOSE 80 443 443/udp

# FrankenPHP server
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
