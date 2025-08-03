# Use PHP 8.2 with Apache (matches Laravel 12 requirements)
FROM php:8.2-cli-alpine

# Set working directory
WORKDIR /app

# Install system dependencies
RUN apk add --no-cache \
   git \
   curl \
   libpng-dev \
   libxml2-dev \
   zip \
   unzip \
   oniguruma-dev \
   libzip-dev \
   freetype-dev \
   libjpeg-turbo-dev \
   libwebp-dev \
   icu-dev \
   postgresql-dev \
   mysql-client \
   && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
   && docker-php-ext-install \
   pdo \
   pdo_mysql \
   pdo_pgsql \
   mbstring \
   exif \
   pcntl \
   bcmath \
   gd \
   zip \
   intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application code first (FIXED: need app files for artisan command)
COPY . .

# Install PHP dependencies without running scripts initially
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts

# Now run the post-install scripts (artisan commands will work now)
RUN composer run-script post-autoload-dump

# Create necessary directories and set permissions
RUN mkdir -p /app/storage/logs \
   && mkdir -p /app/storage/framework/cache \
   && mkdir -p /app/storage/framework/sessions \
   && mkdir -p /app/storage/framework/views \
   && mkdir -p /app/bootstrap/cache \
   && mkdir -p /mnt/storage \
   && chmod -R 775 /app/storage \
   && chmod -R 775 /app/bootstrap/cache \
   && chmod -R 775 /mnt/storage

# Copy .env.example to .env if .env doesn't exist (for Docker)
RUN test -f .env || cp .env.example .env || echo "APP_KEY=base64:$(openssl rand -base64 32)" > .env

# Execute the build commands (matching your buildpack)
RUN php artisan config:clear \
   && php artisan cache:clear \
   && php artisan route:clear \
   && php artisan view:clear \
   && php artisan config:cache

# Expose port 3000 (matches your run command)
EXPOSE 3000

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
   CMD curl -f http://localhost:3000/api/health || curl -f http://localhost:3000 || exit 1

# Create startup script
RUN echo '#!/bin/sh' > /app/start.sh \
   && echo 'set -e' >> /app/start.sh \
   && echo 'echo "Starting Laravel application..."' >> /app/start.sh \
   && echo 'php artisan config:clear' >> /app/start.sh \
   && echo 'php artisan storage:link || true' >> /app/start.sh \
   && echo 'php artisan migrate --force || echo "Migration failed or no database configured"' >> /app/start.sh \
   && echo 'echo "Starting PHP development server on port 3000..."' >> /app/start.sh \
   && echo 'exec php -S 0.0.0.0:3000 -t public' >> /app/start.sh \
   && chmod +x /app/start.sh

# Run with startup script (runs migrations then starts server)
CMD ["/app/start.sh"]