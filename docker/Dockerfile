FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
 && a2enmod headers \
 && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-constelar.ini"
COPY docker/entrypoint.sh /usr/local/bin/constelar-entrypoint
COPY app/ /var/www/app/

RUN chmod +x /usr/local/bin/constelar-entrypoint \
 && chown -R root:root /var/www/app \
 && chmod -R a+rX,go-w /var/www/app

ENTRYPOINT ["constelar-entrypoint"]
CMD ["apache2-foreground"]
