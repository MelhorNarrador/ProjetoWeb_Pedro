<?php
// Devolve todos os utilizadores com contagem de plantas e devices (admin only)
header('Content-Type: application/json');
require_once "../../Utils/init.php";
require_once "../../Middleware/auth.php";
requireAdmin();

try {
    $stmt = $pdo->query("
        SELECT
            u.user_account_id,
            u.user_account_name,
            u.user_account_email,
            u.user_account_role,
            u.user_created_at,
            COUNT(DISTINCT p.plant_id)  AS plant_count,
            COUNT(DISTINCT d.device_id) AS device_count
        FROM user_account u
        LEFT JOIN plant  p ON p.user_account_id = u.user_account_id
        LEFT JOIN device d ON d.user_account_id = u.user_account_id
        GROUP BY u.user_account_id
        ORDER BY u.user_created_at DESC
    ");

    echo json_encode(["success" => true, "users" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (PDOException $e) {
    dbError($e);
}
