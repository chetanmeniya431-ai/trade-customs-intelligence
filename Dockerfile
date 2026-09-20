FROM php:8.3-cli-bookworm

RUN apt-get update && apt-get install -y \
        git unzip curl ca-certificates libpq-dev libzip-dev libpng-dev libonig-dev libxml2-dev \
        libfreetype6-dev libjpeg62-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip gd bcmath mbstring exif \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock* ./
RUN composer install --no-scripts --no-interaction --prefer-dist --no-progress

COPY package.json package-lock.json* ./
RUN npm install

COPY . .

RUN composer install --no-interaction --prefer-dist --no-progress \
    && npm run build

RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8007

# The bind mount in docker-compose.yml (.:/var/www/html) shadows this image's
# baked vendor/, node_modules/, and public/build/ with the host directory, so
# they're rebuilt here at container start rather than assumed present.
CMD ["sh", "-c", "composer install --no-interaction --prefer-dist --no-progress && npm run build && php artisan config:clear && php artisan serve --host=0.0.0.0 --port=8007"]
