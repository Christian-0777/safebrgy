<?php
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Twilio\Rest\Client as TwilioClient;
// use SendGrid\SendGrid;
// use SendGrid\Mail\Mail as SendGridMail;

if (!defined('SAFE_BRGY_MAILER_LOADED')) {
    define('SAFE_BRGY_MAILER_LOADED', true);
}

function getMailerConfig(): array
{
    return [
        'smtp_host' => getenv('SMTP_HOST') ?: '',
        'smtp_port' => getenv('SMTP_PORT') ?: '587',
        'smtp_username' => getenv('SMTP_USERNAME') ?: '',
        'smtp_password' => getenv('SMTP_PASSWORD') ?: '',
        'smtp_encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls',
        'mail_from_address' => getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@safebrgy.local',
        'mail_from_name' => getenv('MAIL_FROM_NAME') ?: 'SafeBRGY',
        'sendgrid_api_key' => getenv('SENDGRID_API_KEY') ?: '',
        'sendgrid_from_email' => getenv('SENDGRID_FROM_EMAIL') ?: getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@safebrgy.local',
        'sendgrid_from_name' => getenv('SENDGRID_FROM_NAME') ?: getenv('MAIL_FROM_NAME') ?: 'SafeBRGY',
        'twilio_account_sid' => getenv('TWILIO_ACCOUNT_SID') ?: '',
        'twilio_auth_token' => getenv('TWILIO_AUTH_TOKEN') ?: '',
        'twilio_from_number' => getenv('TWILIO_FROM_NUMBER') ?: '',
        'httpsms_api_key' => getenv('HTTPSMS_API_KEY') ?: '',
        'httpsms_api_url' => getenv('HTTPSMS_API_URL') ?: '',
        'httpsms_sender' => getenv('HTTPSMS_SENDER') ?: '',
    ];
}

function buildEmailLayout(string $contentHtml): string
{
    $brand = htmlspecialchars(getMailerConfig()['mail_from_name'] ?: 'SafeBRGY', ENT_QUOTES, 'UTF-8');

    return '<!DOCTYPE html>'
         . '<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head><body style="margin:0;padding:0;background:#f3f4f8;font-family:Arial,Helvetica,sans-serif;">'
         . '<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f3f4f8;width:100%;min-width:100%;"><tr><td align="center" style="padding:24px;">'
         . '<table role="presentation" cellpadding="0" cellspacing="0" width="600" style="background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;max-width:600px;width:100%;">'
         . '<tr><td style="padding:20px 24px;background:#ffffff;display:flex;justify-content:space-between;align-items:center;">'
         . '<div style="font-size:20px;font-weight:700;color:#111827;">' . $brand . '</div>'
         . '<div style="font-size:14px;color:#6b7280;">SafeBRGY</div>'
         . '</td></tr>'
         . '<tr><td style="height:1px;background:#e5e7eb;"></td></tr>'
         . '<tr><td style="padding:32px 24px;color:#111827;line-height:1.6;">' . $contentHtml . '</td></tr>'
         . '<tr><td style="height:1px;background:#e5e7eb;"></td></tr>'
         . '<tr><td style="padding:16px 24px;text-align:center;font-size:12px;color:#6b7280;background:#f8fafc;">© 2026 Barangay San Jose — All rights reserved.</td></tr>'
         . '</table></td></tr></table></body></html>';
}

function normalizePhoneNumber(string $number): ?string
{
    $clean = preg_replace('/[^0-9+]/', '', trim($number));
    if ($clean === '') {
        return null;
    }

    if (str_starts_with($clean, '+')) {
        $clean = substr($clean, 1);
    }

    if (str_starts_with($clean, '0')) {
        $clean = substr($clean, 1);
    }

    if (str_starts_with($clean, '63')) {
        $clean = substr($clean, 2);
    }

    if (strlen($clean) !== 10 || $clean[0] !== '9') {
        return null;
    }

    return '+63' . $clean;
}

function maskPhoneNumber(string $phone): string
{
    $normalized = normalizePhoneNumber($phone);
    if (!$normalized) {
        return htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
    }

    return '+63 ' . substr($normalized, 3, 3) . ' *** ' . substr($normalized, -4);
}

function sendMail(string $recipient, string $subject, string $htmlBody, string $plainBody = null): bool
{
    $config = getMailerConfig();
    $plainBody = $plainBody ?? strip_tags(str_replace(['<br>', '<br/>', '<p>', '</p>'], ["\n", "\n", "\n", "\n"], $htmlBody));

    if (!empty($config['smtp_host']) && !empty($config['smtp_username'])) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp_username'];
            $mail->Password = $config['smtp_password'];
            $mail->SMTPSecure = $config['smtp_encryption'] ?: PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int) $config['smtp_port'];
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($config['mail_from_address'], $config['mail_from_name']);
            $mail->addAddress($recipient);
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = buildEmailLayout($htmlBody);
            $mail->AltBody = $plainBody;
            if ($mail->send()) {
                return true;
            }
            error_log('SMTP mail failed to send to ' . $recipient . ': ' . $mail->ErrorInfo);
        } catch (PHPMailerException $exception) {
            error_log('SMTP mail error: ' . $exception->getMessage());
        }
    }

    return false;
}

function sendHttpSms(string $recipient, string $message): bool
{
    $config = getMailerConfig();
    $apiKey = $config['httpsms_api_key'];
    $sender = $config['httpsms_sender'];
    $apiUrl = $config['httpsms_api_url'] ?: 'https://api.httpsms.com/v1/sms/send';

    if (empty($apiKey) || empty($sender)) {
        error_log('HTTP SMS configuration missing api key or sender.');
        return false;
    }

    $payload = json_encode([
        'api_key' => $apiKey,
        'sender' => $sender,
        'to' => $recipient,
        'message' => $message,
    ]);

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        error_log('HTTP SMS request failed: ' . $curlError);
        return false;
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        error_log("HTTP SMS returned status {$httpCode}: {$response}");
        return false;
    }

    $decoded = json_decode($response, true);
    if (is_array($decoded)) {
        if ((isset($decoded['success']) && $decoded['success']) || (isset($decoded['status']) && strtolower($decoded['status']) === 'sent')) {
            return true;
        }
    }

    return true;
}

function sendSms(string $recipient, string $message): bool
{
    $config = getMailerConfig();
    $toNumber = normalizePhoneNumber($recipient);

    if (!$toNumber) {
        error_log('HTTP SMS blocked for ' . $recipient . ': invalid phone number.');
        return false;
    }

    if (!empty($config['httpsms_api_key']) && !empty($config['httpsms_sender'])) {
        return sendHttpSms($toNumber, $message);
    }

    $fromNumber = $config['twilio_from_number'];
    if (empty($config['twilio_account_sid']) || empty($config['twilio_auth_token']) || empty($fromNumber)) {
        error_log('Twilio SMS blocked for ' . $recipient . ': invalid configuration or phone number.');
        return false;
    }

    try {
        $client = new TwilioClient($config['twilio_account_sid'], $config['twilio_auth_token']);
        $client->messages->create($toNumber, [
            'from' => $fromNumber,
            'body' => $message,
        ]);
        return true;
    } catch (Exception $exception) {
        error_log('Twilio SMS error: ' . $exception->getMessage());
        return false;
    }
}

function logNotificationEvent(array $data): bool
{
    if (empty($data['event_type'])) {
        return false;
    }

    require_once __DIR__ . '/db.php';
    $pdo = safeBrgy_db_connect();

    $stmt = $pdo->prepare('INSERT INTO sms_logs (user_id, email, mobile_number, event_type, event_meta, email_sent, sms_sent, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    return (bool) $stmt->execute([
        $data['user_id'] ?? null,
        $data['email'] ?? null,
        $data['mobile_number'] ?? null,
        $data['event_type'],
        isset($data['event_meta']) ? json_encode($data['event_meta'], JSON_UNESCAPED_UNICODE) : null,
        $data['email_sent'] ? 1 : 0,
        $data['sms_sent'] ? 1 : 0,
        $data['status'] ?? null,
    ]);
}

function sendAdminOtpEmail(string $recipient, string $otpCode): bool
{
    $subject = 'SafeBrgy Admin OTP Verification';
    $htmlBody = "<p>Hello,</p>"
        . "<p>Your SafeBrgy admin verification code is <strong>{$otpCode}</strong>.</p>"
        . "<p>This code is valid for 5 minutes.</p>"
        . "<p>If you did not request this code, please ignore this email.</p>"
        . "<p>Thank you,<br>SafeBrgy Team</p>";
    $plainBody = "Hello,\n\nYour SafeBrgy admin verification code is {$otpCode}.\n\nThis code is valid for 5 minutes.\n\nIf you did not request this code, please ignore this email.\n\nThank you,\nSafeBrgy Team";

    return sendMail($recipient, $subject, $htmlBody, $plainBody);
}

function sendRequestStatusEmail(string $recipient, string $residentName, string $requestNumber, string $documentType, string $newStatus): bool
{
    $subject = 'SafeBrgy Request Status Updated';
    $htmlBody = "<p>Hello {$residentName},</p>"
        . "<p>Your request <strong>{$requestNumber}</strong> for <strong>{$documentType}</strong> has been updated to <strong>{$newStatus}</strong>.</p>"
        . "<p>Please log in to your SafeBrgy account to view the latest update.</p>"
        . "<p>Thank you,<br>SafeBrgy Team</p>";
    $plainBody = "Hello {$residentName},\n\nYour request {$requestNumber} for {$documentType} has been updated to {$newStatus}.\n\nPlease log in to your SafeBrgy account to view the latest update.\n\nThank you,\nSafeBrgy Team";

    return sendMail($recipient, $subject, $htmlBody, $plainBody);
}

function sendReportStatusEmail(string $recipient, string $residentName, string $caseNumber, string $newStatus): bool
{
    $subject = 'SafeBrgy Report Status Updated';
    $htmlBody = "<p>Hello {$residentName},</p>"
        . "<p>The status of your report <strong>{$caseNumber}</strong> has been updated to <strong>{$newStatus}</strong>.</p>"
        . "<p>Please log in to your SafeBrgy account for more details.</p>"
        . "<p>Thank you,<br>SafeBrgy Team</p>";
    $plainBody = "Hello {$residentName},\n\nThe status of your report {$caseNumber} has been updated to {$newStatus}.\n\nPlease log in to your SafeBrgy account for more details.\n\nThank you,\nSafeBrgy Team";

    return sendMail($recipient, $subject, $htmlBody, $plainBody);
}

function sendAnnouncementEmail(string $recipient, string $residentName, string $title, string $description, string $priority, array $attachments = [], string $baseUrl = ''): bool
{
    $priorityLabel = ucfirst($priority);
    $attachmentHtml = '';

    if (!empty($attachments)) {
        $attachmentHtml .= '<div style="margin-top: 16px;"><strong>Pictures:</strong><br>';
        foreach ($attachments as $attachment) {
            $fileName = $attachment['file'] ?? '';
            if ($fileName) {
                $imageUrl = $baseUrl ? rtrim($baseUrl, '/') . '/uploads/announcements/' . rawurlencode($fileName) : '/uploads/announcements/' . rawurlencode($fileName);
                $attachmentHtml .= '<img src="' . htmlspecialchars($imageUrl, ENT_QUOTES) . '" alt="' . htmlspecialchars($title, ENT_QUOTES) . '" style="max-width: 100%; max-height: 240px; margin: 8px 0; border-radius: 8px;" />';
            }
        }
        $attachmentHtml .= '</div>';
    }

    $subject = 'SafeBrgy New Announcement: ' . $title;
    $htmlBody = "<p>Hello {$residentName},</p>"
        . "<p>A new announcement has been published on SafeBrgy.</p>"
        . "<p><strong>Title:</strong> {$title}<br>"
        . "<strong>Priority:</strong> {$priorityLabel}</p>"
        . "<p><strong>Message:</strong><br>{$description}</p>"
        . $attachmentHtml
        . "<p>Thank you,<br>SafeBrgy Team</p>";
    $plainBody = "Hello {$residentName},\n\nA new announcement has been published on SafeBrgy.\n\nTitle: {$title}\nPriority: {$priorityLabel}\n\nMessage:\n{$description}\n\nThank you,\nSafeBrgy Team";

    return sendMail($recipient, $subject, $htmlBody, $plainBody);
}

function sendRequestSubmissionNotification(string $recipientEmail, string $recipientName, ?string $mobileNumber, string $documentType, ?int $userId = null): array
{
    $subject = 'SafeBrgy Request Submitted';
    $htmlBody = "<p>Hello {$recipientName},</p>"
        . "<p>Your <strong>{$documentType}</strong> has been submitted. We will send a message for more updates.</p>"
        . "<p>Thank you,<br>SafeBrgy Team</p>";
    $plainBody = "Hello {$recipientName},\n\nYour {$documentType} has been submitted. We will send a message for more updates.\n\nThank you,\nSafeBrgy Team";

    $emailSent = sendMail($recipientEmail, $subject, $htmlBody, $plainBody);
    $smsSent = false;

    if ($mobileNumber) {
        $smsSent = sendSms($mobileNumber, "Your {$documentType} has been submitted. We will send a message for more updates.");
    }

    logNotificationEvent([
        'event_type' => 'request_submission',
        'user_id' => $userId,
        'email' => $recipientEmail,
        'mobile_number' => $mobileNumber,
        'event_meta' => ['document_type' => $documentType],
        'email_sent' => $emailSent,
        'sms_sent' => $smsSent,
        'status' => $emailSent && ($mobileNumber ? $smsSent : true) ? 'sent' : 'partial',
    ]);

    return ['email_sent' => $emailSent, 'sms_sent' => $smsSent];
}

function sendReportSubmissionNotification(string $recipientEmail, string $recipientName, ?string $mobileNumber, string $caseNumber, ?int $userId = null): array
{
    $subject = 'SafeBrgy Report Submitted';
    $htmlBody = "<p>Hello {$recipientName},</p>"
        . "<p>Your report <strong>{$caseNumber}</strong> has been submitted. We will send a message for more updates.</p>"
        . "<p>Thank you,<br>SafeBrgy Team</p>";
    $plainBody = "Hello {$recipientName},\n\nYour report {$caseNumber} has been submitted. We will send a message for more updates.\n\nThank you,\nSafeBrgy Team";

    $emailSent = sendMail($recipientEmail, $subject, $htmlBody, $plainBody);
    $smsSent = false;

    if ($mobileNumber) {
        $smsSent = sendSms($mobileNumber, "Your report {$caseNumber} has been submitted. We will send a message for more updates.");
    }

    logNotificationEvent([
        'event_type' => 'report_submission',
        'user_id' => $userId,
        'email' => $recipientEmail,
        'mobile_number' => $mobileNumber,
        'event_meta' => ['case_number' => $caseNumber],
        'email_sent' => $emailSent,
        'sms_sent' => $smsSent,
        'status' => $emailSent && ($mobileNumber ? $smsSent : true) ? 'sent' : 'partial',
    ]);

    return ['email_sent' => $emailSent, 'sms_sent' => $smsSent];
}

function sendRequestStatusNotification(string $recipientEmail, string $residentName, ?string $mobileNumber, string $requestNumber, string $documentType, string $newStatus, ?int $userId = null): array
{
    $emailSent = sendRequestStatusEmail($recipientEmail, $residentName, $requestNumber, $documentType, $newStatus);
    $smsSent = false;

    if ($mobileNumber) {
        $smsSent = sendSms($mobileNumber, "Your {$documentType} request ({$requestNumber}) status has been updated to {$newStatus}.");
    }

    logNotificationEvent([
        'event_type' => 'request_update',
        'user_id' => $userId,
        'email' => $recipientEmail,
        'mobile_number' => $mobileNumber,
        'event_meta' => ['request_number' => $requestNumber, 'document_type' => $documentType, 'new_status' => $newStatus],
        'email_sent' => $emailSent,
        'sms_sent' => $smsSent,
        'status' => $emailSent && ($mobileNumber ? $smsSent : true) ? 'sent' : 'partial',
    ]);

    return ['email_sent' => $emailSent, 'sms_sent' => $smsSent];
}

function sendReportStatusNotification(string $recipientEmail, string $residentName, ?string $mobileNumber, string $caseNumber, string $newStatus, ?int $userId = null): array
{
    $emailSent = sendReportStatusEmail($recipientEmail, $residentName, $caseNumber, $newStatus);
    $smsSent = false;

    if ($mobileNumber) {
        $smsSent = sendSms($mobileNumber, "Your report {$caseNumber} status has been updated to {$newStatus}.");
    }

    logNotificationEvent([
        'event_type' => 'report_update',
        'user_id' => $userId,
        'email' => $recipientEmail,
        'mobile_number' => $mobileNumber,
        'event_meta' => ['case_number' => $caseNumber, 'new_status' => $newStatus],
        'email_sent' => $emailSent,
        'sms_sent' => $smsSent,
        'status' => $emailSent && ($mobileNumber ? $smsSent : true) ? 'sent' : 'partial',
    ]);

    return ['email_sent' => $emailSent, 'sms_sent' => $smsSent];
}

function sendAnnouncementNotification(string $recipientEmail, string $residentName, ?string $mobileNumber, string $title, string $description, string $priority, array $attachments = [], string $baseUrl = '', ?int $userId = null): array
{
    $emailSent = sendAnnouncementEmail($recipientEmail, $residentName, $title, $description, $priority, $attachments, $baseUrl);
    $smsSent = false;

    if ($mobileNumber) {
        $smsSent = sendSms($mobileNumber, "New announcement: {$title}. Check your email for details.");
    }

    logNotificationEvent([
        'event_type' => 'announcement',
        'user_id' => $userId,
        'email' => $recipientEmail,
        'mobile_number' => $mobileNumber,
        'event_meta' => ['title' => $title, 'priority' => $priority],
        'email_sent' => $emailSent,
        'sms_sent' => $smsSent,
        'status' => $emailSent && ($mobileNumber ? $smsSent : true) ? 'sent' : 'partial',
    ]);

    return ['email_sent' => $emailSent, 'sms_sent' => $smsSent];
}
