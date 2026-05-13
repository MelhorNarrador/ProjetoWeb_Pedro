<?php
// Template de configuração da BD. Copia para database.php e preenche.

$host = "";
$port = "5432";
$dbname = "";
$user = "";
$password = "";

try {
    // Liga a Postgres via PDO
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password
    );

    // Erros lançam exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Falha de ligação: regista e devolve 500
    error_log("[DB CONNECTION FAILED] " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    die(json_encode([
        "success" => false,
        "message" => "Internal server error"
    ]));
}
