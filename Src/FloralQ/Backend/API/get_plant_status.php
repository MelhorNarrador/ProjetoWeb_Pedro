<?php
// Devolve o estado atual de uma planta: humidade + classificação (dry/healthy/overwatered)
// e status do sensor (online/offline)

header('Content-Type: application/json');

require_once "../Utils/init.php";
require_once "../Middleware/auth.php";
$user = requireAuth();
$device_code = requireDeviceCode();
try {

    // Vai buscar a última leitura + os limites da espécie da planta
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
        WHERE d.device_code = :device_code AND d.user_account_id = :user_id
        ORDER BY sr.sensor_reading_recorded_at DESC
        LIMIT 1
");

    $stmt->execute([
        "device_code" => $device_code,
        "user_id"     => $user["user_id"]
    ]);

    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    // SEM LEITURAS = PLANTA UNKNOWN + SENSOR OFFLINE
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
    // SE HÁ LEITURAS, VERIFICA SE O SENSOR ESTÁ ONLINE E O ESTADO DA PLANTA
    $moisture = $data["sensor_reading_moisture_percent"];
    $min = $data["plant_type_min_moisture"];
    $max = $data["plant_type_max_moisture"];

    $seconds_since_last = time() - strtotime($data["sensor_reading_recorded_at"]);
    $sensor_status = getSensorStatus($data["sensor_reading_recorded_at"]);
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
    dbError($e);
}
