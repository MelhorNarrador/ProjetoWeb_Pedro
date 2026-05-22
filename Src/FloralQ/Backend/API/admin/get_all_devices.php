<?php
// Devolve todos os devices com dono e última leitura (admin only)
header('Content-Type: application/json');
require_once "../../Utils/init.php";
require_once "../../Middleware/auth.php";
requireAdmin();

try {
    $stmt = $pdo->query("
        SELECT
            d.device_id,
            d.device_code,
            d.device_is_professional,
            d.activation_code,
            u.user_account_id,
            u.user_account_name,
            u.user_account_email,
            sr.sensor_reading_moisture_percent,
            sr.sensor_reading_recorded_at
        FROM device d
        LEFT JOIN user_account u ON d.user_account_id = u.user_account_id
        LEFT JOIN sensor_reading sr ON sr.device_id = d.device_id
            AND sr.sensor_reading_recorded_at = (
                SELECT MAX(sensor_reading_recorded_at)
                FROM sensor_reading
                WHERE device_id = d.device_id
            )
        ORDER BY d.device_id DESC
    ");

    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($devices as &$device) {
        $device['sensor_status'] = getSensorStatus($device['sensor_reading_recorded_at']);
    }

    echo json_encode(["success" => true, "devices" => $devices]);
} catch (PDOException $e) {
    dbError($e);
}
