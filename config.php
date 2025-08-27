<?php
// Charge Composer et dotenv 
require 'vendor/autoload.php';

// Charge du fichier .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);

// Vérif de l'existance du fichier .env
if (file_exists(__DIR__ . '/.env')) {
    $dotenv->load();
} else {
    die('Le fichier .env est manquant ou inaccessible.'); 
}

//Connexion à la BDD avec les infos du .env
$host = $_ENV['DB_HOST'];  
$user = $_ENV['DB_USER'];  
$password = $_ENV['DB_PASS'];  
$dbname = $_ENV['DB_NAME'];  
?>
