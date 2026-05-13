<?php

header('Content-Type: application/json');
require_once "../Utils/init.php";
require_once "../Middleware/auth.php";
$user = requireAuth();

$plant_id = isset($_POST['plant_id']) ? (int)$_POST['plant_id'] : 0;

if (!$plant_id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "plant_id required"]);
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Image file is required"]);
    exit;
}

$file = $_FILES['image'];

// Limite de tamanho: 5 MB
if ($file['size'] > 5 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Image must be 5MB or less"]);
    exit;
}

// Validação do mime type real
$info = getimagesize($file['tmp_name']);
if ($info === false) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "File is not a valid image"]);
    exit;
}
$mime = $info['mime'];

$allowed = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

if (!isset($allowed[$mime])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Only JPG, PNG, or WEBP images allowed"]);
    exit;
}

$ext = $allowed[$mime];

try {
    // Verificar ownership + obter imagem antiga (para apagar)
    $stmt = $pdo->prepare("
        SELECT plant_image_path FROM plant
        WHERE plant_id = :plant_id AND user_account_id = :user_id
    ");
    $stmt->execute([
        "plant_id" => $plant_id,
        "user_id"  => $user["user_id"]
    ]);
    $plant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$plant) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Plant not found or not owned by user"]);
        exit;
    }

    // Gerar nome único e paths
    $filename   = $plant_id . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $db_path    = "plants/$filename";
    $uploads_root = __DIR__ . "/../../Frontend/Assets/Uploads";
    $disk_path  = "$uploads_root/$db_path";

    // Criar pasta plants/ se não existir
    if (!is_dir(dirname($disk_path))) {
        if (!mkdir(dirname($disk_path), 0775, true)) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Could not create uploads directory"]);
            exit;
        }
    }

    // Mover ficheiro
    if (!move_uploaded_file($file['tmp_name'], $disk_path)) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Could not save image"]);
        exit;
    }

    // Apagar imagem antiga, se existir
    if ($plant['plant_image_path']) {
        $old_disk = "$uploads_root/" . $plant['plant_image_path'];
        if (file_exists($old_disk)) {
            @unlink($old_disk);
        }
    }

    // Atualizar BD
    $stmt = $pdo->prepare("
        UPDATE plant
        SET plant_image_path = :path
        WHERE plant_id = :plant_id
    ");
    $stmt->execute([
        "path"     => $db_path,
        "plant_id" => $plant_id
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Image uploaded",
        "path"    => $db_path
    ]);
} catch (PDOException $e) {
    dbError($e);
}
