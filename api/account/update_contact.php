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
        $phone = trim($_POST['phone'] ?? '');
        $mobileNumber = trim($_POST['mobileNumber'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $emergencyContact = trim($_POST['emergencyContact'] ?? '');
        $emergencyPhone = trim($_POST['emergencyPhone'] ?? '');

        // Validation
        if (empty($phone) || empty($email)) {
            throw new Exception('Phone number and email are required');
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address');
        }

        // Check if email is already taken by another user
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            throw new Exception('Email already in use');
        }

        // Validate phone format (basic)
        if (!preg_match('/^[0-9\-\+\s\(\)]+$/', $phone)) {
            throw new Exception('Invalid phone number format');
        }

        // Update users table
        $stmt = $pdo->prepare('UPDATE users SET email = ?, phone = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$email, $phone, $user_id]);

        // Update residents table
        $stmt = $pdo->prepare('
            UPDATE residents 
            SET mobile_number = ?, emergency_contact_name = ?, 
                emergency_contact_number = ?, updated_at = NOW()
            WHERE user_id = ?
        ');
        $stmt->execute([$mobileNumber, $emergencyContact, $emergencyPhone, $user_id]);

        // Update session
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['phone'] = $phone;

        $_SESSION['account_success'] = 'Contact information updated successfully';
        
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
