<?php
header("Content-Type: application/json");

// Inclut le fichier de configuration (qui contient les informations de connexion à la BDD
require 'config.php';

// Vérifie si le mot de passe crypté est passé en paramètre dans l'URL
if (!isset($_GET['encrypted_password'])) {
    die(json_encode(["error" => "Mot de passe crypté requis"])); // Arrête l'exécution et renvoie une erreur en JSON
}

// Récupère le mot de passe crypté depuis l'URL
$encrypted_password = $_GET['encrypted_password']; 

// Décryptage AES-256-CBC
$key = "ma_cle_secrete_32_bytes"; 
$iv = "1234567890123456"; 

// Décrypte le mot de passe
$decrypted_password = openssl_decrypt($encrypted_password, 'AES-256-CBC', $key, 0, $iv);

// Vérifie si le décryptage a réussi
if (!$decrypted_password) {
    die(json_encode(["error" => "Échec du décryptage du mot de passe"])); // Arrête l'exécution et renvoie une erreur
}

// Connexion à la base de données avec les identifiants fournis
$conn = new mysqli($host, $user, $decrypted_password, $dbname);

// Vérifie si la connexion à la base de données a échoué
if ($conn->connect_error) {
    die(json_encode(["error" => "Échec de connexion à la base de données : " . $conn->connect_error]));
}

// Vérifie si les paramètres "nom" et "prenom" sont fournis dans l'URL
if (!isset($_GET['nom']) || !isset($_GET['prenom'])) {
    die(json_encode(["error" => "Nom et prénom requis"])); // Arrête l'exécution et renvoie une erreur
}

// Récupère les valeurs des paramètres "nom" et "prenom"
$nom = $_GET['nom'];
$prenom = $_GET['prenom'];

// Prépare une requête SQL sécurisée pour rechercher un joueur dans la table "Personnes"
$sql = "SELECT id_personne, nom, prenom FROM Personnes WHERE nom = ? AND prenom = ?";
$stmt = $conn->prepare($sql);

// Lie les paramètres pour éviter les injections SQL (sécurisation)
$stmt->bind_param("ss", $nom, $prenom);

// Exécute la requête SQL
$stmt->execute();

// Récupère les résultats de la requête
$result = $stmt->get_result();

// Initialise un tableau pour stocker les données récupérées
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        "ID joueur" => $row['id_personne'], // Ajoute l'ID du joueur
        "Nom" => $row['nom'],               // Ajoute le nom du joueur
        "Prénom" => $row['prenom']          // Ajoute le prénom du joueur
    ];
}

// Vérifie si aucun joueur correspondant n'a été trouvé
if (empty($data)) {
    die(json_encode(["error" => "Aucun joueur trouvé"])); // Arrête l'exécution et renvoie une erreur
}

// Convertit le tableau des résultats en JSON et l'affiche
echo json_encode($data);

// Ferme la requête préparée et la connexion à la base de données
$stmt->close();
$conn->close();
?>
