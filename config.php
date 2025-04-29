<?php
// Charge Composer et dotenv pour gérer les dépendances et les variables d’environnement.
require 'vendor/autoload.php';

// Création d'une instance de Dotenv pour charger le fichier .env contenant les variables sensibles.
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);

// Vérifie si le fichier .env existe avant de le charger pour éviter une erreur.
if (file_exists(__DIR__ . '/.env')) {
    $dotenv->load(); // Charge les variables d'environnement dans $_ENV et $_SERVER.
} else {
    die('Le fichier .env est manquant ou inaccessible.'); // Arrête le script si le fichier n'existe pas.
}

// Récupération des variables d'environnement définies dans .env.
// Ces valeurs sont utilisées pour la connexion à la base de données.
$host = $_ENV['DB_HOST'];  // Adresse du serveur de base de données
$user = $_ENV['DB_USER'];  // Nom d'utilisateur pour la connexion
$password = $_ENV['DB_PASS'];  // Mot de passe (décrypté si besoin)
$dbname = $_ENV['DB_NAME'];  // Nom de la base de données
?>
