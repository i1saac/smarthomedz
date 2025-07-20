<?php
require_once 'config.php';

function addDesign($user_id, $description) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO designs (user_id, description) VALUES (?, ?)");
        $stmt->execute([$user_id, $description]);
        return ['success' => true, 'message' => 'Description de design soumise avec succès !'];
    } catch (PDOException $e) {
        error_log("Erreur lors de l’ajout du design : " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur lors de la soumission du design.'];
    }
}

function getChatbotResponse($description) {
    // Logique simple pour simuler un chatbot basé sur des mots-clés
    $description = strtolower($description);
    $response = ['advice' => '', 'recommended_design' => ''];

    // Détection des mots-clés pour donner des conseils
    if (strpos($description, 'moderne') !== false || strpos($description, 'minimaliste') !== false) {
        $response['advice'] = "Pour un style moderne ou minimaliste, privilégiez des lignes épurées, des couleurs neutres (blanc, gris, noir) et des appareils connectés discrets comme des éclairages LED intelligents ou des thermostats encastrés.";
        $response['recommended_design'] = "Design Moderne : Intégration de capteurs domotiques invisibles et meubles multifonctionnels.";
    } elseif (strpos($description, 'rustique') !== false || strpos($description, 'naturel') !== false) {
        $response['advice'] = "Un style rustique peut inclure des matériaux comme le bois brut et des appareils connectés avec un design chaleureux, comme des lampes intelligentes avec finition bois.";
        $response['recommended_design'] = "Design Rustique : Utilisation de bois recyclé avec des systèmes d’éclairage connecté.";
    } else {
        $response['advice'] = "Votre description est unique ! Pensez à intégrer des appareils connectés comme des serrures intelligentes ou des systèmes de sécurité pour personnaliser votre maison.";
        $response['recommended_design'] = "Design Personnalisé : Une combinaison d’appareils connectés adaptés à vos besoins spécifiques.";
    }

    return $response;
}
?>