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
        LIMIT 20
    ");

    $stmt->execute(["device_code" => $device_code]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // FILTRAR NULOS
    $filtered = array_values(array_filter(
        $rows,
        fn($r) =>
        $r["sensor_reading_moisture_percent"] !== null
    ));

    if (count($filtered) < 3) {
        echo json_encode([
            "success"    => true,
            "prediction" => "insufficient_data",
            "message"    => "At least 3 readings required"
        ]);
        exit;
    }
    // REGA RECENTE
    $spike = (float)$filtered[0]["sensor_reading_moisture_percent"]
        - (float)$filtered[1]["sensor_reading_moisture_percent"];

    if ($spike >= 10) {
        echo json_encode([
            "success"          => true,
            "prediction"       => "recently_watered",
            "spike_detected"   => round($spike, 1),
            "cooldown_minutes" => 30
        ]);
        exit;
    }
    // REMOVER OUTLIERS
    $moistures = array_column($filtered, "sensor_reading_moisture_percent");
    sort($moistures);
    $count = count($moistures);

    $q1  = (float)$moistures[(int)floor($count * 0.25)];
    $q3  = (float)$moistures[(int)floor($count * 0.75)];
    $iqr = $q3 - $q1;

    if ($iqr > 0) {
        $lower = $q1 - 1.5 * $iqr;
        $upper = $q3 + 1.5 * $iqr;

        $filtered = array_values(array_filter(
            $filtered,
            fn($r) =>
            (float)$r["sensor_reading_moisture_percent"] >= $lower &&
                (float)$r["sensor_reading_moisture_percent"] <= $upper
        ));
    }

    if (count($filtered) < 3) {
        echo json_encode([
            "success"    => true,
            "prediction" => "insufficient_data",
            "message"    => "At least 3 readings required"
        ]);
        exit;
    }
    // ORDENAR DO MAIS ANTIGO PARA O MAIS RECENTE
    $rows = array_reverse($filtered);

    $n        = count($rows);
    $latest   = $rows[$n - 1];
    $previous = $rows[$n - 2];

    $total   = array_sum(array_column($rows, "sensor_reading_moisture_percent"));
    $average = $total / $n;

    $base_time = strtotime($rows[0]["sensor_reading_recorded_at"]);

    $sum_x  = 0.0;
    $sum_y  = 0.0;
    $sum_xy = 0.0;
    $sum_x2 = 0.0;

    foreach ($rows as $r) {
        $x = (float)(strtotime($r["sensor_reading_recorded_at"]) - $base_time);
        $y = (float)$r["sensor_reading_moisture_percent"];

        $sum_x  += $x;
        $sum_y  += $y;
        $sum_xy += $x * $y;
        $sum_x2 += $x * $x;
    }

    $denominator = ($n * $sum_x2) - ($sum_x * $sum_x);

    if ($denominator == 0) {
        echo json_encode([
            "success"    => true,
            "prediction" => "unstable_data",
            "message"    => "Data without variation"
        ]);
        exit;
    }

    $slope     = (($n * $sum_xy) - ($sum_x * $sum_y)) / $denominator;
    $intercept = ($sum_y - $slope * $sum_x) / $n;
    // CALCULAR R², OU SEJA, QUALIDADE DOS DADOS
    $mean_y = $sum_y / $n;
    $ss_tot = 0.0;
    $ss_res = 0.0;

    foreach ($rows as $r) {
        $x      = (float)(strtotime($r["sensor_reading_recorded_at"]) - $base_time);
        $y_real = (float)$r["sensor_reading_moisture_percent"];
        $y_pred = $slope * $x + $intercept;

        $ss_tot += ($y_real - $mean_y) ** 2;
        $ss_res += ($y_real - $y_pred) ** 2;
    }

    $r_squared = ($ss_tot > 0) ? 1 - ($ss_res / $ss_tot) : 0;

    if ($r_squared < 0.5) {
        echo json_encode([
            "success"    => true,
            "prediction" => "unstable_data",
            "message"    => "Inconsistent trend (low R²)",
            "r_squared"  => round($r_squared, 3)
        ]);
        exit;
    }
    // HUMIDADE AUMENTA, SLOPE POSITIVO
    if ($slope > 0) {
        echo json_encode([
            "success"    => true,
            "prediction" => "increasing_moisture",
            "r_squared"  => round($r_squared, 3)
        ]);
        exit;
    }
    // CALCULAR TEMPO ATÉ SECAR
    $rate_per_second  = abs($slope);
    $current_moisture = (float)$latest["sensor_reading_moisture_percent"];
    $min_moisture     = (float)$latest["plant_type_min_moisture"];

    $seconds_until_dry = ($current_moisture - $min_moisture) / $rate_per_second;

    if ($seconds_until_dry <= 0 || $seconds_until_dry > 172800) {
        echo json_encode([
            "success"    => true,
            "prediction" => "unstable_data",
            "message"    => "Prediction outside acceptable range"
        ]);
        exit;
    }
    // FORMATAÇÃO PARA "HUMANO", VALOR AMIGAVEL PARA USER NÉ
    $seconds_int  = (int)round($seconds_until_dry);
    $hours        = (int)floor($seconds_int / 3600);
    $minutes      = (int)floor(($seconds_int % 3600) / 60);
    $dry_in_human = trim(($hours > 0 ? $hours . "h " : "") . $minutes . "m");
    $dry_timestamp = time() + $seconds_int;
    // CONFIANÇA DA PREVISÃO
    $oldest_time = strtotime($rows[0]["sensor_reading_recorded_at"]);
    $newest_time = strtotime($rows[$n - 1]["sensor_reading_recorded_at"]);
    $span_hours  = ($newest_time - $oldest_time) / 3600;

    if ($n >= 8 && $span_hours >= 2 && $r_squared >= 0.75) {
        $confidence = "high";
    } elseif ($n >= 5 || $span_hours >= 1) {
        $confidence = "medium";
    } else {
        $confidence = "low";
    }
    // RESPOSTA
    echo json_encode([
        "success" => true,
        "data"    => [
            "current_moisture" => $current_moisture,
            "min_moisture"     => $min_moisture,
            "average_moisture" => round($average, 1),
            "dry_in_hours"     => round($seconds_until_dry / 3600, 2),
            "dry_in_human"     => $dry_in_human,
            "dry_at"           => date("Y-m-d H:i:s", $dry_timestamp),
            "trend_per_hour"   => round($rate_per_second * 3600, 2),
            "r_squared"        => round($r_squared, 3),
            "confidence"       => $confidence,
            "data_points_used" => $n,
            "span_hours"       => round($span_hours, 2),
            "status"           => $slope < 0 ? "drying" : "stable"
        ]
    ]);
} catch (PDOException $e) {

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
