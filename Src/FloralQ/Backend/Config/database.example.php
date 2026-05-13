<?php

$host = "";
$port = "5432";
$dbname = "";
$user = "";
$password = "";

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("[DB CONNECTION FAILED] " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    die(json_encode([
        "success" => false,
        "message" => "Internal server error"
    ]));
}
