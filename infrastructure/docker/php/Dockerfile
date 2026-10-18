FROM php:8.4-fpm-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libfcgi-bin libicu-dev libzip-dev \
    && docker-php-ext-install intl opcache pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY zz-health.conf /usr/local/etc/php-fpm.d/zz-health.conf
COPY entrypoint.sh /usr/local/bin/app-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/app-entrypoint.sh \
    && chmod +x /usr/local/bin/app-entrypoint.sh

WORKDIR /var/www/html

ENTRYPOINT ["app-entrypoint.sh"]
CMD ["php-fpm"]
