# PHP 8.3 + Apache, matching your local dev environment
FROM php:8.3-apache

# System packages needed for the PHP extensions below:
# - libpq-dev            -> pdo_pgsql / pgsql (Supabase connection)
# - libxml2-dev           -> dom (required by dompdf — this was the exact
#                            "Class Dompdf\Options not found" bug from earlier)
# - libzip-dev            -> zip
# - libfreetype6-dev,
#   libjpeg62-turbo-dev,
#   libpng-dev            -> gd (dompdf uses this for image handling)
RUN apt-get update && apt-get install -y \
        libpq-dev \
        libxml2-dev \
        libzip-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        unzip \
        git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql pgsql dom mbstring xml zip gd \
    && rm -rf /var/lib/apt/lists/*

# Composer (copied straight from the official Composer image, no separate install script)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# App code is kept nested under /var/www/html/stlaf_wip_supplies_ledger to match
# BASE_URL ("/stlaf_wip_supplies_ledger/") and the various hardcoded redirect
# paths already throughout the app (auth.php, asset links, etc.) — see chat
# history. This mirrors the exact layout that already works locally via
# `cd .. && php -S localhost:8000 -t .`, so nothing else needs to change.
WORKDIR /var/www/html/stlaf_wip_supplies_ledger
COPY . .

# --no-dev: skip dev-only packages. --optimize-autoloader: faster class loading in prod.
RUN composer install --no-dev --optimize-autoloader

# Apache's DocumentRoot stays at /var/www/html (the parent), so requests to
# /stlaf_wip_supplies_ledger/... resolve into the folder above.
WORKDIR /var/www/html
RUN a2enmod rewrite

# Render injects a $PORT env var at runtime and expects the app to bind to it
# (not a fixed port) — this entrypoint rewrites Apache's config to match
# whatever port Render assigns before starting the server.
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
