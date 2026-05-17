<?php
// Apaga uma planta e todas as suas leituras de sensor (numa só transação)

header('Content-Type: application/json');
require_once "../Utils/init.php";
require_once "../Middleware/auth.php";
$user = requireAuth();

$data = requireJsonBody();
if (!isset($data["plant_id"])) {
    jsonError(400, "plant_id required");
}

$plant_id = (int)$data["plant_id"];

try {
    // Verifica que a planta existe e pertence ao utilizador
    $stmt = $pdo->prepare("
        SELECT plant_id FROM plant
        WHERE plant_id = :plant_id AND user_account_id = :user_id
    ");
    $stmt->execute([
        "plant_id" => $plant_id,
        "user_id"  => $user["user_id"]
    ]);

    if (!$stmt->fetch()) {
        jsonError(404, "Plant not found or not owned by user");
    }

    // Ou apaga ambos, ou nenhum
    $pdo->beginTransaction();

    // Apaga as leituras do sensor primeiro
    $stmt = $pdo->prepare("
        DELETE FROM sensor_reading
        WHERE device_id = (SELECT device_id FROM plant WHERE plant_id = :plant_id)
    ");
    $stmt->execute(["plant_id" => $plant_id]);

    // Apaga a planta
    $stmt = $pdo->prepare("DELETE FROM plant WHERE plant_id = :plant_id");
    $stmt->execute(["plant_id" => $plant_id]);

    $pdo->commit();

    echo json_encode(["success" => true, "message" => "Plant deleted successfully"]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    dbError($e);
}
