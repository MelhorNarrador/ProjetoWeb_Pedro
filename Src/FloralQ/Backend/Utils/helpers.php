<?php
// TIMEOUT EM SEGUNDOS PARA CONSIDERAR O SENSOR OFFLINE
define("SENSOR_TIMEOUT", 600);

// VALIDA SE O DEVICE CODE EXISTE, E SE É VÁLIDO
function requireDeviceCode()
{
    $device_code = $_GET["device_code"] ?? null;
    if (!$device_code) jsonError(400, "device_code required");
    if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $device_code)) {
        jsonError(400, "Invalid device_code format");
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

// Devolve uma resposta de erro JSON e termina a execução
function jsonError(int $code, string $message): void
{
    http_response_code($code);
    echo json_encode(["success" => false, "message" => $message]);
    exit;
}

// Lê o body JSON da request, ou devolve 400 se inválido
function requireJsonBody(): array
{
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) jsonError(400, "Invalid JSON body");
    return $data;
}
