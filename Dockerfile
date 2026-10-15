# eWnioski na PHP 8.5 + Apache
#
# Budowanie:   docker compose build            (albo: docker build -t ewnioski:php8.5 .)
# Z MS SQL:    docker compose build --build-arg INSTALL_MSSQL=1
#              (rozszerzenia odbc i sqlsrv + sterownik Microsoft ODBC 18 - potrzebne dla RCP i Saturna)

# --- biblioteki z Composera ---
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
# rozszerzenia PHP sprawdza obraz docelowy, nie ten etap
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts --ignore-platform-reqs

# --- aplikacja ---
FROM php:8.5-apache

ARG INSTALL_MSSQL=0

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/

RUN set -eux; \
    install-php-extensions mysqli zip gd opcache; \
    if [ "$INSTALL_MSSQL" = "1" ]; then install-php-extensions odbc sqlsrv; fi; \
    apt-get update; \
    apt-get install -y --no-install-recommends msmtp; \
    rm -rf /var/lib/apt/lists/*

RUN set -eux; \
    a2enmod headers; \
    a2disconf other-vhosts-access-log

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-ewnioski.ini
COPY docker/apache.conf /etc/apache2/conf-enabled/zz-ewnioski.conf
COPY docker/entrypoint.sh /usr/local/bin/ewnioski-entrypoint
RUN chmod +x /usr/local/bin/ewnioski-entrypoint

WORKDIR /var/www/html
COPY --chown=www-data:www-data . /var/www/html
COPY --from=vendor --chown=www-data:www-data /app/vendor /var/www/html/vendor

ENV TZ=Europe/Warsaw \
    EWNIOSKI_INI=/var/www/html/conf/confe.docker.ini

VOLUME ["/var/lib/ewnioski"]
EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -fsS -o /dev/null http://localhost/php/login.php || exit 1

ENTRYPOINT ["ewnioski-entrypoint"]
CMD ["apache2-foreground"]
