<?php
// Endpoint para o user mudar a password
// Pede a password atual + a nova, verifica a antiga e atualiza com novo hash

header('Content-Type: application/json');
require_once "../Utils/init.php";
require_once "../Middleware/auth.php";
$user = requireAuth();

$data = requireJsonBody();

$current_password = $data["current_password"] ?? "";
$new_password     = $data["new_password"] ?? "";

if (!$current_password || !$new_password) {
    jsonError(400, "Current and new password are required");
}

if (strlen($new_password) < 6) {
    jsonError(400, "New password must be at least 6 characters");
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
        jsonError(401, "Current password is incorrect");
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
