FROM dunglas/frankenphp AS base

RUN install-php-extensions \
    pdo_mysql \
    redis \
    zip \
    opcache \
    intl \
    pcntl

ENV SERVER_NAME=:80

FROM base AS production

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY . /app