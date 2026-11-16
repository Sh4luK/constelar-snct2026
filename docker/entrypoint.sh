#!/bin/sh
set -e

# Espera o banco, aplica as migrações pendentes e garante o administrador do .env.
php /var/www/app/bin/migrar.php
php /var/www/app/bin/seed-admin.php

exec docker-php-entrypoint "$@"
