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
        $firstName = trim($_POST['firstName'] ?? '');
        $middleName = trim($_POST['middleName'] ?? '');
        $lastName = trim($_POST['lastName'] ?? '');
        $suffix = trim($_POST['suffix'] ?? '');
        $gender = trim($_POST['gender'] ?? '');
        $birthdate = trim($_POST['birthdate'] ?? '');
        $civilStatus = trim($_POST['civilStatus'] ?? '');
        $nationality = trim($_POST['nationality'] ?? '');
        $occupation = trim($_POST['occupation'] ?? '');
        $profilePath = null;

        if (isset($_FILES['profilePhoto']) && $_FILES['profilePhoto']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['profilePhoto'];
            $allowedMimes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];
            if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5 * 1024 * 1024) {
                throw new Exception('Profile photo must be a valid image under 5MB.');
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            if (!isset($allowedMimes[$mimeType]) || @getimagesize($file['tmp_name']) === false) {
                throw new Exception('Only JPG, PNG, and WebP profile photos are allowed.');
            }

            $uploadDir = __DIR__ . '/../../uploads/profile_images/';
            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                throw new Exception('Unable to create the profile photo directory.');
            }
            $filename = 'profile_' . $user_id . '_' . bin2hex(random_bytes(8)) . '.' . $allowedMimes[$mimeType];
            $profilePath = 'uploads/profile_images/' . $filename;
            if (!move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                throw new Exception('Unable to save the profile photo.');
            }
        }

        // Validation
        if (empty($firstName) || empty($lastName)) {
            throw new Exception('First name and last name are required');
        }

        // Validate birthdate format if provided
        if (!empty($birthdate)) {
            $birthdateObj = DateTime::createFromFormat('Y-m-d', $birthdate);
            if (!$birthdateObj) {
                throw new Exception('Invalid birthdate format');
            }
        }

        // Update residents table
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('
            UPDATE residents 
            SET first_name = ?, middle_name = ?, last_name = ?, 
                gender = ?, birthdate = ?, civil_status = ?, 
                nationality = ?, occupation = ?,
                profile_image_path = COALESCE(?, profile_image_path), updated_at = NOW()
            WHERE user_id = ?
        ');
        $stmt->execute([$firstName, $middleName, $lastName, $gender, $birthdate, $civilStatus, $nationality, $occupation, $profilePath, $user_id]);
        if ($profilePath) {
            $pdo->prepare('UPDATE users SET profile_image = ?, updated_at = NOW() WHERE id = ? AND role = \'resident\'')->execute([$profilePath, $user_id]);
        }
        $pdo->commit();

        // Update session with new name
        $_SESSION['user']['name'] = trim("$firstName $lastName");
        
        $_SESSION['account_success'] = 'Personal information updated successfully';
        
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if (isset($uploadDir, $filename) && is_file($uploadDir . $filename)) {
            @unlink($uploadDir . $filename);
        }
        $_SESSION['account_error'] = $e->getMessage();
    }

    header('Location: ../../public/public-pages/account.php');
    exit;
} else {
    header('Location: ../../public/public-pages/account.php');
    exit;
}
?>
