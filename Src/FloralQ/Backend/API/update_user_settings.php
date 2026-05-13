<?php
// Endpoint genérico para atualizar settings do user (threshold, email alerts, posição do chart)
// Constrói o UPDATE dinamicamente conforme os campos que vierem no payload

header('Content-Type: application/json');
require_once "../Utils/init.php";
require_once "../Middleware/auth.php";
$user = requireAuth();

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid JSON body"]);
    exit;
}

// Mapeia campos do payload para colunas da BD
$allowed = [
    "alert_threshold" => "user_account_alert_threshold",
    "alert_email_enabled" => "user_account_alert_email_enabled",
    "chart_position" => "user_account_chart_position",
];

// Vai-se acumulando aqui o SET ... do SQL conforme os campos forem válidos
$updates = [];
$params  = ["user_id" => $user["user_id"]];

if (isset($data["alert_threshold"])) {
    $t = $data["alert_threshold"];
    if (!is_numeric($t) || $t < 0 || $t > 100) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "alert_threshold must be between 0 and 100"]);
        exit;
    }
    $updates[] = $allowed["alert_threshold"] . " = :alert_threshold";
    $params["alert_threshold"] = (int)$t;
}

if (isset($data["alert_email_enabled"])) {
    if (!is_bool($data["alert_email_enabled"])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "alert_email_enabled must be boolean"]);
        exit;
    }
    $updates[] = $allowed["alert_email_enabled"] . " = :alert_email_enabled";
    $params["alert_email_enabled"] = $data["alert_email_enabled"] ? 1 : 0;
}

if (isset($data["chart_position"])) {
    if (!in_array($data["chart_position"], ["card", "modal"], true)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "chart_position must be 'card' or 'modal'"]);
        exit;
    }
    $updates[] = $allowed["chart_position"] . " = :chart_position";
    $params["chart_position"] = $data["chart_position"];
}

if (empty($updates)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "No valid fields to update"]);
    exit;
}

try {
    // Monta o UPDATE final juntando todas as colunas a atualizar
    $sql = "UPDATE user_account SET " . implode(", ", $updates) . " WHERE user_account_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(["success" => true, "message" => "Settings updated"]);
} catch (PDOException $e) {
    dbError($e);
}
