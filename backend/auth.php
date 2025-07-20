<?php
require_once 'config.php';

function registerUser($username, $email, $password, $name, $account_type) {
    global $pdo;
    
    // Validation des entrées
    if (empty($username) || empty($email) || empty($password) || empty($name) || empty($account_type)) {
        return ['success' => false, 'message' => 'Tous les champs sont requis.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Email invalide.'];
    }
    if (!in_array($account_type, ['client', 'technician', 'store'])) {
        return ['success' => false, 'message' => 'Type de compte invalide.'];
    }
    
    // Vérifier si l’utilisateur existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'Nom d’utilisateur ou email déjà utilisé.'];
    }
    
    // Hacher le mot de passe
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    // Insérer l’utilisateur
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, name, account_type) VALUES (?, ?, ?, ?, ?)");
    $success = $stmt->execute([$username, $email, $hashed_password, $name, $account_type]);
    
    return $success
        ? ['success' => true, 'message' => 'Inscription réussie !']
        : ['success' => false, 'message' => 'Erreur lors de l’inscription.'];
}

function loginUser($username, $password) {
    global $pdo;
    
    // Validation des entrées
    if (empty($username) || empty($password)) {
        return ['success' => false, 'message' => 'Nom d’utilisateur et mot de passe requis.'];
    }
    
    // Vérifier l’utilisateur
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        // Régénérer l’ID de session pour la sécurité
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['account_type'] = $user['account_type'];
        return ['success' => true, 'message' => 'Connexion réussie !'];
    }
    
    return ['success' => false, 'message' => 'Nom d’utilisateur ou mot de passe incorrect.'];
}

function updateProfile($user_id, $phone, $profile_picture) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE users SET phone = ?, profile_picture = ? WHERE id = ?");
    $success = $stmt->execute([$phone, $profile_picture, $user_id]);
    
    return $success
        ? ['success' => true, 'message' => 'Profil mis à jour avec succès !']
        : ['success' => false, 'message' => 'Erreur lors de la mise à jour du profil.'];
}

function addFavorite($user_id, $type, $item_id) {
    global $pdo;
    
    if (!in_array($type, ['service', 'product'])) {
        return ['success' => false, 'message' => 'Type de favori invalide.'];
    }
    
    // Vérifier si le favori existe déjà
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND type = ? AND item_id = ?");
    $stmt->execute([$user_id, $type, $item_id]);
    if ($stmt->rowCount() > 0) {
        return ['success' => false, 'message' => 'Cet élément est déjà dans vos favoris.'];
    }
    
    $stmt = $pdo->prepare("INSERT INTO favorites (user_id, type, item_id) VALUES (?, ?, ?)");
    $success = $stmt->execute([$user_id, $type, $item_id]);
    
    return $success
        ? ['success' => true, 'message' => 'Ajouté aux favoris !']
        : ['success' => false, 'message' => 'Erreur lors de l’ajout aux favoris.'];
}

function removeFavorite($user_id, $type, $item_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND type = ? AND item_id = ?");
    $success = $stmt->execute([$user_id, $type, $item_id]);
    
    return $success
        ? ['success' => true, 'message' => 'Retiré des favoris !']
        : ['success' => false, 'message' => 'Erreur lors du retrait des favoris.'];
}

function getFavorites($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT f.*, 
               CASE 
                   WHEN f.type = 'service' THEN s.name 
                   WHEN f.type = 'product' THEN p.name 
               END AS item_name,
               CASE 
                   WHEN f.type = 'service' THEN s.description 
                   WHEN f.type = 'product' THEN p.description 
               END AS item_description
        FROM favorites f
        LEFT JOIN services s ON f.type = 'service' AND f.item_id = s.id
        LEFT JOIN products p ON f.type = 'product' AND f.item_id = p.id
        WHERE f.user_id = ?
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserProfile($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT username, email, name, account_type, phone, profile_picture FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function logoutUser() {
    session_unset();
    session_destroy();
}
?>