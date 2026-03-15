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
sr.sensor_reading_moisture_percent,
sr.sensor_reading_recorded_at,
pt.plant_type_min_moisture
FROM sensor_reading sr
JOIN device d ON sr.device_id = d.device_id
JOIN plant p ON p.device_id = d.device_id
JOIN plant_type pt ON p.plant_type_id = pt.plant_type_id
WHERE d.device_code = :device_code
ORDER BY sr.sensor_reading_recorded_at DESC
LIMIT 10
");

$stmt->execute([
    "device_code" => $device_code
]);

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($rows) < 3) {
    echo json_encode([
        "success" => true,
        "prediction" => "insufficient_data"
    ]);
    exit;
}

$latest = $rows[0];
$previous = $rows[1];

$increase =
$latest["sensor_reading_moisture_percent"] -
$previous["sensor_reading_moisture_percent"];

if ($increase >= 10) {
    echo json_encode([
        "success" => true,
        "prediction" => "recently_watered"
    ]);
    exit;
}

$total = 0;

foreach ($rows as $r) {
    $total += $r["sensor_reading_moisture_percent"];
}

$average = $total / count($rows);

$n = (float) count($rows);

$sum_x = 0;
$sum_y = 0;
$sum_xy = 0;
$sum_x2 = 0;

$base_time = strtotime($rows[$n-1]["sensor_reading_recorded_at"]);

foreach ($rows as $r) {

    $x = strtotime($r["sensor_reading_recorded_at"]) - $base_time;
    $y = $r["sensor_reading_moisture_percent"];

    $sum_x += $x;
    $sum_y += $y;
    $sum_xy += ($x * $y);
    $sum_x2 += ($x * $x);
}

$denominator = ($n * $sum_x2) - ($sum_x * $sum_x);

if ($denominator == 0) {

    echo json_encode([
        "success" => true,
        "prediction" => "unstable_data"
    ]);
    exit;
}

$slope =
(($n * $sum_xy) - ($sum_x * $sum_y)) / $denominator;

$rate_per_second = -$slope;

$current = $latest["sensor_reading_moisture_percent"];
$min = $latest["plant_type_min_moisture"];

if ($rate_per_second <= 0) {
    echo json_encode([
        "success" => true,
        "prediction" => "stable"
    ]);
    exit;
}

$seconds_until_dry =
($current - $min) / $rate_per_second;

$dry_timestamp =
time() + $seconds_until_dry;

$hours = floor($seconds_until_dry / 3600);
$minutes = floor(($seconds_until_dry % 3600) / 60);

$dry_in_human = "";

if ($hours > 0) {
    $dry_in_human .= $hours . "h ";
}

$dry_in_human .= $minutes . "m";

echo json_encode([
    "success" => true,
    "data" => [
        "current_moisture" => $current,
        "average_moisture" => round($average,1),
        "dry_in_hours" => round($seconds_until_dry / 3600,2),
        "dry_in_human" => $dry_in_human,
        "dry_at" => date("Y-m-d H:i:s",$dry_timestamp)
    ]
]);

} catch (PDOException $e) {

http_response_code(500);

echo json_encode([
    "success" => false,
    "message" => $e->getMessage()
]);

}