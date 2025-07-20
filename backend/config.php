<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'smarthomedz');
define('DB_USER', 'root'); // Remplace par ton utilisateur MySQL
define('DB_PASS', ''); // Remplace par ton mot de passe MySQL

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Démarrer la session
session_start();
?>