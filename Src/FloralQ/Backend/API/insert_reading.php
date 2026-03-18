<?php

header('Content-Type: application/json');

require_once "../Utils/init.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON body"
    ]);
    exit;
}
// VALIDAÇÃO DE CAMPOS
$device_code = $data["device_code"] ?? null;
$moisture = $data["moisture"] ?? null;
$latitude = $data["latitude"] ?? null;
$longitude = $data["longitude"] ?? null;

if (!$device_code || $moisture === null) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "device_code and moisture are required"
    ]);
    exit;
}
validateDeviceCode($device_code);
if (!is_numeric($moisture) || $moisture < 0 || $moisture > 100) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "moisture must be a number between 0 and 100"
    ]);
    exit;
}
try {
    $stmt = $pdo->prepare("
        SELECT device_id
        FROM device
        WHERE device_code = :device_code
    ");

    $stmt->execute([
        "device_code" => $device_code
    ]);

    $device = $stmt->fetch(PDO::FETCH_ASSOC);
    // VER SE O DEVICE EXISTE
    if (!$device) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Device not found"
        ]);
        exit;
    }
    // INSERIR LEITURA
    $stmt = $pdo->prepare("
        INSERT INTO sensor_reading (
            device_id,
            sensor_reading_moisture_percent,
            sensor_reading_latitude,
            sensor_reading_longitude
        )
        VALUES (
            :device_id,
            :moisture,
            :latitude,
            :longitude
        )
    ");
    // SE LAT FOR NULL, METER NULL, SENAO METER VALOR
    $stmt->execute([
        "device_id" => $device["device_id"],
        "moisture" => $moisture,
        "latitude" => $latitude,
        "longitude" => $longitude
    ]);
    // SUCESSIO
    echo json_encode([
        "success" => true,
        "message" => "Reading inserted successfully"
    ]);
    // FAIL
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
