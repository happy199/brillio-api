# Stage 1: Build
FROM php:8.4-fpm-alpine AS builder

# Install extensions using the official installer script
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo pdo_mysql zip gd pcntl

# Install runtime dependencies
RUN apk add --no-cache \
    git \
    curl \
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

# Generate optimized autoload files
RUN composer dump-autoload --optimize

# Optimize Laravel
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Create storage symlink (baked into image)
RUN php artisan storage:link

# Stage 2: Production
FROM php:8.4-fpm-alpine

# Install extensions using the official installer script (much faster and reliable)
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo pdo_mysql zip gd pcntl @composer

# Install system dependencies required for build
RUN apk add --no-cache \
    git \
    nginx \
    supervisor \
    mysql-client

# Copy application from builder
COPY --from=builder /var/www/html /var/www/html

# Copy nginx configuration
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/http.d/default.conf

# Copy supervisor configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create directory for nginx
RUN mkdir -p /run/nginx && \
    mkdir -p /var/lib/nginx/tmp && \
    chown -R nginx:nginx /var/lib/nginx

WORKDIR /var/www/html

# Expose port 8080 (Cloud Run requirement)
EXPOSE 8080

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
