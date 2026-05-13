<?php
// Endpoint para o user mudar a password
// Pede a password atual + a nova, verifica a antiga e atualiza com novo hash

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

$current_password = $data["current_password"] ?? "";
$new_password     = $data["new_password"] ?? "";

if (!$current_password || !$new_password) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Current and new password are required"]);
    exit;
}

if (strlen($new_password) < 6) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "New password must be at least 6 characters"]);
    exit;
}

try {
    // Vai buscar a hash atual
    $stmt = $pdo->prepare("
        SELECT user_account_password_hash
        FROM user_account
        WHERE user_account_id = :user_id
    ");
    $stmt->execute(["user_id" => $user["user_id"]]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || !password_verify($current_password, $row["user_account_password_hash"])) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Current password is incorrect"]);
        exit;
    }

    // Atualiza com a nova
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        UPDATE user_account
        SET user_account_password_hash = :hash
        WHERE user_account_id = :user_id
    ");
    $stmt->execute([
        "hash"    => $new_hash,
        "user_id" => $user["user_id"]
    ]);

    echo json_encode(["success" => true, "message" => "Password changed successfully"]);
} catch (PDOException $e) {
    dbError($e);
}
