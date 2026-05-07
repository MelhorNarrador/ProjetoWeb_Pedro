<?php
header('Content-Type: application/json');
require_once "../Utils/init.php";
require_once "../Middleware/auth.php";
$user = requireAuth();
$device_code = requireDeviceCode();
$range = $_GET["range"] ?? "24h";

// Configuração por range
$config = [
    "24h"   => ["bucket" => null,    "interval" => "24 hours", "limit" => 288],
    "week"  => ["bucket" => "hour",  "interval" => "7 days",   "limit" => 168],
    "month" => ["bucket" => "day",   "interval" => "30 days",  "limit" => 30],
    "year"  => ["bucket" => "month", "interval" => "365 days", "limit" => 12],
];

if (!isset($config[$range])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid range. Use: 24h, week, month, year"
    ]);
    exit;
}

$cfg = $config[$range];

try {
    if ($cfg["bucket"] === null) {
        // Sem agregação
        $sql = "
            SELECT
                sensor_reading_moisture_percent AS moisture,
                sensor_reading_recorded_at AS recorded_at
            FROM sensor_reading sr
            JOIN device d ON sr.device_id = d.device_id
            WHERE d.device_code = :device_code AND d.user_account_id = :user_id
              AND sr.sensor_reading_recorded_at > NOW() - INTERVAL '{$cfg['interval']}'
            ORDER BY sr.sensor_reading_recorded_at ASC
            LIMIT :limit
        ";
    } else {
        // Agregação por hora/dia/mês
        $sql = "
            SELECT
                ROUND(AVG(sensor_reading_moisture_percent)::numeric, 1) AS moisture,
                DATE_TRUNC('{$cfg['bucket']}', sensor_reading_recorded_at) AS recorded_at
            FROM sensor_reading sr
            JOIN device d ON sr.device_id = d.device_id
            WHERE d.device_code = :device_code AND d.user_account_id = :user_id
              AND sr.sensor_reading_recorded_at > NOW() - INTERVAL '{$cfg['interval']}'
            GROUP BY recorded_at
            ORDER BY recorded_at ASC
            LIMIT :limit
        ";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":device_code", $device_code);
    $stmt->bindValue(":user_id", $user["user_id"], PDO::PARAM_INT);
    $stmt->bindValue(":limit", (int)$cfg["limit"], PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "range"   => $range,
        "data"    => $stmt->fetchAll(PDO::FETCH_ASSOC),
    ]);
} catch (PDOException $e) {
    dbError($e);
}
