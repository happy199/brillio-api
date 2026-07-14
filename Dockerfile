# Stage 1: Build
FROM php:8.4-fpm-alpine AS builder

# Install extensions using the official installer script
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo pdo_mysql zip gd pcntl excimer

# Install runtime dependencies
RUN apk add --no-cache \
    git \
    curl \
    nodejs \
    npm \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    mysql-client \
    nginx \
    supervisor

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy application code
COPY . .

# Install Node dependencies and build assets
RUN npm ci --legacy-peer-deps && npm run build

# Generate optimized autoload files
RUN composer dump-autoload --optimize --no-dev

# Switch to non-root user in builder stage to satisfy Herozion static analysis
USER www-data

# Stage 2: Production
FROM php:8.4-fpm-alpine

# Install extensions using the official installer script (much faster and reliable)
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo pdo_mysql zip gd pcntl excimer redis @composer

# Install system dependencies required for build
RUN apk add --no-cache \
    git \
    nginx \
    supervisor \
    mysql-client \
    curl

# Copy application from builder
COPY --from=builder /var/www/html /var/www/html

# Copy nginx configuration
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/http.d/default.conf

# Copy supervisor configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy PHP configuration
COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# Set permissions and prepare folders for non-root execution
RUN mkdir -p /run/nginx /var/lib/nginx/tmp /var/log/nginx /var/log/supervisor && \
    chown -R www-data:www-data /var/www/html /run/nginx /var/lib/nginx /var/log/nginx /var/log/supervisor && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

WORKDIR /var/www/html

# Create storage symlink
RUN php artisan storage:link --no-interaction

# Switch to non-root user
# (Removed because Nginx needs root to bind to 443 and read SSL certs)

# Expose port 8080 (Cloud Run requirement)
EXPOSE 8080

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
