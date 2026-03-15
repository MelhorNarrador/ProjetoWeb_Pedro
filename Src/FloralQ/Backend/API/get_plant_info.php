<?php

header('Content-Type: application/json');

require_once "../Config/database.php";

$device_code = $_GET["device_code"] ?? null;

if (!$device_code) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "device_code required"
    ]);
    exit;
}

try {

$stmt = $pdo->prepare("
SELECT
p.plant_name,
p.plant_location_label,
pt.plant_type_name,
pt.plant_type_min_moisture,
pt.plant_type_max_moisture
FROM plant p
JOIN device d ON p.device_id = d.device_id
JOIN plant_type pt ON p.plant_type_id = pt.plant_type_id
WHERE d.device_code = :device_code
");

$stmt->execute([
"device_code" => $device_code
]);

$data = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
"success" => true,
"data" => $data
]);

} catch (PDOException $e) {

http_response_code(500);

echo json_encode([
"success" => false,
"message" => $e->getMessage()
]);

}