<?php
// VERIFICA SE O UTILIZADOR ESTÁ AUTENTICADO
function requireAuth(): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION["user_id"])) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Unauthorized"]);
        exit;
    }

    return [
        "user_id"   => $_SESSION["user_id"],
        "user_role" => $_SESSION["user_role"]
    ];
}

// VERIFICA SE O UTILIZADOR É ADMIN (PARA O BACKOFFICE)
function requireAdmin(): array
{
    $user = requireAuth();

    if ($user["user_role"] !== "admin") {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Forbidden"]);
        exit;
    }

    return $user;
}
