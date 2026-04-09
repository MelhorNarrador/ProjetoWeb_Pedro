<?php

header('Content-Type: application/json');
require_once "../Utils/init.php";
require_once "../Middleware/auth.php";
$user = requireAuth();
try {
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
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
