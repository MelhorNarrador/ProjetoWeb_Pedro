<?php
// TIMEOUT EM SEGUNDOS PARA CONSIDERAR O SENSOR OFFLINE
define("SENSOR_TIMEOUT", 600);

// VALIDA SE O DEVICE CODE EXISTE, E SE É VÁLIDO
function requireDeviceCode()
{
    $device_code = $_GET["device_code"] ?? null;
    if (!$device_code) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "device_code required"
        ]);
        exit;
    }
    if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $device_code)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Invalid device_code format"
        ]);
        exit;
    }
    return $device_code;
}

// FORMATA SEGUNDOS PARA UM TEXTO LEGÍVEL
function formatLastReading($seconds)
{
    if ($seconds < 60) {
        return $seconds . "s";
    }

    $minutes = floor($seconds / 60);

    if ($minutes < 60) {
        return $minutes . "m";
    }

    $hours   = floor($minutes / 60);
    $minutes = $minutes % 60;

    if ($minutes == 0) {
        return $hours . "h";
    }

    return $hours . "h " . $minutes . "m";
}

// DETERMINA O STATUS DA PLANTA COM BASE NA UMIDADE ATUAL E NOS LIMITES DA PLANTA
function getPlantStatus($moisture, $min, $max)
{
    if ($moisture < $min) return "dry";
    if ($moisture > $max) return "overwatered";
    return "healthy";
}
// Verifica se o sensor está online com base no timestamp da última leitura.
function getSensorStatus($timestamp)
{
    if (!$timestamp) return "offline";
    $seconds_since_last = time() - strtotime($timestamp);
    return ($seconds_since_last > SENSOR_TIMEOUT) ? "offline" : "online";
}

// Trata erros de PDO sem expor detalhes da BD
function dbError(PDOException $e)
{
    error_log("[DB ERROR] " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Internal server error"
    ]);
}
