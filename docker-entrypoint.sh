#!/bin/bash
set -e

# Render sets $PORT at runtime; default to 10000 (Render's default) if unset,
# e.g. for local testing of the image itself.
: "${PORT:=10000}"

sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

exec "$@"
