<?php
// Apaga um device e tudo o que lhe está associado (planta + leituras) — admin only
header('Content-Type: application/json');
require_once "../../Utils/init.php";
require_once "../../Middleware/auth.php";
requireAdmin();
$data = requireJsonBody();

$device_id = $data['device_id'] ?? null;
if (!$device_id) jsonError(400, "device_id is required");

try {
    $stmt = $pdo->prepare("SELECT device_id FROM device WHERE device_id = :id");
    $stmt->execute(['id' => $device_id]);
    if (!$stmt->fetch()) jsonError(404, "Device not found");

    // 1. Apagar leituras do device
    $pdo->beginTransaction();
    $pdo->prepare("DELETE FROM sensor_reading WHERE device_id = :id")->execute(['id' => $device_id]);

    // 2. Apagar planta associada (se existir)
    $pdo->prepare("DELETE FROM plant WHERE device_id = :id")->execute(['id' => $device_id]);

    // 3. Apagar device
    $pdo->prepare("DELETE FROM device WHERE device_id = :id")->execute(['id' => $device_id]);
    $pdo->commit();
    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    dbError($e);
}
