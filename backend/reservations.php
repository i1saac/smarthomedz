<?php
require_once 'config.php';

function addReservation($user_id, $type, $service_id = null, $product_id = null, $store_id = null, $technician_id = null, $reservation_date) {
    global $pdo;
    
    if (empty($user_id) || empty($type) || empty($reservation_date)) {
        return ['success' => false, 'message' => 'Champs requis manquants.'];
    }
    if (!in_array($type, ['technician', 'product'])) {
        return ['success' => false, 'message' => 'Type de réservation invalide.'];
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO reservations (user_id, type, service_id, product_id, store_id, technician_id, reservation_date)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $success = $stmt->execute([$user_id, $type, $service_id, $product_id, $store_id, $technician_id, $reservation_date]);
    
    return $success
        ? ['success' => true, 'message' => 'Réservation effectuée avec succès !']
        : ['success' => false, 'message' => 'Erreur lors de la réservation.'];
}

function getReservations($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT r.*, s.name AS service_name, p.name AS product_name, t.name AS technician_name, st.name AS store_name
        FROM reservations r
        LEFT JOIN services s ON r.service_id = s.id
        LEFT JOIN products p ON r.product_id = p.id
        LEFT JOIN users t ON r.technician_id = t.id
        LEFT JOIN stores st ON r.store_id = st.id
        WHERE r.user_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>