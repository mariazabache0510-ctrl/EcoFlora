#!/bin/sh
set -e
php /var/www/html/docker/backend/migrate.php
exec docker-php-entrypoint "$@"
