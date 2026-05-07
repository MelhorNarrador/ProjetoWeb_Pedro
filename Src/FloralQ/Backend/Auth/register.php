<?php
header('Content-Type: application/json');
require_once "../Utils/init.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid JSON body"]);
    exit;
}

$name     = trim($data["name"] ?? "");
$email    = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

// VALIDAÇÃO DOS CAMPOS
if (!$name || !$email || !$password) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "name, email and password are required"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid email"]);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Password must be at least 6 characters"]);
    exit;
}

try {
    // VERIFICA SE O EMAIL JÁ EXISTE
    $stmt = $pdo->prepare("SELECT user_account_id FROM user_account WHERE user_account_email = :email");
    $stmt->execute(["email" => $email]);

    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Email already in use"]);
        exit;
    }

    // CRIA A CONTA
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO user_account (user_account_name, user_account_email, user_account_password_hash)
        VALUES (:name, :email, :hash)
    ");

    $stmt->execute([
        "name"  => $name,
        "email" => $email,
        "hash"  => $hash
    ]);

    echo json_encode(["success" => true, "message" => "Account created"]);
} catch (PDOException $e) {
    dbError($e);
}
