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
        // Get preference values (checked = 1, unchecked = 0)
        $notifDocUpdate = isset($_POST['notifDocUpdate']) ? 1 : 0;
        $notifAnnouncements = isset($_POST['notifAnnouncements']) ? 1 : 0;
        $notifReports = isset($_POST['notifReports']) ? 1 : 0;

        // Store preferences in session for now (or create a user_preferences table)
        $_SESSION['notifications'] = [
            'document_updates' => $notifDocUpdate,
            'announcements' => $notifAnnouncements,
            'reports' => $notifReports
        ];

        // TODO: Create a user_notification_preferences table and save to database
        // For now, just save to session
        
        $_SESSION['account_success'] = 'Notification preferences updated successfully';
        
    } catch (Exception $e) {
        $_SESSION['account_error'] = 'Error updating preferences: ' . $e->getMessage();
    }

    header('Location: ../../public/public-pages/account.php');
    exit;
} else {
    header('Location: ../../public/public-pages/account.php');
    exit;
}
?>
