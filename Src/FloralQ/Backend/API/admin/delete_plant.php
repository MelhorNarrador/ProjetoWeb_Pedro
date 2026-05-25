<?php
// Apaga uma planta e as suas leituras de sensor — admin only
// O device associado fica livre (user_account_id mantém-se, mas sem planta)
header('Content-Type: application/json');
require_once "../../Utils/init.php";
require_once "../../Middleware/auth.php";
requireAdmin();
$data = requireJsonBody();

$plant_id = $data['plant_id'] ?? null;
if (!$plant_id) jsonError(400, "plant_id is required");

try {
    // Obter o device_id para apagar as leituras
    $stmt = $pdo->prepare("SELECT device_id, plant_image_path FROM plant WHERE plant_id = :id");
    $stmt->execute(['id' => $plant_id]);
    $plant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$plant) jsonError(404, "Plant not found");

    // 1. Apagar leituras do device desta planta
    $pdo->beginTransaction();
    $pdo->prepare("DELETE FROM sensor_reading WHERE device_id = :id")->execute(['id' => $plant['device_id']]);

    // 2. Apagar planta
    $pdo->prepare("DELETE FROM plant WHERE plant_id = :id")->execute(['id' => $plant_id]);
    $pdo->commit();
    // Apagar imagem do disco, se existir
    if (!empty($plant['plant_image_path'])) {
        $disk_path = __DIR__ . "/../../../Frontend/Assets/Uploads/" . $plant['plant_image_path'];
        if (file_exists($disk_path) && !unlink($disk_path)) {
            error_log("Failed to delete plant image: $disk_path");
        }
    }
    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    dbError($e);
}
