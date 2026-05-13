<?php
// Devolve os sensores do utilizador que ainda não estão associados a nenhuma planta
// (usado no form de criar planta para escolher o sensor)

header('Content-Type: application/json');
require_once "../Utils/init.php";
require_once "../Middleware/auth.php";
$user = requireAuth();
try {
    // Filtra os devices que ainda não têm planta associada
    $stmt = $pdo->prepare("
        SELECT device_id, device_code
        FROM device
        WHERE user_account_id = :user_id
        AND device_id NOT IN (SELECT device_id FROM plant WHERE device_id IS NOT NULL)
    ");
    $stmt->execute(['user_id' => $user['user_id']]);
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "devices" => $devices]);
} catch (PDOException $e) {
    dbError($e);
}
