FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite

COPY . /var/www/html/
COPY docker-entrypoint.sh /usr/local/bin/campusmarket-entrypoint

RUN chmod +x /usr/local/bin/campusmarket-entrypoint \
    && mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html

EXPOSE 10000

ENTRYPOINT ["campusmarket-entrypoint"]
CMD ["apache2-foreground"]
