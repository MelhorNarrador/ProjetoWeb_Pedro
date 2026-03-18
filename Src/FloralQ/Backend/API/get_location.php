<?php

header('Content-Type: application/json');

require_once "../Utils/init.php";

$device_code = $_GET["device_code"] ?? null;

if (!$device_code) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "device_code is required"
    ]);
    exit;
}
validateDeviceCode($device_code);
try {
    // OBTEM A LEITURA MAIS RECENTE COM COORDENADAS GPS
    $stmt = $pdo->prepare("
        SELECT
        sr.sensor_reading_latitude,
        sr.sensor_reading_longitude,
        sr.sensor_reading_recorded_at
        FROM sensor_reading sr
        JOIN device d ON sr.device_id = d.device_id
        WHERE d.device_code = :device_code
        AND sr.sensor_reading_latitude IS NOT NULL
        AND sr.sensor_reading_longitude IS NOT NULL
        ORDER BY sr.sensor_reading_recorded_at DESC
        LIMIT 1
    ");

    $stmt->execute([
        "device_code" => $device_code
    ]);

    $reading = $stmt->fetch(PDO::FETCH_ASSOC);

    // SEM LOCALIZAÇÃO REGISTADA
    if (!$reading) {
        echo json_encode([
            "success" => true,
            "data"    => null,
            "message" => "No GPS data available"
        ]);
        exit;
    }

    // SUCESSO
    echo json_encode([
        "success" => true,
        "data"    => [
            "latitude"   => (float)$reading["sensor_reading_latitude"],
            "longitude"  => (float)$reading["sensor_reading_longitude"],
            "recorded_at" => $reading["sensor_reading_recorded_at"]
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
