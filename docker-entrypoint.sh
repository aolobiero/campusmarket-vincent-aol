#!/bin/sh
set -eu

PORT="${PORT:-10000}"
mkdir -p /var/www/html/uploads
chown www-data:www-data /var/www/html/uploads
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/g" /etc/apache2/sites-available/000-default.conf

exec "$@"
