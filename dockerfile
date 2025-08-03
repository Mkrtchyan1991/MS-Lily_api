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

# Create database directory and SQLite file
RUN mkdir -p /app/database \
   && touch /app/database/database.sqlite

# Copy .env.example to .env if .env doesn't exist, or create a build-time .env
RUN if [ -f .env.example ]; then \
   cp .env.example .env; \
   else \
   echo "APP_NAME=Laravel" > .env; \
   echo "APP_ENV=production" >> .env; \
   echo "APP_KEY=base64:$(openssl rand -base64 32)" >> .env; \
   echo "APP_DEBUG=false" >> .env; \
   echo "APP_URL=http://localhost:3000" >> .env; \
   echo "" >> .env; \
   echo "DB_CONNECTION=sqlite" >> .env; \
   echo "DB_DATABASE=/app/database/database.sqlite" >> .env; \
   echo "" >> .env; \
   echo "CACHE_STORE=file" >> .env; \
   echo "SESSION_DRIVER=file" >> .env; \
   echo "QUEUE_CONNECTION=sync" >> .env; \
   fi

# Execute the build commands (matching your buildpack) with file-based cache/session
RUN CACHE_STORE=file SESSION_DRIVER=file php artisan config:clear \
   && CACHE_STORE=file SESSION_DRIVER=file php artisan cache:clear \
   && CACHE_STORE=file SESSION_DRIVER=file php artisan route:clear \
   && CACHE_STORE=file SESSION_DRIVER=file php artisan view:clear \
   && CACHE_STORE=file SESSION_DRIVER=file php artisan config:cache

# Expose port 3000 (matches your run command)
EXPOSE 3000

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
   CMD curl -f http://localhost:3000/api/health || curl -f http://localhost:3000 || exit 1

# Create startup script with better database handling
RUN echo '#!/bin/sh' > /app/start.sh \
   && echo 'set -e' >> /app/start.sh \
   && echo 'echo "Starting Laravel application..."' >> /app/start.sh \
   && echo '' >> /app/start.sh \
   && echo '# Ensure database directory and file exist' >> /app/start.sh \
   && echo 'mkdir -p /app/database' >> /app/start.sh \
   && echo 'touch /app/database/database.sqlite' >> /app/start.sh \
   && echo '' >> /app/start.sh \
   && echo '# Clear caches' >> /app/start.sh \
   && echo 'php artisan config:clear' >> /app/start.sh \
   && echo '' >> /app/start.sh \
   && echo '# Create storage link (ignore if already exists)' >> /app/start.sh \
   && echo 'php artisan storage:link 2>/dev/null || echo "Storage link already exists or failed"' >> /app/start.sh \
   && echo '' >> /app/start.sh \
   && echo '# Run migrations (create if migration files exist)' >> /app/start.sh \
   && echo 'if [ -d "database/migrations" ] && [ "$(ls -A database/migrations)" ]; then' >> /app/start.sh \
   && echo '    echo "Running database migrations..."' >> /app/start.sh \
   && echo '    php artisan migrate --force || echo "Migration failed - continuing without database"' >> /app/start.sh \
   && echo 'else' >> /app/start.sh \
   && echo '    echo "No migrations found, skipping database setup"' >> /app/start.sh \
   && echo 'fi' >> /app/start.sh \
   && echo '' >> /app/start.sh \
   && echo 'echo "Starting PHP development server on port 3000..."' >> /app/start.sh \
   && echo 'exec php -S 0.0.0.0:3000 -t public' >> /app/start.sh \
   && chmod +x /app/start.sh

# Run with startup script (runs migrations then starts server)
CMD ["/app/start.sh"]