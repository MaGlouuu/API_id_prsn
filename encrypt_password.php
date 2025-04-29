<?php
// Paramètres de chiffrement
$key = "ma_cle_secrete_32_bytes";  // La clé (doit être de 32 caractères pour AES-256)
$iv = "1234567890123456";  // L'IV (initialisation vector), doit être de 16 caractères

$password_plain = "Azerty.1@";  // Ton mot de passe en clair

// Chiffrement du mot de passe
$encrypted_password = openssl_encrypt($password_plain, 'AES-256-CBC', $key, 0, $iv);

// Vérifier si le mot de passe a été correctement chiffré
if ($encrypted_password === false) {
    echo "Erreur de chiffrement.\n";
    exit;
}

// Afficher le mot de passe chiffré
echo "Mot de passe chiffré : " . $encrypted_password . PHP_EOL;
?>
