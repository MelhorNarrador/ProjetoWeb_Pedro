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
$activation_code = $data["activation_code"] ?? null;

try {
    $stmt = $pdo->prepare("
        SELECT device_id
        FROM device
        WHERE activation_code = :activation_code
    ");

    $stmt->execute([
        "activation_code" => $activation_code
    ]);

    $device = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$device) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Invalid activation code"]);
        exit;
    }
    // ASSOCIA O DISPOSITIVO AO UTILIZADOR
    $stmt = $pdo->prepare("
        UPDATE device
        SET user_account_id = :user_id, activation_code = NULL
        WHERE device_id = :device_id");
    $stmt->execute([
        "user_id"   => $user["user_id"],
        "device_id" => $device["device_id"]
    ]);

    echo json_encode(["success" => true, "message" => "Device redeemed successfully"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    exit;
}
