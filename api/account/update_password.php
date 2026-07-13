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
        $currentPassword = $_POST['currentPassword'] ?? '';
        $newPassword = $_POST['newPassword'] ?? '';
        $confirmPassword = $_POST['confirmPassword'] ?? '';

        // Validation
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            throw new Exception('All password fields are required');
        }

        // Check password confirmation
        if ($newPassword !== $confirmPassword) {
            throw new Exception('New password and confirm password do not match');
        }

        // Validate password strength
        if (strlen($newPassword) < 8) {
            throw new Exception('Password must be at least 8 characters long');
        }

        if (!preg_match('/[A-Z]/', $newPassword)) {
            throw new Exception('Password must contain at least one uppercase letter');
        }

        if (!preg_match('/[a-z]/', $newPassword)) {
            throw new Exception('Password must contain at least one lowercase letter');
        }

        if (!preg_match('/[0-9]/', $newPassword)) {
            throw new Exception('Password must contain at least one number');
        }

        // Verify current password
        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            throw new Exception('Current password is incorrect');
        }

        // Hash and update new password
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$newHash, $user_id]);

        $_SESSION['account_success'] = 'Password changed successfully';
        
    } catch (Exception $e) {
        $_SESSION['account_error'] = $e->getMessage();
    }

    header('Location: ../../public/public-pages/account.php');
    exit;
} else {
    header('Location: ../../public/public-pages/account.php');
    exit;
}
?>
