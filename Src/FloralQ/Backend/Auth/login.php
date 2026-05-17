<?php
// Endpoint de login: valida credenciais e abre sessão
header('Content-Type: application/json');
require_once "../Utils/init.php";

// Lê o corpo JSON da request (devolve 400 se faltar/inválido)
$data = requireJsonBody();

$email    = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

// Campos obrigatórios
if (!$email || !$password) {
    jsonError(400, "email and password are required");
}

try {
    // Procura o utilizador pelo email
    $stmt = $pdo->prepare("
        SELECT user_account_id, user_account_name, user_account_email,
               user_account_password_hash, user_account_role
        FROM user_account
        WHERE user_account_email = :email
    ");
    $stmt->execute(["email" => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // MENSAGEM GENÉRICA (não revela se foi email ou password que falhou)
    if (!$user || !password_verify($password, $user["user_account_password_hash"])) {
        jsonError(401, "Invalid credentials");
    }

    // INICIA A SESSÃO E GUARDA OS DADOS DO UTILIZADOR
    session_start();
    $_SESSION["user_id"]   = $user["user_account_id"];
    $_SESSION["user_role"] = $user["user_account_role"];

    // Devolve dados básicos do user (sem hash) para o frontend
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
