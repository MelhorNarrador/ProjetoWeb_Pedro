<?php
// Endpoint de registo: cria nova conta de utilizador
header('Content-Type: application/json');
require_once "../Utils/init.php";

// Lê o corpo JSON da request (devolve 400 se faltar/inválido)
$data = requireJsonBody();

$name     = trim($data["name"] ?? "");
$email    = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

// VALIDAÇÃO DOS CAMPOS
if (!$name || !$email || !$password) {
    jsonError(400, "name, email and password are required");
}

// Email tem de ter formato válido
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonError(400, "Invalid email");
}

// Password mínima de 6 caracteres
if (strlen($password) < 6) {
    jsonError(400, "Password must be at least 6 characters");
}

try {
    // VERIFICA SE O EMAIL JÁ EXISTE
    $stmt = $pdo->prepare("SELECT user_account_id FROM user_account WHERE user_account_email = :email");
    $stmt->execute(["email" => $email]);

    if ($stmt->fetch()) {
        jsonError(409, "Email already in use");
    }

    // CRIA A CONTA (gera hash bcrypt da password)
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
