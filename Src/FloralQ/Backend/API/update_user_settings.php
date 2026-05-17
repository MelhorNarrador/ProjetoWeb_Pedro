<?php
// Endpoint genérico para atualizar settings do user (threshold, email alerts, posição do chart)
// Constrói o UPDATE dinamicamente conforme os campos que vierem no payload

header('Content-Type: application/json');
require_once "../Utils/init.php";
require_once "../Middleware/auth.php";
$user = requireAuth();

$data = requireJsonBody();

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
        jsonError(400, "alert_threshold must be between 0 and 100");
    }
    $updates[] = $allowed["alert_threshold"] . " = :alert_threshold";
    $params["alert_threshold"] = (int)$t;
}

if (isset($data["alert_email_enabled"])) {
    if (!is_bool($data["alert_email_enabled"])) {
        jsonError(400, "alert_email_enabled must be boolean");
    }
    $updates[] = $allowed["alert_email_enabled"] . " = :alert_email_enabled";
    $params["alert_email_enabled"] = $data["alert_email_enabled"] ? 1 : 0;
}

if (isset($data["chart_position"])) {
    if (!in_array($data["chart_position"], ["card", "modal"], true)) {
        jsonError(400, "chart_position must be 'card' or 'modal'");
    }
    $updates[] = $allowed["chart_position"] . " = :chart_position";
    $params["chart_position"] = $data["chart_position"];
}

if (empty($updates)) {
    jsonError(400, "No valid fields to update");
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
