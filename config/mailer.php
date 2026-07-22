<?php
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
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
        'mail_from_name' => getenv('MAIL_FROM_NAME') ?: 'SafeBrgy',
        'sendgrid_api_key' => getenv('SENDGRID_API_KEY') ?: '',
        'sendgrid_from_email' => getenv('SENDGRID_FROM_EMAIL') ?: getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@safebrgy.local',
        'sendgrid_from_name' => getenv('SENDGRID_FROM_NAME') ?: getenv('MAIL_FROM_NAME') ?: 'SafeBrgy',
    ];
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
            $mail->Body = $htmlBody;
            $mail->AltBody = $plainBody;
            if ($mail->send()) {
                return true;
            } else {
                error_log('SMTP mail failed to send to ' . $recipient . ': ' . $mail->ErrorInfo);
            }
        } catch (PHPMailerException $exception) {
            error_log('SMTP mail error: ' . $exception->getMessage());
        }
    }

    // SendGrid is disabled for now
    /*
    if (!empty($config['sendgrid_api_key'])) {
        try {
            $email = new SendGridMail();
            $email->setFrom($config['sendgrid_from_email'], $config['sendgrid_from_name']);
            $email->setSubject($subject);
            $email->addTo($recipient);
            $email->addContent('text/plain', $plainBody);
            $email->addContent('text/html', $htmlBody);

            $sendgrid = new SendGrid($config['sendgrid_api_key']);
            $response = $sendgrid->send($email);
            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                return true;
            }
            error_log('SendGrid mail error: HTTP ' . $response->statusCode());
        } catch (Exception $exception) {
            error_log('SendGrid mail exception: ' . $exception->getMessage());
        }
    }
    */

    return false;
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
