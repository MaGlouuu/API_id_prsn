<?php
// Indique que la réponse sera du JSON
header("Content-Type: application/json");

// Inclut les infos de connexion (host, user, dbname, etc.)
require 'config.php';

// Vérifie si le mot de passe crypté est fourni
if (!isset($_GET['encrypted_password'])) {
    die(json_encode(["error" => "Mot de passe crypté requis"]));
}

$encrypted_password = $_GET['encrypted_password'];

// Clé et IV pour le décryptage AES-256-CBC
$key = "ma_cle_secrete_32_bytes";
$iv = "1234567890123456";

// Déchiffre le mot de passe
$decrypted_password = openssl_decrypt($encrypted_password, 'AES-256-CBC', $key, 0, $iv);

// Vérifie si le décryptage a fonctionné
if (!$decrypted_password) {
    die(json_encode(["error" => "Échec du décryptage du mot de passe"]));
}

// Connexion à la base de données
$conn = new mysqli($host, $user, $decrypted_password, $dbname);

// Vérifie la connexion
if ($conn->connect_error) {
    die(json_encode(["error" => "Échec de connexion à la base de données : " . $conn->connect_error]));
}

// Prépare et exécute la requête pour récupérer toute la table Personnes
$sql = "SELECT id_personne, nom, prenom FROM Personnes";
$result = $conn->query($sql);

// Vérifie s'il y a des résultats
if (!$result || $result->num_rows === 0) {
    die(json_encode(["error" => "Aucune donnée trouvée dans la table Personnes"]));
}

// Stocke les résultats dans un tableau
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        "ID joueur" => $row['id_personne'],
        "Nom" => $row['nom'],
        "Prénom" => $row['prenom']
    ];
}

// Retourne les résultats au format JSON
echo json_encode($data);

// Ferme la connexion
$conn->close();
?>
