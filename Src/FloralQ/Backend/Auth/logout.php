<?php
// Endpoint de logout: destrói a sessão atual
header('Content-Type: application/json');
session_start();
session_destroy();
echo json_encode(["success" => true, "message" => "Logged out"]);
