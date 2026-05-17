<?php
// Atualiza os dados de uma planta existente
// Suporta também remover a imagem associada (flag remove_image)

header('Content-Type: application/json');
require_once "../Utils/init.php";
require_once "../Middleware/auth.php";
$user = requireAuth();

$data = requireJsonBody();
if (!isset($data["plant_id"])) {
    jsonError(400, "plant_id required");
}

$plant_id = (int)$data["plant_id"];
$plant_name = $data["plant_name"] ?? null;
$plant_location_label = $data["plant_location_label"] ?? null;
$plant_type_id = $data["plant_type_id"] ?? null;
$plant_is_grown = $data["plant_is_grown"] ?? false;
$remove_image = $data["remove_image"] ?? false;

if (!$plant_name || !$plant_type_id) {
    jsonError(400, "plant_name and plant_type_id are required");
}
if (strlen($plant_name) > 30) {
    jsonError(400, "Plant name must be 30 characters or less");
}
if ($plant_location_label !== null && strlen($plant_location_label) > 50) {
    jsonError(400, "Location must be 50 characters or less");
}

try {
    // Verifica que a planta pertence ao user
    $stmt = $pdo->prepare("
        SELECT plant_id FROM plant
        WHERE plant_id = :plant_id AND user_account_id = :user_id
    ");
    $stmt->execute([
        "plant_id" => $plant_id,
        "user_id" => $user["user_id"]
    ]);

    if (!$stmt->fetch()) {
        jsonError(404, "Plant not found or not owned by user");
    }

    // Remover imagem se pedido: apaga ficheiro do disco e mete o path a NULL
    if ($remove_image) {
        // Buscar o path atual e apagar o ficheiro do disco
        $stmt = $pdo->prepare("SELECT plant_image_path FROM plant WHERE plant_id = :plant_id");
        $stmt->execute(["plant_id" => $plant_id]);
        $current = $stmt->fetchColumn();

        if ($current) {
            $disk_path = __DIR__ . "/../../Frontend/Assets/Uploads/" . $current;
            if (file_exists($disk_path)) {
                @unlink($disk_path);
            }
        }

        // Atualiza com image_path = NULL
        $stmt = $pdo->prepare("
            UPDATE plant
            SET plant_name = :plant_name,
                plant_location_label = :plant_location_label,
                plant_type_id = :plant_type_id,
                plant_is_grown = :plant_is_grown,
                plant_image_path = NULL
            WHERE plant_id = :plant_id
        ");
    } else {
        $stmt = $pdo->prepare("
            UPDATE plant
            SET plant_name = :plant_name,
                plant_location_label = :plant_location_label,
                plant_type_id = :plant_type_id,
                plant_is_grown = :plant_is_grown
            WHERE plant_id = :plant_id
        ");
    }

    $stmt->execute([
        "plant_name"           => $plant_name,
        "plant_location_label" => $plant_location_label,
        "plant_type_id"        => $plant_type_id,
        "plant_is_grown"       => $plant_is_grown ? 1 : 0,
        "plant_id"             => $plant_id,
    ]);

    echo json_encode(["success" => true, "message" => "Plant updated successfully"]);
} catch (PDOException $e) {
    dbError($e);
}
