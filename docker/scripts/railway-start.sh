#!/bin/sh
set -eu

port="${PORT:-8080}"
sed -i "s/listen 8080;/listen ${port};/" /etc/nginx/nginx.conf

if [ -z "${APP_KEY:-}" ]; then

	echo "ERROR: APP_KEY is not configured in Railway Variables." >&2
	exit 1
fi

if [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
	for variable in DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD; do
		if [ -z "$(printenv "$variable" || true)" ]; then
			echo "ERROR: ${variable} is empty. Add Railway MySQL variable references to the app service." >&2
			exit 1
		fi
	done
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
	php artisan migrate --force
	php artisan storage:link || true
fi

php-fpm -D

exec nginx -g 'daemon off;'
