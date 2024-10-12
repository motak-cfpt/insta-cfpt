<?php

/**
 * Authentification de l'utilisateur
 */

define('ROOT', '.');

require_once ROOT . '/vendor/autoload.php';
require_once ROOT . '/lib/cors.php';

use Firebase\JWT\JWT; // librairie pour effectuer l'authentification jwt

require_once 'lib/const.php';

const VALIDITY = 60 * 60; // validité: 1 heure à compter de la génération du jeton

// Seules les requêtes HTTP POST sont autorisées pour cette route
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(HTTP_METHOD_NOT_ALLOWED);
    exit(1);
}

header('Content-Type: application/json');

// Chargement du fichier json contenant les couples nom d'utilisateur / mot de passe
$data = file_get_contents('data.json');
$users = json_decode($data, true);

// Récupération du nom d'utilisateur / mot de passe depuis le corps JSON de requête HTTP
$rawData = file_get_contents("php://input");

// Décodage des données JSON
$jsonData = json_decode($rawData, true);

// Si le décodage JSON n'a pas fonctionné, on retoure un code d'erreur et on arrête l'exécution
if (json_last_error() != JSON_ERROR_NONE) {
    http_response_code(HTTP_BAD_REQUEST);
    exit(1);
}

// On récupère les informations de connexion
$login = $jsonData['login'] ?? '';
$password = $jsonData['password'] ?? '';

// Filtrage des champs par sécurité
if (isset($login) && isset($password)) {
    $login = filter_var($login, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = filter_var($password, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
} else {
    http_response_code(HTTP_BAD_REQUEST);
    exit(1);
}

// Vérifie que le nom d'utilisateur et le mot de passe sont valables
if (isset($users[$login]) && $users[$login] === $password) {
    // L'authentification est un succès -> génération du jeton JWT 
    $issuedAt = time();
    $payload = array(
        'iat' => $issuedAt,
        'exp' => $issuedAt + VALIDITY,
        'login' => $login
    );

    /* TODO: En production, le secret sera passé par docker via une variable d'environnement 
       afin de ne pas stocker de mot de passe dans le code
       $secretKey = getenv('SECRET');
    */
    $secretKey = "monsecret";
    $jwt = JWT::encode($payload, $secretKey, 'HS256');
    echo json_encode(['token' => $jwt]);
} else {
    // Si échec de l'authentification, on retour le code HTTP 401
    http_response_code(HTTP_UNAUTHORIZED);
}

// si on a atteint cette ligne, le programme retournera automatiquement le code HTTP_OK 