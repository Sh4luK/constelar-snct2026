#!/bin/sh
set -e

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
