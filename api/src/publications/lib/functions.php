<?php
use MongoDB\Client;

/**
 * Récupère la liste des publications d'un utilisteur
 * 
 * @param string $username - Nom de l'utilisateur connecté
 */
function getPublications($username) {
        $mongoClient = new MongoDB\Client(
            // TODO en production, les identifiants seront passés en variable d'environnement
            'mongodb://admin:admin@mongo:27017',
            [],
            ['typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array']]
        );
    
        // On se positionne sur notre base
        $instacfpt = $mongoClient->instacfpt;
    
        // On se positionne sur la tabler user
        $userCollection = $instacfpt->User;

        // On récupère la table des publications
        $publicationCollection = $instacfpt->Publication;

        // On vérifie que l'utilisateur est bien enregistré dans la table User
        $user = $userCollection->findOne(['name' => $username]);
        if (!$user) {
            throw new Exception('User not found');
        }
    
        // On stocke son identifiant unique
        $user_id = $user['_id'];
    
        // On récupère seulement les publications de l'utilisateur
        $publications = $publicationCollection->find(['author' => (string)$user_id]);
    
        // On crée un tableau de publication
        $publicationList = [];
        foreach ($publications as $publication) {
            $publicationList[] = $publication;
        }
    
        // On retourne ce tableau à l'appelant
        return $publicationList;   
}