<?php

// Il n'est pas autorisé d'appeler ce script en direct
if (!defined('ROOT')) {
    define('ROOT', '..');
    require_once ROOT . '/lib/const.php';
    http_response_code(HTTP_FORBIDDEN);
    exit(1);
}

// librairie pour effectuer l'authentification jwt
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Retourne le jeton extrait des en-têtes HTTP
 * 
 * Remarque: cette fonction est plus interopérable que l'alternative: apache_request_headers
 */
function getToken() {
    $KEY = "Authorization";
    $headers = getallheaders();
    $token = NULL;
    if (array_key_exists($KEY, $headers)) {
        $token = $headers[$KEY];
        /*  "/^Bearer /" est une expression régulière. '^Bearer' veut dire texte commençant par le texte Bearer
            les / au début et à la fin délimitent l'expression régulière à trouver
        */
        if (! preg_match_all("/^Bearer /", $token) ) return NULL;
        $token = str_replace('Bearer ', '', $token);
        if (strlen($token) == 0) {
            http_response_code(HTTP_BAD_REQUEST);
            exit(1);
        }
    }
    return $token;    
    
}

$token = getToken();

$decoded = NULL;
if (isset($token)) {
    /* TODO: En production, le secret sera passé par docker via une variable d'environnement 
       afin de ne pas stocker de mot de passe dans le code
       $secretKey = getenv('SECRET');
    */
    $SECRET_KEY = "monsecret";
    $decoded = NULL;
    try {
        $decoded = JWT::decode($token, new Key($SECRET_KEY, 'HS256'));
    } catch (Exception $e) {
        http_response_code(HTTP_BAD_REQUEST);
        exit(1);
    }
} else {
    http_response_code(HTTP_BAD_REQUEST);
    exit(1);
}
