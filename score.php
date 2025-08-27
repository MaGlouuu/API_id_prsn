<?php
header("Content-Type: application/json");
require 'config.php';

// Vérifie que les paramètres requis sont bien fournis
if (!isset($_GET['encrypted_password'], $_GET['nom'], $_GET['prenom'], $_GET['id_match'], $_GET['resultat'], $_GET['mode_de_jeu'])) {
    echo json_encode(["status" => "error", "message" => "Paramètres manquants."]);
    exit;
}

// Récupération des données
$encrypted_password = $_GET['encrypted_password'];
$nom = $_GET['nom'];
$prenom = $_GET['prenom'];
$id_match = (int)$_GET['id_match'];
$resultat = $_GET['resultat'];
$mode_de_jeu = $_GET['mode_de_jeu'];

// Clé de décryptage
$key = "ma_cle_secrete_32_bytes";
$iv = "1234567890123456";

// Décryptage du mot de passe
$decrypted_password = openssl_decrypt($encrypted_password, 'AES-256-CBC', $key, 0, $iv);
if (!$decrypted_password) {
    echo json_encode(["status" => "error", "message" => "Échec du décryptage du mot de passe."]);
    exit;
}

// Connexion à la base de données
$conn = new mysqli($host, $user, $decrypted_password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Échec de la connexion à la base de données."]);
    exit;
}

// Récupération de l'ID de la personne
$stmt = $conn->prepare("SELECT id_personne FROM Personnes WHERE nom = ? AND prenom = ?");
$stmt->bind_param("ss", $nom, $prenom);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Joueur introuvable."]);
    $stmt->close();
    $conn->close();
    exit;
}
$row = $result->fetch_assoc();
$id_personne = $row['id_personne'];
$stmt->close();

// Vérifie que le match existe
$stmtMatch = $conn->prepare("SELECT id_match FROM Matches WHERE id_match = ?");
$stmtMatch->bind_param("i", $id_match);
$stmtMatch->execute();
$resultMatch = $stmtMatch->get_result();
if ($resultMatch->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Le match avec ID $id_match n'existe pas."]);
    $stmtMatch->close();
    $conn->close();
    exit;
}
$stmtMatch->close();

// Vérifie si le joueur a déjà un score pour ce match
$stmtCheck = $conn->prepare("SELECT * FROM Jouer WHERE id_personne = ? AND id_match = ?");
$stmtCheck->bind_param("ii", $id_personne, $id_match);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

if ($resultCheck->num_rows > 0) {
    // Mise à jour du score existant
    $stmtUpdate = $conn->prepare("UPDATE Jouer SET resultat = ?, mode_de_jeu = ? WHERE id_personne = ? AND id_match = ?");
    $stmtUpdate->bind_param("ssii", $resultat, $mode_de_jeu, $id_personne, $id_match);
    $stmtUpdate->execute();
    $stmtUpdate->close();
    $message = "Score mis à jour";
} else {
    // Insertion d'un nouveau score
    $stmtInsert = $conn->prepare("INSERT INTO Jouer (id_personne, id_match, resultat, mode_de_jeu) VALUES (?, ?, ?, ?)");
    $stmtInsert->bind_param("iiss", $id_personne, $id_match, $resultat, $mode_de_jeu);
    $stmtInsert->execute();
    $stmtInsert->close();
    $message = "Score ajouté";
}

$stmtCheck->close();
$conn->close();

// Réponse finale
echo json_encode(["status" => "success", "message" => $message]);
?>
