<?php
// Smoke test: confirma que a ligação à BD está OK e devolve a hora do servidor

require_once "../Utils/init.php";

$stmt = $pdo->query("SELECT NOW()");
$row = $stmt->fetch();

echo "Ligação à base de dados 200<br>";
echo "Hora do servidor: " . $row['now'];
