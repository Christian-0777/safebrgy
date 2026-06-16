<?php
require_once __DIR__ . '/../../config/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'resident') {
    header('Location: ../../public/login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$pdo = safeBrgy_db_connect();

try {
    // Get user data
    $stmt = $pdo->prepare('
        SELECT u.id, u.email, u.phone, u.created_at,
               r.* 
        FROM users u
        LEFT JOIN residents r ON u.id = r.user_id
        WHERE u.id = ?
    ');
    $stmt->execute([$user_id]);
    $userData = $stmt->fetch();

    if (!$userData) {
        throw new Exception('User data not found');
    }

    // Get user's requests
    $stmt = $pdo->prepare('SELECT * FROM requests WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$user_id]);
    $requests = $stmt->fetchAll();

    // Get user's reports
    $stmt = $pdo->prepare('SELECT * FROM reports WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$user_id]);
    $reports = $stmt->fetchAll();

    // Compile all data
    $allData = [
        'export_date' => date('Y-m-d H:i:s'),
        'user' => $userData,
        'requests' => $requests,
        'reports' => $reports
    ];

    // Convert to JSON
    $jsonData = json_encode($allData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    // Send as download
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="safebrgy-personal-data-' . date('Y-m-d') . '.json"');
    header('Content-Length: ' . strlen($jsonData));
    
    echo $jsonData;
    exit;

} catch (Exception $e) {
    $_SESSION['account_error'] = 'Error downloading data: ' . $e->getMessage();
    header('Location: ../../public/public-pages/account.php');
    exit;
}
?>
