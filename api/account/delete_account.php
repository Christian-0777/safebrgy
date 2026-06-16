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

        // Verify confirmation
        $confirmText = trim($_POST['confirm'] ?? '');
        if ($confirmText !== 'DELETE') {
            throw new Exception('Confirmation text does not match');
        }

        // Start transaction
        $pdo->beginTransaction();

        // Delete all user data (cascade should handle related records)
        $stmt = $pdo->prepare('DELETE FROM residents WHERE user_id = ?');
        $stmt->execute([$user_id]);

        // Delete user account
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$user_id]);

        $pdo->commit();

        // Destroy session
        session_destroy();
        
        // Redirect to home page
        header('Location: ../../index.php?message=account_deleted');
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['account_error'] = 'Error deleting account: ' . $e->getMessage();
        header('Location: ../../public/public-pages/account.php');
        exit;
    }
} else {
    header('Location: ../../public/public-pages/account.php');
    exit;
}
?>
