<?php
header("Content-Type: application/json; charset=UTF-8");

require 'config.php';  // Le fichier config.php contient les informations de la base de données
require 'encrypt_password.php';  // Le fichier encrypt_password.php contient la clé de déchiffrement

// Lire les paramètres envoyés via GET
$encrypted_password = isset($_GET['encrypted_password']) ? $_GET['encrypted_password'] : '';
$nom = isset($_GET['nom']) ? $_GET['nom'] : '';
$prenom = isset($_GET['prenom']) ? $_GET['prenom'] : '';
$id_match = isset($_GET['id_match']) ? $_GET['id_match'] : '';
$resultat = isset($_GET['resultat']) ? $_GET['resultat'] : '';
$mode_de_jeu = isset($_GET['mode_de_jeu']) ? $_GET['mode_de_jeu'] : '';

// Vérifier que toutes les données sont présentes
if (empty($encrypted_password) || empty($nom) || empty($prenom) || empty($resultat) || empty($mode_de_jeu) || empty($id_match)) {
    echo json_encode(["status" => "error", "message" => "Données manquantes"]);
    exit;
}

// Déchiffrer le mot de passe
$decrypted_password = openssl_decrypt($encrypted_password, 'AES-256-CBC', $key, 0, $iv);

// Si le mot de passe ne se déchiffre pas correctement
if (!$decrypted_password) {
    echo json_encode(["status" => "error", "message" => "Échec du déchiffrement du mot de passe"]);
    exit;
}

// Connexion à la base de données
$conn = new mysqli($host, $user, $decrypted_password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connexion échouée: " . $conn->connect_error]);
    exit;
}

// Récupérer l'ID du joueur (nom et prénom)
$sql_id = "SELECT id_personne FROM Personnes WHERE nom = ? AND prenom = ?";
$stmt_id = $conn->prepare($sql_id);
$stmt_id->bind_param("ss", $nom, $prenom);
$stmt_id->execute();
$result_id = $stmt_id->get_result();

if ($row = $result_id->fetch_assoc()) {
    $id_personne = $row['id_personne'];

    // Vérifier si le score pour ce joueur et ce match existe déjà dans la table "Jouer"
    $sql_check_score = "SELECT id_personne, id_match FROM Jouer WHERE id_personne = ? AND id_match = ?";
    $stmt_check_score = $conn->prepare($sql_check_score);
    $stmt_check_score->bind_param("ii", $id_personne, $id_match);
    $stmt_check_score->execute();
    $result_check_score = $stmt_check_score->get_result();

    if ($result_check_score->num_rows > 0) {
        // Si le score existe déjà, mettre à jour le score
        $sql_update = "UPDATE Jouer SET resultat = ?, mode_de_jeu = ? WHERE id_personne = ? AND id_match = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ssii", $resultat, $mode_de_jeu, $id_personne, $id_match);

        if ($stmt_update->execute()) {
            echo json_encode(["status" => "success", "message" => "Score mis à jour"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Erreur lors de la mise à jour du score"]);
        }
        $stmt_update->close();
    } else {
        // Si le score n'existe pas, insérer un nouveau score
        $sql_insert = "INSERT INTO Jouer (id_personne, id_match, resultat, mode_de_jeu) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("iiss", $id_personne, $id_match, $resultat, $mode_de_jeu);

        if ($stmt_insert->execute()) {
            echo json_encode(["status" => "success", "message" => "Score enregistré"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Erreur lors de l'insertion"]);
        }
        $stmt_insert->close();
    }

    $stmt_check_score->close();
} else {
    echo json_encode(["status" => "error", "message" => "Utilisateur non trouvé"]);
}

$stmt_id->close();
$conn->close();
?>
