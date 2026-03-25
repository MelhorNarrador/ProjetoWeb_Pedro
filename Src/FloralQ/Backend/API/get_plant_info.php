<?php

header('Content-Type: application/json');
require_once "../Utils/init.php";
$device_code = requireDeviceCode();

// SE O DEVICE EXISTE, MOSTRA PLAMTA
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
        WHERE d.device_code = :device_code
");

    $stmt->execute([
        "device_code" => $device_code
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
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
