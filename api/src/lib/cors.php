<?php

/* Nécessaire pour la sécurité CORS pour le basculement depuis l'origine instak.cfpt.info vers api.instak.cfpt.info
Le navigateur fait alors une requête initiale HTTP OPTION sans passer le token pour vérifier les autorisations Access-Control-Allow-Origin du serveur
*/
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    return;
}