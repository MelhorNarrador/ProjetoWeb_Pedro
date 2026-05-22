<?php
// Altera o role de um utilizador (admin only)
header('Content-Type: application/json');
require_once "../../Utils/init.php";
require_once "../../Middleware/auth.php";
$admin = requireAdmin();
$data  = requireJsonBody();

$target_id = $data['user_id'] ?? null;
$new_role  = $data['role']    ?? null;

if (!$target_id || !$new_role)
    jsonError(400, "user_id and role are required");
if (!in_array($new_role, ['admin', 'user'], true))
    jsonError(400, "Invalid role");
if ($target_id == $admin['user_id'])
    jsonError(400, "Cannot change your own role");

try {
    // Impede de revogar o último admin do sistema
    if ($new_role === 'user') {
        $count = (int) $pdo->query("SELECT COUNT(*) FROM user_account WHERE user_account_role = 'admin'")->fetchColumn();
        if ($count <= 1) jsonError(400, "Cannot demote the last admin");
    }

    $stmt = $pdo->prepare("UPDATE user_account SET user_account_role = :role WHERE user_account_id = :id");
    $stmt->execute(['role' => $new_role, 'id' => $target_id]);

    if ($stmt->rowCount() === 0) jsonError(404, "User not found");

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    dbError($e);
}
