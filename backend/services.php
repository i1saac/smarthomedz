<?php
require_once 'config.php';

function addService($name, $description, $icon) {
    global $pdo;
    
    if (empty($name) || empty($description) || empty($icon)) {
        return ['success' => false, 'message' => 'Tous les champs sont requis.'];
    }
    
    $stmt = $pdo->prepare("INSERT INTO services (name, description, icon) VALUES (?, ?, ?)");
    $success = $stmt->execute([$name, $description, $icon]);
    
    return $success
        ? ['success' => true, 'message' => 'Service ajouté avec succès !']
        : ['success' => false, 'message' => 'Erreur lors de l’ajout du service.'];
}

function getServices() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM services ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>