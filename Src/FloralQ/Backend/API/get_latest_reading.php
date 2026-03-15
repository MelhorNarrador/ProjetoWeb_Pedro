<?php

header('Content-Type: application/json');

require_once "../Config/database.php";

$device_code = $_GET["device_code"] ?? null;

if (!$device_code) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "device_code is required"
    ]);
    exit;
}

try {

    $stmt = $pdo->prepare("
        SELECT
            sr.sensor_reading_moisture_percent,
            sr.sensor_reading_latitude,
            sr.sensor_reading_longitude,
            sr.sensor_reading_recorded_at
        FROM sensor_reading sr
        JOIN device d ON sr.device_id = d.device_id
        WHERE d.device_code = :device_code
        ORDER BY sr.sensor_reading_recorded_at DESC
        LIMIT 1
    ");

    $stmt->execute([
        "device_code" => $device_code
    ]);

    $reading = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $reading
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}