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
pt.plant_type_min_moisture,
pt.plant_type_max_moisture
FROM sensor_reading sr
JOIN device d ON sr.device_id = d.device_id
JOIN plant p ON p.device_id = d.device_id
JOIN plant_type pt ON p.plant_type_id = pt.plant_type_id
WHERE d.device_code = :device_code
ORDER BY sr.sensor_reading_recorded_at DESC
LIMIT 1
");

$stmt->execute([
"device_code" => $device_code
]);

$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) {
echo json_encode([
"success" => false,
"message" => "No data found"
]);
exit;
}

$moisture = $data["sensor_reading_moisture_percent"];
$min = $data["plant_type_min_moisture"];
$max = $data["plant_type_max_moisture"];

$status = "healthy";

if ($moisture < $min) {
$status = "dry";
}

if ($moisture > $max) {
$status = "overwatered";
}

echo json_encode([
"success" => true,
"data" => [
"moisture" => $moisture,
"min" => $min,
"max" => $max,
"status" => $status
]
]);

} catch (PDOException $e) {

http_response_code(500);

echo json_encode([
"success" => false,
"message" => $e->getMessage()
]);

}