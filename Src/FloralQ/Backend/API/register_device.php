<?php
header('Content-Type: application/json');
require_once "../Config/database.php";

$data = json_decode(file_get_contents("php://input"), true);

$device_code = $data["device_code"] ?? null;
$is_professional = $data["is_professional"] ?? false;

if (!$device_code) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "device_code required"]);
    exit;
}
// REGISTA O DEVICE, IGNORA SE JÁ EXISTIR
try {
    $stmt = $pdo->prepare("
        INSERT INTO device (device_code, device_is_professional)
        VALUES (:device_code, :is_professional)
        ON CONFLICT (device_code) DO NOTHING
");
    // SUCESSO
    $stmt->execute([
        "device_code" => $device_code,
        "is_professional" => $is_professional ? 1 : 0
    ]);

    echo json_encode(["success" => true, "message" => "Device registered"]);
    // FAIL
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
