<?php
header('Content-Type: application/json');
require_once "../Utils/init.php";

$device_code = $_GET['device_code'] ?? null;
if (!$device_code) jsonError(400, "device_code required");

try {
    $stmt = $pdo->prepare("
    SELECT
        p.plant_is_grown,
        pt.plant_type_min_moisture,
        pt.plant_type_max_moisture,
        sr.sensor_reading_moisture_percent
    FROM device d
    JOIN plant      p  ON p.device_id      = d.device_id
    JOIN plant_type pt ON pt.plant_type_id = p.plant_type_id
    LEFT JOIN sensor_reading sr ON sr.device_id = d.device_id
    AND sr.sensor_reading_recorded_at = (
        SELECT MAX(sensor_reading_recorded_at)
        FROM sensor_reading WHERE device_id = d.device_id
    )
    WHERE d.device_code = :device_code
    ");
    $stmt->execute([
        "device_code" => $device_code
    ]);
    $plant = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$plant) jsonError(404, "Device not found or has no plant");

    // Usa o mesmo helper que get_user_plants
    $state = getMoistureState(
        $plant['sensor_reading_moisture_percent'],
        $plant['plant_type_min_moisture'],
        $plant['plant_type_max_moisture']
    );

    // O ESP32 espera "offline" em vez de "no-data"
    if ($state === "no-data") $state = "offline";

    echo json_encode([
        "success" => true,
        "state" => $state,
        "is_grown" => (bool) $plant['plant_is_grown']
    ]);
} catch (PDOException $e) {
    dbError($e);
}
