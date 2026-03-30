<?php

header('Content-Type: application/json');
require_once "../Utils/init.php";
require_once "../Middleware/auth.php";
$user = requireAuth();
try {
    $stmt = $pdo->prepare("
    SELECT 
    p.plant_id,
    p.plant_name,
    p.plant_location_label,
    p.plant_is_grown,
    d.device_id,
    d.device_is_professional,
    sr.sensor_reading_moisture_percent
    FROM plant p
    JOIN device d ON p.device_id = d.device_id
    LEFT JOIN sensor_reading sr ON sr.device_id = d.device_id
    AND sr.sensor_reading_recorded_at = (
        SELECT MAX(sensor_reading_recorded_at)
        FROM sensor_reading
        WHERE device_id = d.device_id
    )
    WHERE p.user_account_id = :user_id");

    $stmt->execute(['user_id' => $user['user_id']]);
    $plants = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "plants" => $plants]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
