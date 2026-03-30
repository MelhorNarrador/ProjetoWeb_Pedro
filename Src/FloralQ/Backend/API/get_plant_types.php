<?php

header('Content-Type: application/json');
require_once "../Utils/init.php";
require_once "../Middleware/auth.php";
$user = requireAuth();
try {
    $stmt = $pdo->prepare("SELECT plant_type_id, plant_type_name FROM plant_type");
    $stmt->execute();
    $plant_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "plant_types" => $plant_types]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
