<?php
// Calcula uma previsão de quanto tempo falta até a planta ficar seca,
// usando regressão linear sobre as últimas 20 leituras + nível de confiança (R²)

header('Content-Type: application/json');
require_once "../Utils/init.php";
require_once "../Middleware/auth.php";
$user = requireAuth();

$device_id = $_GET["device_id"] ?? null;
if (!$device_id || !is_numeric($device_id)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "device_id required"]);
    exit;
}

try {

    // Vai buscar as últimas 20 leituras (ordem desc, depois invertemos)
    $stmt = $pdo->prepare("
        SELECT
        sr.sensor_reading_moisture_percent,
        sr.sensor_reading_recorded_at,
        pt.plant_type_min_moisture
        FROM sensor_reading sr
        JOIN plant p ON p.device_id = sr.device_id
        JOIN plant_type pt ON p.plant_type_id = pt.plant_type_id
        WHERE sr.device_id = :device_id AND p.user_account_id = :user_id
        ORDER BY sr.sensor_reading_recorded_at DESC
        LIMIT 20
    ");

    $stmt->execute([
        "device_id" => (int)$device_id,
        "user_id"   => $user["user_id"]
    ]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // FILTRAR NULOS
    $filtered = array_values(array_filter(
        $rows,
        fn($r) =>
        $r["sensor_reading_moisture_percent"] !== null
    ));

    if (count($filtered) < 3) {
        echo json_encode([
            "success" => true,
            "data"    => [
                "prediction" => "insufficient_data",
                "message"    => "At least 3 readings required"
            ]
        ]);
        exit;
    }
    // REGA RECENTE: se houve um salto grande de humidade entre as 2 últimas leituras,
    // não vale a pena prever — a planta foi regada agora mesmo
    $spike = (float)$filtered[0]["sensor_reading_moisture_percent"]
        - (float)$filtered[1]["sensor_reading_moisture_percent"];

    if ($spike >= 10) {
        echo json_encode([
            "success" => true,
            "data"    => [
                "prediction"       => "recently_watered",
                "spike_detected"   => round($spike, 1)
            ]
        ]);
        exit;
    }
    // REMOVER OUTLIERS usando o método IQR (intervalos interquartis)
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
            "success" => true,
            "data"    => [
                "prediction" => "insufficient_data",
                "message"    => "At least 3 readings required"
            ]
        ]);
        exit;
    }
    // ORDENAR DO MAIS ANTIGO PARA O MAIS RECENTE
    $sorted_rows = array_reverse($filtered);

    $n        = count($sorted_rows);
    $latest   = $sorted_rows[$n - 1];

    $total   = array_sum(array_column($sorted_rows, "sensor_reading_moisture_percent"));
    $average = $total / $n;

    $base_time = strtotime($sorted_rows[0]["sensor_reading_recorded_at"]);

    // Acumuladores para calcular a regressão linear (mínimos quadrados)
    $sum_x  = 0.0;
    $sum_y  = 0.0;
    $sum_xy = 0.0;
    $sum_x2 = 0.0;

    foreach ($sorted_rows as $r) {
        $x = (float)(strtotime($r["sensor_reading_recorded_at"]) - $base_time);
        $y = (float)$r["sensor_reading_moisture_percent"];

        $sum_x  += $x;
        $sum_y  += $y;
        $sum_xy += $x * $y;
        $sum_x2 += $x * $x;
    }

    $denominator = ($n * $sum_x2) - ($sum_x * $sum_x);

    if (abs($denominator) < PHP_FLOAT_EPSILON) {
        echo json_encode([
            "success" => true,
            "data"    => [
                "prediction" => "unstable_data",
                "message"    => "Data without variation"
            ]
        ]);
        exit;
    }

    // Fórmulas standard da regressão linear (y = slope*x + intercept)
    $slope     = (($n * $sum_xy) - ($sum_x * $sum_y)) / $denominator;
    $intercept = ($sum_y - $slope * $sum_x) / $n;
    // CALCULAR R², OU SEJA, QUALIDADE DOS DADOS
    $mean_y = $sum_y / $n;
    $ss_tot = 0.0;
    $ss_res = 0.0;

    foreach ($sorted_rows as $r) {
        $x      = (float)(strtotime($r["sensor_reading_recorded_at"]) - $base_time);
        $y_real = (float)$r["sensor_reading_moisture_percent"];
        $y_pred = $slope * $x + $intercept;

        $ss_tot += ($y_real - $mean_y) ** 2;
        $ss_res += ($y_real - $y_pred) ** 2;
    }

    $r_squared = ($ss_tot > 0) ? 1 - ($ss_res / $ss_tot) : 0;

    if ($r_squared < 0.5) {
        echo json_encode([
            "success" => true,
            "data"    => [
                "prediction" => "unstable_data",
                "message"    => "Inconsistent trend (low R²)",
                "r_squared"  => round($r_squared, 3)
            ]
        ]);
        exit;
    }
    // HUMIDADE AUMENTA, SLOPE POSITIVO
    if ($slope > 0) {
        echo json_encode([
            "success" => true,
            "data"    => [
                "prediction" => "increasing_moisture",
                "r_squared"  => round($r_squared, 3)
            ]
        ]);
        exit;
    }
    // CALCULAR TEMPO ATÉ SECAR: extrapola a partir do slope quando vamos chegar ao min
    $rate_per_second  = abs($slope);
    $current_moisture = (float)$latest["sensor_reading_moisture_percent"];
    $min_moisture     = (float)$latest["plant_type_min_moisture"];

    $seconds_until_dry = ($current_moisture - $min_moisture) / $rate_per_second;

    if ($seconds_until_dry <= 0 || $seconds_until_dry > 172800) {
        echo json_encode([
            "success" => true,
            "data"    => [
                "prediction" => "unstable_data",
                "message"    => "Prediction outside acceptable range"
            ]
        ]);
        exit;
    }
    // FORMATAÇÃO PARA VALOR AMIGAVEL PARA USER
    $seconds_int  = (int)round($seconds_until_dry);
    $hours        = (int)floor($seconds_int / 3600);
    $minutes      = (int)floor(($seconds_int % 3600) / 60);
    $dry_in_friendly = trim(($hours > 0 ? $hours . "h " : "") . $minutes . "m");
    $newest_time   = strtotime($sorted_rows[$n - 1]["sensor_reading_recorded_at"]);
    $dry_timestamp = $newest_time + $seconds_int;

    // CONFIANÇA DA PREVISÃO: combina número de leituras, intervalo de tempo e R²
    $oldest_time = strtotime($sorted_rows[0]["sensor_reading_recorded_at"]);
    $span_hours  = ($newest_time - $oldest_time) / 3600;

    if ($n >= 8 && $span_hours >= 2 && $r_squared >= 0.75) {
        $confidence = "high";
    } elseif ($n >= 5 && $span_hours >= 1) {
        $confidence = "medium";
    } else {
        $confidence = "low";
    }
    // RESPOSTA
    echo json_encode([
        "success" => true,
        "data"    => [
            "prediction" => "drying",
            "current_moisture" => $current_moisture,
            "min_moisture"     => $min_moisture,
            "average_moisture" => round($average, 1),
            "dry_in_hours"     => round($seconds_until_dry / 3600, 2),
            "dry_in_friendly"  => $dry_in_friendly,
            "dry_at"           => date("Y-m-d H:i:s", $dry_timestamp),
            "trend_per_hour"   => round($rate_per_second * 3600, 2),
            "r_squared"        => round($r_squared, 3),
            "confidence"       => $confidence,
            "data_points_used" => $n,
            "span_hours"       => round($span_hours, 2)
        ]
    ]);
} catch (PDOException $e) {
    dbError($e);
}
