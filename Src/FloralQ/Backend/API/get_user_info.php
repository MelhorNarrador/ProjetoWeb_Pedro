<?php
// Devolve a info da conta do user (usado pelo modal Account)
// Inclui nome, email, flags de verificação e settings (threshold de alerta, posição do chart)

header('Content-Type: application/json');
require_once "../Utils/init.php";
require_once "../Middleware/auth.php";
$user = requireAuth();

try {
    $stmt = $pdo->prepare("
        SELECT user_account_name,
               user_account_email,
               user_account_email_verified,
               user_account_alert_threshold,
               user_account_alert_email_enabled,
               user_account_chart_position
        FROM user_account
        WHERE user_account_id = :user_id
    ");
    $stmt->execute(["user_id" => $user["user_id"]]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "User not found"]);
        exit;
    }

    echo json_encode(["success" => true, "data" => $data]);
} catch (PDOException $e) {
    dbError($e);
}
