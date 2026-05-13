<?php

header('Content-Type: application/json');
require_once "../Utils/init.php";
require_once "../Middleware/auth.php";
$user = requireAuth();
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid JSON body"]);
    exit;
}
$device_id = $data["device_id"] ?? null;
$plant_type_id = $data["plant_type_id"] ?? null;
$plant_name = $data["plant_name"] ?? null;
$plant_location_label = $data["plant_location_label"] ?? null;
$plant_is_grown = $data["plant_is_grown"] ?? false;

if (!$device_id || !$plant_type_id || !$plant_name) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "A device, plant type, and plant name are required"]);
    exit;
}
if (strlen($plant_name) > 30) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Plant name must be 30 characters or less"]);
    exit;
}
if ($plant_location_label !== null && strlen($plant_location_label) > 50) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Location must be 50 characters or less"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT device_id
        FROM device
        WHERE device_id = :device_id AND user_account_id = :user_id
    ");

    $stmt->execute([
        "device_id" => $device_id,
        "user_id"     => $user["user_id"]
    ]);

    $device = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$device) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Device not found or not owned by user"]);
        exit;
    }
    // INSERE A PLANTA ASSOCIADA AO DISPOSITIVO
    $stmt = $pdo->prepare("
        INSERT INTO plant (device_id, plant_type_id, plant_location_label, plant_name, user_account_id, plant_is_grown)
        VALUES (:device_id, :plant_type_id, :plant_location_label, :plant_name, :user_account_id, :plant_is_grown)
        RETURNING plant_id
    ");
    $stmt->execute([
        "device_id"     => $device["device_id"],
        "plant_type_id" => $plant_type_id,
        "plant_location_label" => $plant_location_label,
        "plant_name" => $plant_name,
        "user_account_id" => $user["user_id"],
        "plant_is_grown" => $plant_is_grown ? 1 : 0
    ]);
    $new_plant_id = (int)$stmt->fetchColumn();

    echo json_encode([
        "success"  => true,
        "message"  => "Plant created successfully",
        "plant_id" => $new_plant_id
    ]);
} catch (PDOException $e) {
    dbError($e);
}
