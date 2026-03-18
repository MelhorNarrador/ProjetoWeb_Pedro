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

define("SENSOR_TIMEOUT", 600);
// FORMATAÇÃO DE TEMPO PARA HUMANO, VALOR QUE O USER PRECEBE
function formatLastReading($seconds)
{
    if ($seconds < 60) {
        return $seconds . " segundos";
    }

    $minutes = floor($seconds / 60);

    if ($minutes < 60) {
        return $minutes . " minutos";
    }

    $hours = floor($minutes / 60);
    $minutes = $minutes % 60;

    if ($minutes == 0) {
        return $hours . " horas";
    }

    return $hours . " hora(s) e " . $minutes . " minuto(s)";
}

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
    $now = time();

    $seconds_since_last = $now - $last_reading_time;

    $sensor_status = "online";

    if ($seconds_since_last > SENSOR_TIMEOUT) {
        $sensor_status = "offline";
    }

    $plant_status = "unknown";

    if ($sensor_status === "online") {

        if ($moisture < $min) {
            $plant_status = "dry";
        } elseif ($moisture > $max) {
            $plant_status = "overwatered";
        } else {
            $plant_status = "healthy";
        }
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
            "last_reading_human" => formatLastReading($seconds_since_last)
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
