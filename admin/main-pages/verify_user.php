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
$notificationStatus = ['email_sent' => false, 'sms_sent' => false];

if ($action === 'approve') {
    $stmt = $pdo->prepare('UPDATE users SET is_verified = 1, updated_at = NOW() WHERE id = :id');
    $result = $stmt->execute(['id' => $userId]);

    if ($result) {
        $stmt = $pdo->prepare('SELECT u.email, u.phone, r.first_name, r.last_name, r.mobile_number FROM users u LEFT JOIN residents r ON u.id = r.user_id WHERE u.id = ?');
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if ($user) {
            $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: 'Resident';
            $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
            $subject = 'SafeBrgy - Account Approved';
            $htmlBody = "
            <html>
            <body>
                <h2>Congratulations, $safeName!</h2>
                <p>Your account has been successfully approved.</p>
                <p>Approval Date: " . date('F d, Y') . "</p>
                <p>You can now log in to your SafeBrgy account.</p>
            </body>
            </html>
            ";
            $plainBody = "Congratulations, {$name}!\n\nYour SafeBrgy account has been successfully approved.\nApproval Date: " . date('F d, Y') . "\nYou can now log in to your SafeBrgy account.";
            $emailSent = sendMail($user['email'], $subject, $htmlBody, $plainBody);
            $smsSent = sendSms($user['mobile_number'] ?: ($user['phone'] ?? ''), 'Your SafeBrgy account has been approved. You can now log in.');
            $notificationStatus = ['email_sent' => $emailSent, 'sms_sent' => $smsSent];

            try {
                logNotificationEvent([
                    'user_id' => $userId,
                    'email' => $user['email'],
                    'mobile_number' => $user['mobile_number'] ?: ($user['phone'] ?? null),
                    'event_type' => 'account_approval',
                    'event_meta' => ['action' => 'approve'],
                    'email_sent' => $emailSent,
                    'sms_sent' => $smsSent,
                    'status' => $emailSent && $smsSent ? 'sent' : 'partial',
                ]);
            } catch (Throwable $exception) {
                error_log('Approval notification log failed: ' . $exception->getMessage());
            }
        }

        // Log action
        $stmt = $pdo->prepare('INSERT INTO admin_logs (admin_id, action, meta) VALUES (?, ?, ?)');
        $stmt->execute([$_SESSION['admin_user']['id'], 'approve_user', json_encode(['user_id' => $userId])]);

        echo json_encode(['success' => true, 'notifications' => $notificationStatus]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
} elseif ($action === 'reject') {
    $stmt = $pdo->prepare('SELECT u.email, u.phone, r.first_name, r.last_name, r.mobile_number FROM users u LEFT JOIN residents r ON u.id = r.user_id WHERE u.id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
    $result = $stmt->execute(['id' => $userId]);

    if ($result && $user) {
        $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: 'Resident';
        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeReason = htmlspecialchars($reason, ENT_QUOTES, 'UTF-8');
        $subject = 'SafeBrgy - Account Rejected';
        $htmlBody = "
        <html>
        <body>
            <h2>Account Rejection Notice</h2>
            <p>Dear $safeName,</p>
            <p>We regret to inform you that your account registration has been rejected.</p>
            <p><strong>Reason:</strong> $safeReason</p>
            <p>Please contact the barangay office for more information.</p>
        </body>
        </html>
        ";
        $plainBody = "Dear {$name},\n\nYour SafeBrgy account registration has been rejected.\nReason: {$reason}\n\nPlease contact the barangay office for more information.";
        $emailSent = sendMail($user['email'], $subject, $htmlBody, $plainBody);
        $smsSent = sendSms($user['mobile_number'] ?: ($user['phone'] ?? ''), "Your SafeBrgy account registration was rejected. Reason: {$reason}");
        $notificationStatus = ['email_sent' => $emailSent, 'sms_sent' => $smsSent];

        try {
            logNotificationEvent([
                'user_id' => $userId,
                'email' => $user['email'],
                'mobile_number' => $user['mobile_number'] ?: ($user['phone'] ?? null),
                'event_type' => 'account_rejection',
                'event_meta' => ['action' => 'reject', 'reason' => $reason],
                'email_sent' => $emailSent,
                'sms_sent' => $smsSent,
                'status' => $emailSent && $smsSent ? 'sent' : 'partial',
            ]);
        } catch (Throwable $exception) {
            error_log('Rejection notification log failed: ' . $exception->getMessage());
        }

        // Log action
        $stmt = $pdo->prepare('INSERT INTO admin_logs (admin_id, action, meta) VALUES (?, ?, ?)');
        $stmt->execute([$_SESSION['admin_user']['id'], 'reject_user', json_encode(['user_id' => $userId, 'reason' => $reason])]);

        echo json_encode(['success' => true, 'notifications' => $notificationStatus]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
}

exit;
