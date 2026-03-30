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
if (!is_numeric($moisture) || $moisture < 0 || $moisture > 100) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "moisture must be a number between 0 and 100"
    ]);
    exit;
}
// VALIDAÇÃO DE LATITUDE E LONGITUDE SE FOREM FORNECIDOS
if ($latitude !== null && (!is_numeric($latitude) || $latitude < -90 || $latitude > 90)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "latitude must be between -90 and 90"]);
    exit;
}
if ($longitude !== null && (!is_numeric($longitude) || $longitude < -180 || $longitude > 180)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "longitude must be between -180 and 180"]);
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
    // SUCESSO
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
