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
        $reason = trim($_POST['reason'] ?? '');

        // TODO: Create a user_status table or add status column to users table
        // For now, we'll just add is_active column logic (needs migration)

        // Option 1: Add is_active column and set it to 0 (needs DB migration)
        // $stmt = $pdo->prepare('UPDATE users SET is_active = 0, updated_at = NOW() WHERE id = ?');
        // $stmt->execute([$user_id]);

        // For now, just log the deactivation request
        // This should be handled after creating proper schema

        // Destroy session
        session_destroy();
        
        // Redirect to login
        header('Location: ../../public/login.php?message=account_deactivated');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['account_error'] = 'Error deactivating account: ' . $e->getMessage();
        header('Location: ../../public/public-pages/account.php');
        exit;
    }
} else {
    header('Location: ../../public/public-pages/account.php');
    exit;
}
?>
