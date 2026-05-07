<?php

header('Content-Type: application/json');
require_once "../Utils/init.php";
require_once "../Middleware/auth.php";
$user = requireAuth();

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !isset($data["plant_id"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "plant_id required"]);
    exit;
}

$plant_id             = (int)$data["plant_id"];
$plant_name           = $data["plant_name"] ?? null;
$plant_location_label = $data["plant_location_label"] ?? null;
$plant_type_id        = $data["plant_type_id"] ?? null;
$plant_is_grown       = $data["plant_is_grown"] ?? false;

if (!$plant_name || !$plant_type_id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "plant_name and plant_type_id are required"]);
    exit;
}

try {
    // Verifica que a planta pertence ao user
    $stmt = $pdo->prepare("
        SELECT plant_id FROM plant
        WHERE plant_id = :plant_id AND user_account_id = :user_id
    ");
    $stmt->execute([
        "plant_id" => $plant_id,
        "user_id"  => $user["user_id"]
    ]);

    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Plant not found or not owned by user"]);
        exit;
    }

    // Atualiza
    $stmt = $pdo->prepare("
        UPDATE plant
        SET plant_name = :plant_name,
            plant_location_label = :plant_location_label,
            plant_type_id = :plant_type_id,
            plant_is_grown = :plant_is_grown
        WHERE plant_id = :plant_id
    ");
    $stmt->execute([
        "plant_name"           => $plant_name,
        "plant_location_label" => $plant_location_label,
        "plant_type_id"        => $plant_type_id,
        "plant_is_grown"       => $plant_is_grown ? 1 : 0,
        "plant_id"             => $plant_id,
    ]);

    echo json_encode(["success" => true, "message" => "Plant updated successfully"]);
} catch (PDOException $e) {
    dbError($e);
}
