<?php
require_once __DIR__ . '/../../config/db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'resident') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must be logged in as a resident.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['profilePhoto'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No profile photo was received.']);
    exit;
}

$file = $_FILES['profilePhoto'];
$maxSize = 5 * 1024 * 1024;
$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp'
];

try {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The profile photo upload failed.');
    }

    if ($file['size'] > $maxSize) {
        throw new RuntimeException('Profile photo must be 5MB or smaller.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!isset($allowedMimes[$mimeType]) || @getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('Only JPG, PNG, and WebP images are allowed.');
    }

    $uploadDir = __DIR__ . '/../../uploads/profile_images/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        throw new RuntimeException('Unable to create the profile photo directory.');
    }

    $userId = (int) $_SESSION['user']['id'];
    $filename = 'profile_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $allowedMimes[$mimeType];
    $relativePath = 'uploads/profile_images/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        throw new RuntimeException('Unable to save the profile photo.');
    }

    $pdo = safeBrgy_db_connect();
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('UPDATE users SET profile_image = ?, updated_at = NOW() WHERE id = ? AND role = \'resident\'');
    $stmt->execute([$relativePath, $userId]);
    $savedImage = $pdo->prepare('SELECT profile_image FROM users WHERE id = ? AND role = \'resident\'');
    $savedImage->execute([$userId]);
    if ($savedImage->fetchColumn() !== $relativePath) {
        throw new RuntimeException('Profile photo could not be linked to your account.');
    }
    $pdo->prepare('UPDATE residents SET profile_image_path = ? WHERE user_id = ?')->execute([$relativePath, $userId]);
    $pdo->commit();

    echo json_encode(['success' => true, 'path' => $relativePath]);
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if (isset($uploadDir, $filename) && is_file($uploadDir . $filename)) {
        @unlink($uploadDir . $filename);
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $exception->getMessage()]);
}
