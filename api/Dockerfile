FROM debian

# Passe aux miroirs d'infomaniak suisse pour plus de rapidité
RUN sed -i 's/deb.debian.org/mirror.infomaniak.ch/g' /etc/apt/sources.list.d/debian.sources

RUN apt update

# Installe le serveur web
RUN apt install -y nginx

# Déploie la configuration Nginx avec support PHP et autorisations CORS pour insta.cfpt.info
COPY default /etc/nginx/sites-available/

# Installe php
RUN apt install -y php-fpm
RUN service php8.2-fpm start

# Définit le répertoire web comme celui par défaut
WORKDIR /var/www/html

# Expose le port 80 à l'hôte
EXPOSE 80

# Installe le driver Mongo pour PHP
RUN apt install -y php-mongodb

# Installe les dépendances PHP de l'application
RUN apt install -y composer

# Script de démarrage
COPY entry-point.sh /
RUN chmod +x /entry-point.sh

CMD ["/entry-point.sh"]
