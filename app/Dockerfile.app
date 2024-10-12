# Utilisez l'image PHP avec Apache intégré
FROM php:8.1-apache

# Installer les dépendances nécessaires pour GD, sockets, zip, unzip, git et libzip-dev
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    unzip \
    git \
    libzip-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install gd sockets zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copier les fichiers du projet dans le conteneur
COPY . /var/www/html

# Définir le répertoire de travail
WORKDIR /var/www/html

# Exécuter composer install pour installer les dépendances PHP
RUN composer install

# Exposer le port 80 pour l'accès HTTP
EXPOSE 80

# Lancer Apache en mode foreground
CMD ["apache2-foreground"]
