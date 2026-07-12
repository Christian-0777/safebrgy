<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/mailer.php';
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
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Validation
        if (empty($subject) || empty($message)) {
            throw new Exception('Subject and message are required');
        }

        // Get user info
        $stmt = $pdo->prepare('SELECT email, name FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        // TODO: Send email to barangay office
        // For now, just store in database or log

        $_SESSION['account_success'] = 'Your message has been sent to the barangay office. We will get back to you soon.';
        
    } catch (Exception $e) {
        $_SESSION['account_error'] = 'Error sending message: ' . $e->getMessage();
    }

    header('Location: ../../public/public-pages/account.php');
    exit;
} else {
    header('Location: ../../public/public-pages/account.php');
    exit;
}
?>
