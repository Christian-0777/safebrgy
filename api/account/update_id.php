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
        $files = [
            'front' => $_FILES['validIdFrontFile'] ?? null,
            'back' => $_FILES['validIdBackFile'] ?? null,
        ];
        $maxSize = 10 * 1024 * 1024;
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        $storedPaths = [];

        foreach ($files as $side => $file) {
            if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('Both the front and back of the ID are required');
            }
            if ($file['size'] > $maxSize) {
                throw new Exception('The ' . $side . ' ID file exceeds the 10MB limit');
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if (!in_array($mimeType, $allowedMimes, true)) {
                throw new Exception('The ' . $side . ' ID must be a JPG, PNG, or WebP image');
            }

            $uploadDir = __DIR__ . '/../../uploads/id/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'id_' . $user_id . '_' . $side . '_' . time() . '.' . $ext;
            $filepath = $uploadDir . $filename;
            $relativePath = '/uploads/id/' . $filename;

            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception('Failed to save the ' . $side . ' ID file');
            }
            $storedPaths[$side] = $relativePath;
        }

        $stmt = $pdo->prepare('
            UPDATE residents 
            SET valid_id_path = ?, valid_id_back_path = ?, updated_at = NOW()
            WHERE user_id = ?
        ');
        $stmt->execute([$storedPaths['front'], $storedPaths['back'], $user_id]);

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
