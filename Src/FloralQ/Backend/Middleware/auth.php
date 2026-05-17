<?php
// VERIFICA SE O UTILIZADOR ESTÁ AUTENTICADO
function requireAuth(): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION["user_id"])) {
        jsonError(401, "Unauthorized");
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
    if ($user["user_role"] !== "admin") jsonError(403, "Forbidden");
    return $user;
}
