#!/bin/sh
set -eu

port="${PORT:-8080}"
sed -i "s/listen 8080;/listen ${port};/" /etc/nginx/nginx.conf

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
	php artisan migrate --force
	php artisan storage:link || true
fi

php-fpm -D

exec nginx -g 'daemon off;'
