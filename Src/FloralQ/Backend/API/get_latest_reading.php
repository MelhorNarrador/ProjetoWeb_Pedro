<?php

header('Content-Type: application/json');

require_once "../Utils/init.php";
require_once "../Middleware/auth.php";
$user = requireAuth();
$device_code = requireDeviceCode();
// OBTEM A LEITURA MAIS RECENTE DO DEVICE
try {

    $stmt = $pdo->prepare("
        SELECT
        sr.sensor_reading_moisture_percent,
        sr.sensor_reading_latitude,
        sr.sensor_reading_longitude,
        sr.sensor_reading_recorded_at
        FROM sensor_reading sr
        JOIN device d ON sr.device_id = d.device_id
        WHERE d.device_code = :device_code AND d.user_account_id = :user_id
        ORDER BY sr.sensor_reading_recorded_at DESC
        LIMIT 1
    ");

    $stmt->execute([
        "device_code" => $device_code,
        "user_id"     => $user["user_id"]
    ]);

    $reading = $stmt->fetch(PDO::FETCH_ASSOC);
    // SE NAO HOUVER LEITURAS
    if (!$reading) {
        echo json_encode([
            "success" => true,
            "data"    => null,
            "message" => "No readings found for this device"
        ]);
        exit;
    }
    // SUCESSO
    echo json_encode([
        "success" => true,
        "data"    => $reading
    ]);
    // FAIL
} catch (PDOException $e) {
    dbError($e);
}
