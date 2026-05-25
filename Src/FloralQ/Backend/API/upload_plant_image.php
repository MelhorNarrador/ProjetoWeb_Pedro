<?php
// Recebe um ficheiro de imagem (multipart/form-data) e guarda no disco
// O path relativo fica gravado na BD em plant.plant_image_path

header('Content-Type: application/json');
require_once "../Utils/init.php";
require_once "../Middleware/auth.php";
$user = requireAuth();

$plant_id = isset($_POST['plant_id']) ? (int)$_POST['plant_id'] : 0;

if (!$plant_id) {
    jsonError(400, "plant_id required");
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    jsonError(400, "Invalid image file");
}

$file = $_FILES['image'];

// Limite de tamanho: 5 MB
if ($file['size'] > 5 * 1024 * 1024) {
    jsonError(400, "Image must be 5MB or less");
}

// Validação do mime type real (lê os bytes do ficheiro em vez de confiar na extensão)
$info = getimagesize($file['tmp_name']);
if ($info === false) {
    jsonError(400, "File is not a valid image");
}
$mime = $info['mime'];

$allowed = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

if (!isset($allowed[$mime])) {
    jsonError(400, "Only JPG, PNG, or WEBP images allowed");
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
        jsonError(404, "Plant not found or not owned by user");
        exit;
    }

    // Gerar nome único e paths (prefixo plant_id + 8 hex aleatórios para evitar colisões)
    $filename   = $plant_id . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $db_path    = "plants/$filename";
    $uploads_root = __DIR__ . "/../../Frontend/Assets/Uploads";
    $disk_path  = "$uploads_root/$db_path";

    // Criar pasta plants/ se não existir
    if (!is_dir(dirname($disk_path))) {
        if (!mkdir(dirname($disk_path), 0775, true)) {
            jsonError(500, "Could not create uploads directory");
            exit;
        }
    }

    // Mover ficheiro
    if (!move_uploaded_file($file['tmp_name'], $disk_path)) {
        jsonError(500, "Could not save image");
        exit;
    }

    // Apagar imagem antiga, se existir
    if ($plant['plant_image_path']) {
        $old_disk = "$uploads_root/" . $plant['plant_image_path'];
        if (file_exists($old_disk)) {
            if (!unlink($old_disk)) {
                error_log("Failed to delete old plant image: $old_disk");
            };
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
