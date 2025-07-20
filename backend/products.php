<?php
require_once 'config.php';

function addProduct($name, $description, $price, $image_url, $store_ids) {
    global $pdo;
    
    if (empty($name) || empty($description) || empty($price) || empty($image_url) || empty($store_ids)) {
        return ['success' => false, 'message' => 'Tous les champs sont requis.'];
    }
    
    $pdo->beginTransaction();
    try {
        // Insérer le produit
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $image_url]);
        $product_id = $pdo->lastInsertId();
        
        // Associer aux magasins
        $stmt = $pdo->prepare("INSERT INTO product_store (product_id, store_id) VALUES (?, ?)");
        foreach ($store_ids as $store_id) {
            $stmt->execute([$product_id, $store_id]);
        }
        
        $pdo->commit();
        return ['success' => true, 'message' => 'Produit ajouté avec succès !'];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'message' => 'Erreur lors de l’ajout du produit : ' . $e->getMessage()];
    }
}

function getProducts($store_id = null) {
    global $pdo;
    if ($store_id) {
        $stmt = $pdo->prepare("
            SELECT p.* 
            FROM products p
            JOIN product_store ps ON p.id = ps.product_id
            WHERE ps.store_id = ?
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$store_id]);
    } else {
        $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getStores() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM stores ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>