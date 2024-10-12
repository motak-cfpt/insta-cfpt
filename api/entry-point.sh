#!/bin/bash

# On démarre le service d'interpréteur PHP
service php8.2-fpm start

# Fonction pour récupérer le signal stop de docker (SIGTERM)
term_handler() {
  echo "Received SIGTERM, stopping Nginx immediately..."
  pkill -f nginx
  exit 0
}

# Capture le signal SIGTERM et le redirige vers la fonction de rappel ("call back")
trap 'term_handler' SIGTERM

# Démarrage du serveur web Nginx
/usr/sbin/nginx -g 'daemon off;' &

# Attente de la fin d'exécution
wait $!
