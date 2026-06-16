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
        $issueType = trim($_POST['type'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $page = trim($_POST['page'] ?? '');

        // Validation
        if (empty($issueType) || empty($description)) {
            throw new Exception('Issue type and description are required');
        }

        $validTypes = ['technical', 'security', 'other'];
        if (!in_array($issueType, $validTypes)) {
            throw new Exception('Invalid issue type');
        }

        // TODO: Store issue report in database
        // For now, just show success message

        $_SESSION['account_success'] = 'Thank you for reporting this issue. Our technical team will investigate and get back to you shortly.';
        
    } catch (Exception $e) {
        $_SESSION['account_error'] = 'Error reporting issue: ' . $e->getMessage();
    }

    header('Location: ../../public/public-pages/account.php');
    exit;
} else {
    header('Location: ../../public/public-pages/account.php');
    exit;
}
?>
