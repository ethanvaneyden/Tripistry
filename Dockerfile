FROM php:8.2-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev zip unzip git \
    && docker-php-ext-install pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY . /app

EXPOSE 10000

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-10000} router.php"]
