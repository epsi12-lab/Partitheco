# Étape 1 : résolution des dépendances Composer — le binaire composer ne doit pas
# se retrouver dans l'image finale.
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Étape 2 : image d'exécution
FROM php:8.2-apache

# Installer les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    curl \
    && docker-php-ext-install pdo pdo_pgsql pdo_mysql zip calendar \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Activer mod_rewrite pour Apache
RUN a2enmod rewrite

# Pointer le DocumentRoot Apache vers public/ : seul ce dossier doit être
# accessible par HTTP, le reste (classes/, scripts/, vendor/, etc.) ne l'est pas.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf

# Copier le projet dans le conteneur
WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor

# Définir les permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Exposer le port 80
EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -fsS http://localhost/index.php || exit 1

# Démarrer Apache
CMD ["apache2-foreground"]
