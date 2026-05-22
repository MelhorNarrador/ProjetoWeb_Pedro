<?php
// Apaga um utilizador e limpa as suas plantas/leituras (admin only)
// Devices ficam na BD com user_account_id = NULL
header('Content-Type: application/json');
require_once "../../Utils/init.php";
require_once "../../Middleware/auth.php";
$admin = requireAdmin();
$data  = requireJsonBody();

$target_id = $data['user_id'] ?? null;
if (!$target_id)
    jsonError(400, "user_id is required");
if ($target_id == $admin['user_id'])
    jsonError(400, "Cannot delete your own account");

try {
    $stmt = $pdo->prepare("SELECT user_account_role FROM user_account WHERE user_account_id = :id");
    $stmt->execute(['id' => $target_id]);
    $target = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$target)
        jsonError(404, "User not found");
    if ($target['user_account_role'] === 'admin')
        jsonError(400, "Cannot delete an admin. Demote first.");
    // 1. Obter os device_ids das plantas do utilizador
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("SELECT device_id FROM plant WHERE user_account_id = :id");
    $stmt->execute(['id' => $target_id]);
    $device_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 2. Apagar leituras desses devices
    if ($device_ids) {
        $placeholders = implode(',', array_fill(0, count($device_ids), '?'));
        $pdo->prepare("DELETE FROM sensor_reading WHERE device_id IN ($placeholders)")->execute($device_ids);
    }

    // 3. Apagar plantas
    $pdo->prepare("DELETE FROM plant WHERE user_account_id = :id")->execute(['id' => $target_id]);

    // 4. Desassocia devices
    $pdo->prepare("UPDATE device SET user_account_id = NULL WHERE user_account_id = :id")->execute(['id' => $target_id]);

    // 5. Apagar utilizador
    $pdo->prepare("DELETE FROM user_account WHERE user_account_id = :id")->execute(['id' => $target_id]);
    $pdo->commit();
    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    dbError($e);
}
