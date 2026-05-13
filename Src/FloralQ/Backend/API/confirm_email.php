<?php

header('Content-Type: application/json');
require_once "../Utils/init.php";
require_once "../Middleware/auth.php";
$user = requireAuth();

try {
    $stmt = $pdo->prepare("
        UPDATE user_account
        SET user_account_email_verified = TRUE
        WHERE user_account_id = :user_id
    ");
    $stmt->execute(["user_id" => $user["user_id"]]);

    echo json_encode(["success" => true, "message" => "Email confirmed"]);
} catch (PDOException $e) {
    dbError($e);
}
