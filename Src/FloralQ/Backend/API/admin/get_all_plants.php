<?php
// Devolve todas as plantas do sistema com dono e última leitura (admin only)
header('Content-Type: application/json');
require_once "../../Utils/init.php";
require_once "../../Middleware/auth.php";
requireAdmin();

try {
    $stmt = $pdo->query("
        SELECT
            p.plant_id,
            p.plant_name,
            p.plant_location_label,
            p.plant_is_grown,
            p.created_at,
            u.user_account_name,
            u.user_account_email,
            d.device_code,
            pt.plant_type_name,
            sr.sensor_reading_moisture_percent,
            sr.sensor_reading_recorded_at
        FROM plant p
        JOIN user_account u  ON p.user_account_id = u.user_account_id
        JOIN device d        ON p.device_id = d.device_id
        JOIN plant_type pt   ON p.plant_type_id = pt.plant_type_id
        LEFT JOIN sensor_reading sr ON sr.device_id = d.device_id
            AND sr.sensor_reading_recorded_at = (
                SELECT MAX(sensor_reading_recorded_at)
                FROM sensor_reading
                WHERE device_id = d.device_id
            )
        ORDER BY p.plant_id DESC
    ");

    echo json_encode(["success" => true, "plants" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    dbError($e);
}
