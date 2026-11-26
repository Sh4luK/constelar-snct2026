#!/bin/sh
set -e

# Plataformas como o Railway definem a porta em $PORT; o Apache passa a ouvir nela.
if [ -n "${PORT:-}" ] && [ "$PORT" != "80" ]; then
  sed -i "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
  sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf
fi

# Fotos ficam num volume próprio; as do acervo inicial são copiadas uma única vez.
mkdir -p /var/www/dados/fotos
cp -n /var/www/app/db/fotos/*.jpg /var/www/dados/fotos/ 2>/dev/null || true
chown -R www-data:www-data /var/www/dados/fotos

# Espera o banco, aplica as migrações pendentes e garante o administrador do .env.
php /var/www/app/bin/migrar.php
php /var/www/app/bin/seed-admin.php

# Fotos do acervo que ainda faltam: busca na Wikimedia em segundo plano (não atrasa o site).
(php /var/www/app/bin/importar-fotos.php || true) &

exec docker-php-entrypoint "$@"
