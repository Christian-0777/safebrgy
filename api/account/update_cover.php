<?php
require_once __DIR__ . '/../../config/db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'resident') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must be logged in as a resident.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['coverPhoto'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No cover photo was received.']);
    exit;
}

$file = $_FILES['coverPhoto'];
$maxSize = 10 * 1024 * 1024;
$allowedMimes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp'
];

try {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The cover photo upload failed.');
    }

    if ($file['size'] > $maxSize) {
        throw new RuntimeException('Cover photo must be 10MB or smaller.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!isset($allowedMimes[$mimeType]) || @getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('Only JPG, PNG, GIF, and WebP images are allowed.');
    }

    $uploadDir = __DIR__ . '/../../uploads/cover_photos/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        throw new RuntimeException('Unable to create the cover photo directory.');
    }

    $userId = (int) $_SESSION['user']['id'];
    $filename = 'cover_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $allowedMimes[$mimeType];
    $relativePath = 'uploads/cover_photos/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
        throw new RuntimeException('Unable to save the cover photo.');
    }

    $pdo = safeBrgy_db_connect();
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('UPDATE users SET cover_photo = ?, updated_at = NOW() WHERE id = ? AND role = \'resident\'');
    $stmt->execute([$relativePath, $userId]);
    $savedCover = $pdo->prepare('SELECT cover_photo FROM users WHERE id = ? AND role = \'resident\'');
    $savedCover->execute([$userId]);
    if ($savedCover->fetchColumn() !== $relativePath) {
        throw new RuntimeException('Cover photo could not be linked to your account.');
    }
    $pdo->prepare('UPDATE residents SET cover_photo_path = ? WHERE user_id = ?')->execute([$relativePath, $userId]);
    $pdo->commit();

    echo json_encode(['success' => true, 'path' => $relativePath]);
} catch (Throwable $exception) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $exception->getMessage()]);
}
