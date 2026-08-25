<?php
/**
 * Guest endpoint compatibility helpers.
 * The canonical connection and schema bootstrap lives in /config/db.php.
 */
require_once __DIR__ . '/../../config/db.php';

// Create PDO connection
function getDBConnection() {
    return safeBrgy_db_connect();
}

// Generate case number in format CASE-YYYYMMDD-XXXX
function generateCaseNumber() {
    $date = date('Ymd');
    $random = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
    return "CASE-{$date}-{$random}";
}

// Sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Handle file upload
function handleFileUpload($file, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], $maxSize = 5 * 1024 * 1024) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error'];
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, WebP allowed'];
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File size exceeds 5MB limit'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('report_', true) . '.' . $extension;
    $uploadDirectory = __DIR__ . '/../upload/';
    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true)) {
        return ['success' => false, 'message' => 'Failed to create upload directory'];
    }
    $uploadPath = $uploadDirectory . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => false, 'message' => 'Failed to save file'];
    }
    
    return ['success' => true, 'filename' => $filename, 'path' => 'upload/' . $filename];
}

// JSON response helper
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}