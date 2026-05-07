<?php

header('Content-Type: application/json');
require_once "../Utils/init.php";
require_once "../Middleware/auth.php";
$user = requireAuth();
$device_code = requireDeviceCode();

// SE O DEVICE EXISTE, MOSTRA PLANTA
try {

    $stmt = $pdo->prepare("
        SELECT
        p.plant_name,
        p.plant_location_label,
        pt.plant_type_name,
        pt.plant_type_min_moisture,
        pt.plant_type_max_moisture,
        d.device_is_professional
        FROM plant p
        JOIN device d ON p.device_id = d.device_id
        JOIN plant_type pt ON p.plant_type_id = pt.plant_type_id
        WHERE d.device_code = :device_code AND d.user_account_id = :user_id
");

    $stmt->execute([
        "device_code" => $device_code,
        "user_id"     => $user["user_id"]
    ]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        echo json_encode([
            "success" => true,
            "data"    => null,
            "message" => "No plant found for this device"
        ]);
        exit;
    }
    // SUCESSO
    echo json_encode([
        "success" => true,
        "data"    => $data
    ]);
    // FAIL
} catch (PDOException $e) {
    dbError($e);
}
