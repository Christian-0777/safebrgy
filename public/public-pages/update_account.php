<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mailer.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'resident') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = safeBrgy_db_connect();

    // Update user info
    if (isset($_POST['fullName'])) {
        // Assuming fullName is first last, but for simplicity, update email and phone
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);

        // Check if email is already taken by another user
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $_SESSION['account_error'] = 'Email already in use';
            header('Location: account.php');
            exit;
        }

        // Update users table
        $stmt = $pdo->prepare('UPDATE users SET email = ?, phone = ? WHERE id = ?');
        $stmt->execute([$email, $phone, $user_id]);

        // Update residents table if phone changed
        $stmt = $pdo->prepare('UPDATE residents SET mobile_number = ? WHERE user_id = ?');
        $stmt->execute([$phone, $user_id]);

        // Update session
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['phone'] = $phone;

        $_SESSION['account_success'] = 'Account updated successfully';
    }

    // Change password
    if (!empty($_POST['currentPassword']) && !empty($_POST['newPassword'])) {
        $current = $_POST['currentPassword'];
        $new = $_POST['newPassword'];
        $confirm = $_POST['confirmPassword'];

        if ($new !== $confirm) {
            $_SESSION['account_error'] = 'New passwords do not match';
            header('Location: account.php');
            exit;
        }

        // Verify current password
        $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        if (!password_verify($current, $user['password_hash'])) {
            $_SESSION['account_error'] = 'Current password is incorrect';
            header('Location: account.php');
            exit;
        }

        // Update password
        $new_hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([$new_hash, $user_id]);

        $_SESSION['account_success'] = 'Password changed successfully';
    }

    header('Location: account.php');
    exit;
} else {
    header('Location: account.php');
    exit;
}
?>