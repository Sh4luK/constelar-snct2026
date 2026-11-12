#!/bin/sh
set -e

# Cria/atualiza o administrador a partir de ADMIN_EMAIL/ADMIN_PASSWORD.
php /var/www/app/bin/seed-admin.php

exec docker-php-entrypoint "$@"
