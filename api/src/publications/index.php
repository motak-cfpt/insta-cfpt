<?php

/**
 * Publications
 */

define('ROOT', '..');

require_once ROOT . '/vendor/autoload.php';
require_once ROOT . '/lib/cors.php';
require_once ROOT . '/lib/security.php';
require_once ROOT . '/publications/lib/functions.php';

header('Content-Type: application/json');

echo json_encode(getPublications($decoded->login));
http_response_code(200);