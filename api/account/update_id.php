<?php
require_once __DIR__ . '/../../config/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'resident') {
    header('Location: ../../public/login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = safeBrgy_db_connect();

    try {
        // Check if file is uploaded
        if (!isset($_FILES['validIdFile']) || $_FILES['validIdFile']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('No file uploaded or upload error');
        }

        $file = $_FILES['validIdFile'];
        
        // Validate file size (max 10MB)
        $maxSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            throw new Exception('File size exceeds 10MB limit');
        }

        // Validate file type
        $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimes)) {
            throw new Exception('Invalid file type. Only JPG, PNG, and PDF are allowed');
        }

        // Create upload directory if it doesn't exist
        $uploadDir = __DIR__ . '/../../uploads/valid_ids/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'id_' . $user_id . '_' . time() . '.' . $ext;
        $filepath = $uploadDir . $filename;
        $relativePath = '/uploads/valid_ids/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to save file');
        }

        // Update database
        $stmt = $pdo->prepare('
            UPDATE residents 
            SET valid_id_path = ?, updated_at = NOW()
            WHERE user_id = ?
        ');
        $stmt->execute([$relativePath, $user_id]);

        $_SESSION['account_success'] = 'Valid ID uploaded successfully';
        
    } catch (Exception $e) {
        $_SESSION['account_error'] = 'Error uploading ID: ' . $e->getMessage();
    }

    header('Location: ../../public/public-pages/account.php');
    exit;
} else {
    header('Location: ../../public/public-pages/account.php');
    exit;
}
?>
