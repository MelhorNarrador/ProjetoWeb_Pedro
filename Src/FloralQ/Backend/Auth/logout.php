<?php
// Endpoint de logout: destrói a sessão atual
require_once "../Utils/init.php";
header('Content-Type: application/json');
startSecureSession();
session_destroy();
echo json_encode(["success" => true, "message" => "Logged out"]);
