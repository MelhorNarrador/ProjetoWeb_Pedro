<?php

header('Content-Type: application/json');

require_once "../Config/database.php";

$device_code = $_GET["device_code"] ?? null;
$limit = $_GET["limit"] ?? 50;

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
sr.sensor_reading_recorded_at
FROM sensor_reading sr
JOIN device d ON sr.device_id = d.device_id
WHERE d.device_code = :device_code
ORDER BY sr.sensor_reading_recorded_at DESC
LIMIT :limit
");

$stmt->bindValue(":device_code", $device_code);
$stmt->bindValue(":limit", (int)$limit, PDO::PARAM_INT);

$stmt->execute();

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

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