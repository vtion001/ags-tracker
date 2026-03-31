# =====================================================
# AGS Break Tracker - Production Docker Container
# =====================================================

FROM php:8.2-fpm-alpine

# Install system dependencies for Alpine
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    libzip-dev \
    nginx \
    supervisor \
    openssl \
    tzdata \
    autoconf \
    g++ \
    make \
    linux-headers \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && pecl install redis-6.0.2 && docker-php-ext-enable redis \
    && cp /usr/share/zoneinfo/UTC /etc/localtime \
    && echo "UTC" > /etc/timezone \
    && rm -rf /tmp/pear

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /var/www/html

# Set working directory
WORKDIR /var/www/html

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs \
    && rm -f vendor/composer/platform_check.php 2>/dev/null || true

# Create storage directories
RUN mkdir -p storage/logs storage/framework/{cache,sessions,views} storage/app/public bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Create non-root user
RUN addgroup -g 1000 -S www && adduser -u 1000 -S www -G www

# Copy configuration files
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/nginx.conf /etc/nginx/http.d/nginx.conf

# Set permissions - keep root for supervisor
RUN chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache

# Fix volume mount permissions at runtime
RUN chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Run as root (supervisor needs root)
USER root

# Expose port
EXPOSE 8080

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=10s --retries=3 \
    CMD curl -f http://localhost:8080/up || exit 1

# Copy entrypoint script
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Start supervisor (nginx + php-fpm) with permission fix
ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
