<?php
require_once __DIR__ . '/config/mailer.php';
require_once __DIR__ . '/config/db.php';

$status = null;
$recipient = '';
$message = 'Test SMS from SafeBRGY.';
$selectedResidentId = '';
$residents = [];

function fetchResidents(): array
{
    try {
        $pdo = safeBrgy_db_connect();
        $stmt = $pdo->prepare('SELECT id, first_name, last_name, mobile_number FROM residents WHERE mobile_number IS NOT NULL AND TRIM(mobile_number) != "" ORDER BY last_name, first_name');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $exception) {
        error_log('Resident fetch failed: ' . $exception->getMessage());
        return [];
    }
}

$residents = fetchResidents();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedResidentId = trim((string) ($_POST['resident_id'] ?? ''));
    $recipient = trim((string) ($_POST['recipient'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));

    if ($selectedResidentId !== '') {
        foreach ($residents as $resident) {
            if ((string) $resident['id'] === $selectedResidentId) {
                $recipient = $resident['mobile_number'] ?: $recipient;
                break;
            }
        }
    }

    if ($recipient === '' || $message === '') {
        $status = [
            'success' => false,
            'message' => 'Phone number and message are required.',
        ];
    } else {
        $sent = sendSms($recipient, $message);
        $status = [
            'success' => $sent,
            'message' => $sent
                ? 'SMS request was sent. Check your device or gateway logs.'
                : 'SMS send failed. Review server logs and Textbee configuration.',
        ];
    }
}

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeBRGY SMS Tester</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; color: #111827; margin: 0; padding: 0; }
        .page { max-width: 720px; margin: 40px auto; padding: 24px; background: #ffffff; border: 1px solid #d1d5db; border-radius: 12px; box-shadow: 0 12px 32px rgba(15, 23, 42, 0.08); }
        h1 { margin-top: 0; }
        label { display: block; margin: 18px 0 6px; font-weight: 600; }
        input[type="text"], textarea { width: 100%; padding: 12px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; }
        textarea { min-height: 160px; resize: vertical; }
        button { margin-top: 18px; padding: 12px 20px; font-size: 15px; color: #ffffff; background: #2563eb; border: none; border-radius: 8px; cursor: pointer; }
        button:hover { background: #1d4ed8; }
        .status { margin: 20px 0; padding: 14px 16px; border-radius: 10px; }
        .status.success { background: #ecfdf5; color: #166534; border: 1px solid #d1fae5; }
        .status.error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .note { margin-top: 24px; color: #475569; font-size: 14px; line-height: 1.6; }
        a { color: #2563eb; text-decoration: none; }
    </style>
</head>
<body>
    <div class="page">
        <h1>SMS Tester</h1>
        <p>Use this page to send a test SMS through the configured Textbee gateway.</p>

        <?php if ($status !== null): ?>
            <div class="status <?php echo $status['success'] ? 'success' : 'error'; ?>">
                <?php echo escape($status['message']); ?>
            </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <label for="resident_id">Resident</label>
            <select id="resident_id" name="resident_id">
                <option value="">-- Select a resident to autofill number --</option>
                <?php foreach ($residents as $resident): ?>
                    <option value="<?php echo escape((string) $resident['id']); ?>" data-mobile="<?php echo escape($resident['mobile_number']); ?>" <?php echo $selectedResidentId === (string) $resident['id'] ? 'selected' : ''; ?>>
                        <?php echo escape(trim($resident['last_name'] . ', ' . $resident['first_name'])); ?> &mdash; <?php echo escape($resident['mobile_number']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="recipient">Recipient mobile number</label>
            <input id="recipient" name="recipient" type="text" placeholder="e.g. 09171234567 or +639171234567" value="<?php echo escape($recipient); ?>" />

            <label for="message">Message</label>
            <textarea id="message" name="message"><?php echo escape($message); ?></textarea>

            <button type="submit">Send SMS</button>
        </form>

        <script>
            document.getElementById('resident_id').addEventListener('change', function () {
                var selectedOption = this.options[this.selectedIndex];
                var mobileNumber = selectedOption.getAttribute('data-mobile') || '';
                if (mobileNumber) {
                    document.getElementById('recipient').value = mobileNumber;
                }
            });
        </script>

        <div class="note">
            <p>Make sure the following values are set in your environment or <code>.env</code> file:</p>
            <ul>
                <li><code>TEXTBEE_DEVICE_ID</code></li>
                <li><code>TEXTBEE_API_KEY</code></li>
            </ul>
            <p>If delivery fails, check your server error log for the Textbee response or cURL errors.</p>
        </div>
    </div>
</body>
</html>
