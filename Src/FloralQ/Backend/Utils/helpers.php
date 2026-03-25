<?php
// VALIDA SE O DEVICE CODE EXISTE, E SE É VÁLIDO
function requireDeviceCode()
{
    $device_code = $_REQUEST["device_code"] ?? null;
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

// FORMATAÇÃO DE TEMPO PARA VALOR QUE O USER PRECEBE
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

// DETERMINA O STATUS DA PLANTA COM BASE NA UMIDADE ATUAL E NOS LIMITES DA PLANTA
function getPlantStatus($moisture, $min, $max)
{
    if ($moisture < $min) return "dry";
    if ($moisture > $max) return "overwatered";
    return "healthy";
}
