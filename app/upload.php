<?php
require_once __DIR__ . '/vendor/autoload.php';  // Charger les dépendances Composer

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Chemin du dossier où les images seront stockées
$targetDir = "uploads/";
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);  // Créer le dossier s'il n'existe pas
}

$targetFile = $targetDir . basename($_FILES["file"]["name"]);
$imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

// Vérifier si le fichier est bien une image
$check = getimagesize($_FILES["file"]["tmp_name"]);
if ($check === false) {
    echo "Le fichier n'est pas une image.";
    exit;
}

// Vérifier si le fichier existe déjà
if (file_exists($targetFile)) {
    echo "Le fichier existe déjà.";
    exit;
}

// Limiter la taille de l'image (ex: max 5MB)
if ($_FILES["file"]["size"] > 5000000) {
    echo "Le fichier est trop volumineux.";
    exit;
}

// Autoriser uniquement certains formats d'images
if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
    echo "Seuls les fichiers JPG, JPEG et PNG sont autorisés.";
    exit;
}

// Télécharger l'image
if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
    echo "Le fichier " . htmlspecialchars(basename($_FILES["file"]["name"])) . " a été téléchargé avec succès.";

    //@TODO : Connexion à RabbitMQ
    $connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest');
    //@TODO :  Créer un canal de communication
    $channel = $connection->channel();

    //@TODO :  Déclarer une file d'attente
    $channel->queue_declare('image_queue', false, true, false, false);

    //@TODO :  Créer un message contenant le chemin de l'image
    $messageBody = json_encode(['image_path' => $targetFile]);
    //@TODO :  Créer un message AMQP avec le contenu Créé précédemment
    $message = new AMQPMessage($messageBody);

    //@TODO : Envoyer le message à la file d'attente
    $channel->basic_publish($message, '', 'image_queue');

    //@TODO : Fermer le canal et la connexion
    $channel->close();
    $connection->close();
} else {
    echo "Une erreur est survenue lors du téléchargement.";
}
?>
