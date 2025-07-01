# Multi-stage Dockerfile for Laravel + FrankenPHP Production

# ============================================================================
# Stage 1: PHP Dependencies
# ============================================================================
FROM composer:2.7 AS composer-builder

WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies (production only, optimized)
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-suggest \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# ============================================================================
# Stage 2: Node.js build stage for frontend assets
# ============================================================================
FROM node:22-alpine AS frontend-builder

WORKDIR /app

# Copy package files
COPY package*.json ./
COPY tsconfig.json ./
COPY vite.config.ts ./
COPY tailwind.config.js ./

# Install dependencies
RUN npm ci --only=production --no-audit --no-fund

# Copy source files needed for build
COPY resources/ resources/
COPY public/ public/

# Copy vendor directory for Ziggy dependency
COPY --from=composer-builder /app/vendor vendor/

# Build frontend assets for production
RUN npm run build

# ============================================================================
# Stage 3: FrankenPHP Production Runtime
# ============================================================================
FROM dunglas/frankenphp:1-php8.3-alpine AS production

# Install system dependencies
RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    mysql-client \
    oniguruma-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        gd \
        intl \
        mbstring \
        opcache \
        pdo_mysql \
        zip

# Configure PHP for production
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Set working directory
WORKDIR /app

# Copy application files
COPY --from=composer-builder /app/vendor vendor/
COPY --from=frontend-builder /app/public/build public/build/
COPY . .

# Set proper permissions
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x artisan

# Create storage directories if they don't exist
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views \
    && chown -R www-data:www-data storage bootstrap/cache

# Copy FrankenPHP configuration
COPY docker/frankenphp/Caddyfile /etc/caddy/Caddyfile

# Copy supervisor configuration for background jobs
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy entrypoint script
COPY docker/scripts/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose ports
EXPOSE 80 443

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Set user
USER www-data

# Use custom entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Default command
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"] 