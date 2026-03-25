<?php

header('Content-Type: application/json');

require_once "../Utils/init.php";
define("SENSOR_TIMEOUT", 600);
$device_code = requireDeviceCode();
try {

    $stmt = $pdo->prepare("
        SELECT
        sr.sensor_reading_moisture_percent,
        sr.sensor_reading_recorded_at,
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
    //nÃO HÁ LEITURAS = UNKNOWN + OFFLINE
    if (!$data) {
        echo json_encode([
            "success" => true,
            "data" => [
                "plant_status" => "unknown",
                "sensor_status" => "offline"
            ]
        ]);
        exit;
    }
    //SE HÁ LEITURAS, VERIFICA SE OSENSOR TA OINLINE, E VE PLANTA 
    $moisture = $data["sensor_reading_moisture_percent"];
    $min = $data["plant_type_min_moisture"];
    $max = $data["plant_type_max_moisture"];

    $last_reading_time = strtotime($data["sensor_reading_recorded_at"]);
    $seconds_since_last = time() - $last_reading_time;
    $sensor_status = ($seconds_since_last > SENSOR_TIMEOUT) ? "offline" : "online";
    $plant_status = "unknown";
    if ($sensor_status === "online") {
        $plant_status =
            getPlantStatus(
                $data["sensor_reading_moisture_percent"],
                $data["plant_type_min_moisture"],
                $data["plant_type_max_moisture"]
            );
    }

    // SUCESSO
    echo json_encode([
        "success" => true,
        "data" => [
            "moisture" => $moisture,
            "min" => $min,
            "max" => $max,
            "plant_status" => $plant_status,
            "sensor_status" => $sensor_status,
            "seconds_since_last_reading" => $seconds_since_last,
            "last_reading_friendly" => formatLastReading($seconds_since_last)
        ]
    ]);
    // FAIL
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
