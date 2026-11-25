FROM php:8.3-apache

# GD (redimensionar e limpar fotos enviadas) e EXIF (corrigir a rotação de fotos de celular).
RUN apt-get update \
 && apt-get install -y --no-install-recommends libjpeg62-turbo-dev libpng-dev libwebp-dev \
 && docker-php-ext-configure gd --with-jpeg --with-webp \
 && docker-php-ext-install gd exif \
 && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql opcache \
 && a2enmod headers expires \
 && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-constelar.ini"
COPY docker/entrypoint.sh /usr/local/bin/constelar-entrypoint
COPY app/ /var/www/app/

# Remove CR de arquivos vindos de clone no Windows (CRLF quebra o script de entrada).
RUN sed -i 's/\r$//' /usr/local/bin/constelar-entrypoint
RUN chmod +x /usr/local/bin/constelar-entrypoint \
 && chown -R root:root /var/www/app \
 && chmod -R a+rX,go-w /var/www/app

HEALTHCHECK --interval=10s --timeout=4s --start-period=120s --retries=3 \
  CMD curl -fsS http://127.0.0.1/saude.php >/dev/null || exit 1

ENTRYPOINT ["constelar-entrypoint"]
CMD ["apache2-foreground"]
