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
        $feedbackType = trim($_POST['type'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Validation
        if (empty($feedbackType) || empty($message)) {
            throw new Exception('Feedback type and message are required');
        }

        $validTypes = ['bug', 'feature', 'improvement', 'general'];
        if (!in_array($feedbackType, $validTypes)) {
            throw new Exception('Invalid feedback type');
        }

        // TODO: Store feedback in database (create feedback table)
        // For now, just show success message

        $_SESSION['account_success'] = 'Thank you for your feedback! We appreciate your input to help us improve SafeBrgy.';
        
    } catch (Exception $e) {
        $_SESSION['account_error'] = 'Error submitting feedback: ' . $e->getMessage();
    }

    header('Location: ../../public/public-pages/account.php');
    exit;
} else {
    header('Location: ../../public/public-pages/account.php');
    exit;
}
?>
