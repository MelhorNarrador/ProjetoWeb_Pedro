<?php
header('Content-Type: application/json');
require_once "../Utils/init.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid JSON body"]);
    exit;
}

$email    = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "email and password are required"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT user_account_id, user_account_name, user_account_email,
               user_account_password_hash, user_account_role
        FROM user_account
        WHERE user_account_email = :email
    ");
    $stmt->execute(["email" => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // MENSAGEM GENÉRICA
    if (!$user || !password_verify($password, $user["user_account_password_hash"])) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Invalid credentials"]);
        exit;
    }

    // INICIA A SESSÃO E GUARDA OS DADOS DO UTILIZADOR
    session_start();
    $_SESSION["user_id"]   = $user["user_account_id"];
    $_SESSION["user_role"] = $user["user_account_role"];

    echo json_encode([
        "success" => true,
        "user"    => [
            "id"    => $user["user_account_id"],
            "name"  => $user["user_account_name"],
            "email" => $user["user_account_email"],
            "role"  => $user["user_account_role"]
        ]
    ]);
} catch (PDOException $e) {
    dbError($e);
}
