<?php
// Inclut le fichier autoload de Composer pour charger automatiquement les classes
require_once __DIR__ . '/vendor/autoload.php';

// Utilise les classes nécessaires de la bibliothèque PhpAmqpLib
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Exception\AMQPIOException;
use PhpAmqpLib\Exception\AMQPTimeoutException;

/**
 ** Fonction pour générer une miniature d'une image
 * @param string $imagePath Chemin de l'image source
 */
function generateThumbnail($imagePath, $width, $height, $outputPath)
{
    // Vérifie si le fichier image existe
    if (!file_exists($imagePath)) {
        echo " [x] Erreur : le fichier image $imagePath n'existe pas.\n";
        return;
    }

    // Détermine le type de l'image en fonction de son extension
    $imageExtension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

    // Crée l'image source en fonction de son type
    switch ($imageExtension) {
        case 'jpg':
        case 'jpeg':
            $sourceImage = imagecreatefromjpeg($imagePath);
            break;
        case 'png':
            $sourceImage = imagecreatefrompng($imagePath);
            break;
        default:
            echo " [x] Erreur : format d'image non supporté ($imageExtension).\n";
            return;
    }

    if (!$sourceImage) {
        echo " [x] Erreur : impossible de créer l'image depuis $imagePath.\n";
        return;
    }

    // Obtient la largeur et la hauteur de l'image originale
    $originalWidth = imagesx($sourceImage);
    $originalHeight = imagesy($sourceImage);

    // Crée une nouvelle image vide avec les dimensions spécifiées
    $thumbnail = imagecreatetruecolor($width, $height);

    // Redimensionne l'image originale pour qu'elle tienne dans la miniature
    imagecopyresampled($thumbnail, $sourceImage, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);

    // Sauvegarde la miniature dans le répertoire spécifié
    imagejpeg($thumbnail, $outputPath);

    // Affiche un message indiquant que la miniature a été générée
    echo " [x] Miniature générée : $outputPath\n";

    // Libère la mémoire associée aux images
    imagedestroy($sourceImage);
    imagedestroy($thumbnail);
}

/**
 * Traite une image en générant des miniatures de différentes tailles
 * @param array $imageData Données de l'image à traiter
 */
function processImage($imageData)
{
    // Vérifie si le chemin de l'image est spécifié dans les données
    if (!isset($imageData['image_path'])) {
        echo " [x] Erreur : données d'image invalides dans processImage($imageData).\n";
        return;
    }

    // Récupère le chemin de l'image
    $imagePath = $imageData['image_path'];

    // Génère les miniatures de l'image en taille 50x50, 100x100 et 200x200
    generateThumbnail($imagePath, 50, 50, 'thumbnails/' . basename($imagePath, '.jpg') . '_50x50.jpg');
    generateThumbnail($imagePath, 100, 100, 'thumbnails/' . basename($imagePath, '.jpg') . '_100x100.jpg');
    generateThumbnail($imagePath, 200, 200, 'thumbnails/' . basename($imagePath, '.jpg') . '_200x200.jpg');
}

/**
 * Se connecte à RabbitMQ
 * @return AMQPStreamConnection Connexion à RabbitMQ
 */
function connectToRabbitMQ()
{
    // Utilisation de heartbeat pour maintenir la connexion active
    $heartbeat = 60;  // Intervalle de heartbeat en secondes
    // @TODO : 172.18.0.2 est eventuellement à remplacer par l'adresse IP de votre serveur RabbitMQ
    $connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest', '/', false, 'AMQPLAIN', null, 'en_US', 3, 3, null, false, $heartbeat);
    return $connection;
}

/**
 * Démarre le consommateur RabbitMQ
 */
function startConsumer()
{
    try {
        // @TODO : Se connecte à RabbitMQ
        $connection = connectToRabbitMQ();
        // @TODO : Ouvre un canal de communication
        $channel = $connection->channel();

        // @TODO : Déclare la queue pour recevoir les messages
        $channel->queue_declare('image_queue', false, true, false, false);
        //Callback pour traiter les messages reçus
        $callback = function ($msg) {
            // Affiche le message reçu dans le body de la tâche ($msg)
            echo " [x] Reçu tâche : ", $msg->body, "\n";
            // Décode les données de l'image à partir du message JSON
            $imageData = json_decode($msg->body, true);
            // Affiche le chemin de l'image à traiter
            echo " [x] Traitement de l'image : ", $imageData['image_path'], "\n";
            // Traite l'image
            processImage($imageData);
        };

        // @TODO : Consomme les messages de la queue 'image_queue'
        $channel->basic_consume('image_queue', '', false, true, false, false, $callback);

        // Boucle de consommation des messages
        while ($channel->is_consuming()) {
            try {
                // Attend les messages avec un timeout de 60 secondes
                $channel->wait(null, false, 60);
            } catch (AMQPTimeoutException $e) {
                // Timeout atteint sans message, on continue à attendre
                echo " [x] Timeout de la connexion, on continue...\n";
            } catch (AMQPConnectionClosedException $e) {
                // Connexion fermée, tentative de reconnexion
                echo " [x] Connexion fermée, tentative de reconnexion...\n";
                break;
            } catch (AMQPIOException $e) {
                // Problème réseau, tentative de reconnexion
                echo " [x] Problème réseau, tentative de reconnexion...\n";
                break;
            } catch (Exception $e) {
                // Autre erreur lors du traitement
                echo " [x] Erreur lors du traitement : ", $e->getMessage(), "\n";
                break;
            }
        }

        //@TODO :  Ferme le canal et la connexion
        $channel->close();
        $connection->close();
    } catch (Exception $e) {
        // Affiche une erreur et le message de l'erreur si la connexion à RabbitMQ échoue
        echo " [x] Erreur lors de la connexion à RabbitMQ : ", $e->getMessage(), "\n";
    }
}

/**
 * Boucle principale pour démarrer automatiquement le consommateur RabbitMQ
 */
while (true) {
    // @TODO : Affiche un message indiquant une tentative de connexion à RabbitMQ
    echo " [x] Tentative de connexion à RabbitMQ...\n";
    // @TODO :  Démarre le consommateur en appelant la fonction startConsumer()
    startConsumer();
    // Attente de 5 secondes avant de tenter de se reconnecter
    echo " [x] Attente de 5 secondes avant de tenter de se reconnecter...\n";
    sleep(5);
}
