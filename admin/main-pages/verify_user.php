<?php
require_once __DIR__ . '/../admin_protect.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/mailer.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['user_id'] ?? null;
$action = $data['action'] ?? null;
$reason = $data['reason'] ?? '';

if (!$userId || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    exit;
}

$pdo = safeBrgy_db_connect();

if ($action === 'approve') {
    $stmt = $pdo->prepare('UPDATE users SET is_verified = 1, updated_at = NOW() WHERE id = :id');
    $result = $stmt->execute(['id' => $userId]);

    if ($result) {
        // Get user details for email
        $stmt = $pdo->prepare('SELECT u.email, r.first_name, r.last_name FROM users u LEFT JOIN residents r ON u.id = r.user_id WHERE u.id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if ($user) {
            $name = $user['first_name'] . ' ' . $user['last_name'];
            $subject = 'SafeBrgy - Account Approved';
            $htmlBody = "
            <html>
            <body>
                <h2>Congratulations, $name!</h2>
                <p>Your account has been successfully approved.</p>
                <p>Approval Date: " . date('F d, Y') . "</p>
                <p>You can now log in to your SafeBrgy account.</p>
            </body>
            </html>
            ";
            sendMail($user['email'], $subject, $htmlBody);
        }

        // Log action
        $stmt = $pdo->prepare('INSERT INTO admin_logs (admin_id, action, meta) VALUES (?, ?, ?)');
        $stmt->execute([$_SESSION['admin_user']['id'], 'approve_user', json_encode(['user_id' => $userId])]);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
} elseif ($action === 'reject') {
    // Get user details before deletion
    $stmt = $pdo->prepare('SELECT u.email, r.first_name, r.last_name FROM users u LEFT JOIN residents r ON u.id = r.user_id WHERE u.id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
    $result = $stmt->execute(['id' => $userId]);

    if ($result && $user) {
        $name = $user['first_name'] . ' ' . $user['last_name'];
        $subject = 'SafeBrgy - Account Rejected';
        $htmlBody = "
        <html>
        <body>
            <h2>Account Rejection Notice</h2>
            <p>Dear $name,</p>
            <p>We regret to inform you that your account registration has been rejected.</p>
            <p><strong>Reason:</strong> $reason</p>
            <p>Please contact the barangay office for more information.</p>
        </body>
        </html>
        ";
        sendMail($user['email'], $subject, $htmlBody);

        // Log action
        $stmt = $pdo->prepare('INSERT INTO admin_logs (admin_id, action, meta) VALUES (?, ?, ?)');
        $stmt->execute([$_SESSION['admin_user']['id'], 'reject_user', json_encode(['user_id' => $userId, 'reason' => $reason])]);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
}

exit;
