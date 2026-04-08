FROM php:8.2-cli-alpine

WORKDIR /app

RUN docker-php-ext-install pdo_mysql
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-interaction

COPY . .

EXPOSE 8090

CMD ["sh", "-c", "composer install --no-interaction && php bin/server.php"]
