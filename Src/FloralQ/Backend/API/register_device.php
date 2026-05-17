<?php
// Endpoint chamado pelo próprio sensor (ESP32) na primeira ligação
// Regista o device na BD e devolve um código de ativação para o user inserir no site
header('Content-Type: application/json');
require_once "../Utils/init.php";

$data = requireJsonBody();

$device_code     = $data["device_code"] ?? null;
$is_professional = $data["is_professional"] ?? false;

if (!$device_code) {
    jsonError(400, "device_code required");
}
// GERA UM CÓDIGO DE ATIVAÇÃO ÚNICO DE 8 CARACTERES
$activation_code = strtoupper(bin2hex(random_bytes(4)));
// REGISTA O DEVICE SE NÃO EXISTIR, OU ATUALIZA O is_professional SE JÁ EXISTIR
try {
    $stmt = $pdo->prepare("
        INSERT INTO device (device_code, device_is_professional, activation_code)
        VALUES (:device_code, :is_professional, :activation_code)
        ON CONFLICT (device_code) DO UPDATE SET
            device_is_professional = EXCLUDED.device_is_professional,
            activation_code = COALESCE(device.activation_code, EXCLUDED.activation_code)
        RETURNING activation_code
    ");

    $stmt->execute([
        "device_code"     => $device_code,
        "is_professional" => $is_professional ? 1 : 0,
        "activation_code" => $activation_code
    ]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "success"         => true,
        "activation_code" => $row["activation_code"]
    ]);
} catch (PDOException $e) {
    dbError($e);
}
