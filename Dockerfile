FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libcurl4-openssl-dev libonig-dev \
    && docker-php-ext-install curl mbstring pdo_mysql \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite

COPY . /var/www/html/
COPY docker-entrypoint.sh /usr/local/bin/campusmarket-entrypoint

RUN chmod +x /usr/local/bin/campusmarket-entrypoint \
    && mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html

EXPOSE 10000

ENTRYPOINT ["campusmarket-entrypoint"]
CMD ["apache2-foreground"]
