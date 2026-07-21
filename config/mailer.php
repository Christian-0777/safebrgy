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

/**
 * Send a request status update notification to a resident.
 *
 * @param string $recipient      Resident email address
 * @param string $referenceNo    Request reference number (e.g. REQ-2025-00123)
 * @param string $documentType   Document type (Barangay Clearance, etc.)
 * @param string $status         New status (Pending, Approved, Rejected, Ready for Pickup, Processing, Received)
 * @param string|null $residentName  Optional resident name for personalization
 * @param string|null $notes     Optional admin notes / instructions
 * @return bool
 */
function sendRequestStatusEmail(string $recipient, string $referenceNo, string $documentType, string $status, ?string $residentName = null, ?string $notes = null): bool
{
    $greeting = $residentName ? "Dear {$residentName}," : 'Hello,';
    $subject = "SafeBrgy Request Update — {$referenceNo} ({$status})";

    $statusColor = match ($status) {
        'Approved', 'Ready for Pickup', 'Received' => '#16a34a',
        'Rejected', 'Dismissed' => '#dc2626',
        'Processing' => '#2563eb',
        default => '#f59e0b',
    };

    $notesHtml = $notes ? "<p><strong>Notes from the barangay office:</strong></p><p style=\"background:#f9fafb;padding:12px;border-radius:6px;border-left:4px solid {$statusColor};\">" . htmlspecialchars($notes) . "</p>" : '';

    $htmlBody = "<div style=\"font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:0 auto;color:#333;\">"
        . "<div style=\"background:#1e3a5f;padding:20px;border-radius:8px 8px 0 0;\"><h2 style=\"color:#fff;margin:0;\">SafeBrgy</h2><p style=\"color:#cbd5e1;margin:4px 0 0;\">Document Request Status Update</p></div>"
        . "<div style=\"padding:24px;border:1px solid #e5e7eb;border-top:none;border-radius:0 0 8px 8px;\">"
        . "<p>{$greeting}</p>"
        . "<p>Your document request has been updated. Here are the details:</p>"
        . "<table style=\"width:100%;border-collapse:collapse;margin:16px 0;\">"
        . "<tr><td style=\"padding:8px 12px;background:#f3f4f6;font-weight:bold;width:40%;\">Reference No.</td><td style=\"padding:8px 12px;\">{$referenceNo}</td></tr>"
        . "<tr><td style=\"padding:8px 12px;background:#f3f4f6;font-weight:bold;\">Document Type</td><td style=\"padding:8px 12px;\">" . htmlspecialchars($documentType) . "</td></tr>"
        . "<tr><td style=\"padding:8px 12px;background:#f3f4f6;font-weight:bold;\">Status</td><td style=\"padding:8px 12px;\"><span style=\"display:inline-block;padding:4px 12px;border-radius:9999px;background:{$statusColor};color:#fff;font-size:13px;font-weight:600;\">{$status}</span></td></tr>"
        . "</table>"
        . $notesHtml
        . "<p>If you have any questions, please contact your barangay office.</p>"
        . "<p>Thank you,<br>SafeBrgy Team</p>"
        . "</div></div>";

    $plainBody = "{$greeting}\n\n"
        . "Your document request has been updated.\n\n"
        . "Reference No.: {$referenceNo}\n"
        . "Document Type: {$documentType}\n"
        . "Status: {$status}\n"
        . ($notes ? "\nNotes from the barangay office:\n{$notes}\n" : '')
        . "\nIf you have any questions, please contact your barangay office.\n\nThank you,\nSafeBrgy Team";

    return sendMail($recipient, $subject, $htmlBody, $plainBody);
}

/**
 * Send a report status update notification to a resident.
 *
 * @param string $recipient   Resident email address
 * @param string $caseNumber  Report case number (e.g. RPT-001)
 * @param string $reportType  Report type (Incident, Lost Property, Blotter)
 * @param string $title       Report title
 * @param string $status      New status (Pending, Ongoing, Resolved, Dismissed)
 * @param string|null $residentName  Optional resident name for personalization
 * @param string|null $notes   Optional admin notes
 * @return bool
 */
function sendReportStatusEmail(string $recipient, string $caseNumber, string $reportType, string $title, string $status, ?string $residentName = null, ?string $notes = null): bool
{
    $greeting = $residentName ? "Dear {$residentName}," : 'Hello,';
    $subject = "SafeBrgy Report Update — {$caseNumber} ({$status})";

    $statusColor = match ($status) {
        'Resolved' => '#16a34a',
        'Dismissed' => '#dc2626',
        'Ongoing' => '#2563eb',
        default => '#f59e0b',
    };

    $notesHtml = $notes ? "<p><strong>Notes from the barangay office:</strong></p><p style=\"background:#f9fafb;padding:12px;border-radius:6px;border-left:4px solid {$statusColor};\">" . htmlspecialchars($notes) . "</p>" : '';

    $htmlBody = "<div style=\"font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:0 auto;color:#333;\">"
        . "<div style=\"background:#1e3a5f;padding:20px;border-radius:8px 8px 0 0;\"><h2 style=\"color:#fff;margin:0;\">SafeBrgy</h2><p style=\"color:#cbd5e1;margin:4px 0 0;\">Report Status Update</p></div>"
        . "<div style=\"padding:24px;border:1px solid #e5e7eb;border-top:none;border-radius:0 0 8px 8px;\">"
        . "<p>{$greeting}</p>"
        . "<p>Your submitted report has been updated. Here are the details:</p>"
        . "<table style=\"width:100%;border-collapse:collapse;margin:16px 0;\">"
        . "<tr><td style=\"padding:8px 12px;background:#f3f4f6;font-weight:bold;width:40%;\">Case Number</td><td style=\"padding:8px 12px;\">{$caseNumber}</td></tr>"
        . "<tr><td style=\"padding:8px 12px;background:#f3f4f6;font-weight:bold;\">Report Type</td><td style=\"padding:8px 12px;\">" . htmlspecialchars($reportType) . "</td></tr>"
        . "<tr><td style=\"padding:8px 12px;background:#f3f4f6;font-weight:bold;\">Title</td><td style=\"padding:8px 12px;\">" . htmlspecialchars($title) . "</td></tr>"
        . "<tr><td style=\"padding:8px 12px;background:#f3f4f6;font-weight:bold;\">Status</td><td style=\"padding:8px 12px;\"><span style=\"display:inline-block;padding:4px 12px;border-radius:9999px;background:{$statusColor};color:#fff;font-size:13px;font-weight:600;\">{$status}</span></td></tr>"
        . "</table>"
        . $notesHtml
        . "<p>If you have any questions, please contact your barangay office.</p>"
        . "<p>Thank you,<br>SafeBrgy Team</p>"
        . "</div></div>";

    $plainBody = "{$greeting}\n\n"
        . "Your submitted report has been updated.\n\n"
        . "Case Number: {$caseNumber}\n"
        . "Report Type: {$reportType}\n"
        . "Title: {$title}\n"
        . "Status: {$status}\n"
        . ($notes ? "\nNotes from the barangay office:\n{$notes}\n" : '')
        . "\nIf you have any questions, please contact your barangay office.\n\nThank you,\nSafeBrgy Team";

    return sendMail($recipient, $subject, $htmlBody, $plainBody);
}

/**
 * Send an announcement notification email to residents.
 *
 * @param string $recipient    Resident email address
 * @param string $title        Announcement title
 * @param string $body         Announcement body (HTML allowed)
 * @param string $priority     Priority level (normal, important, urgent)
 * @param array  $attachments  Optional array of attachment URLs [['name' => ..., 'url' => ...], ...]
 * @param string|null $residentName  Optional resident name for personalization
 * @return bool
 */
function sendAnnouncementEmail(string $recipient, string $title, string $body, string $priority = 'normal', array $attachments = [], ?string $residentName = null): bool
{
    $greeting = $residentName ? "Dear {$residentName}," : 'Dear Resident,';

    $priorityLabel = ucfirst($priority);
    $priorityColor = match ($priority) {
        'urgent' => '#dc2626',
        'important' => '#f59e0b',
        default => '#2563eb',
    };

    $subjectPrefix = $priority === 'urgent' ? '[URGENT] ' : ($priority === 'important' ? '[Important] ' : '');
    $subject = $subjectPrefix . "SafeBrgy Announcement: {$title}";

    // Build attachments list
    $attachmentsHtml = '';
    if (!empty($attachments)) {
        $attachmentsHtml = '<p><strong>Attachments:</strong></p><ul style="padding-left:20px;">';
        foreach ($attachments as $attachment) {
            $name = htmlspecialchars($attachment['name'] ?? basename($attachment['url'] ?? ''));
            $url = htmlspecialchars($attachment['url'] ?? '');
            if ($url) {
                $attachmentsHtml .= "<li><a href=\"{$url}\" style=\"color:#2563eb;\">{$name}</a></li>";
            }
        }
        $attachmentsHtml .= '</ul>';
    }

    $htmlBody = "<div style=\"font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:0 auto;color:#333;\">"
        . "<div style=\"background:#1e3a5f;padding:20px;border-radius:8px 8px 0 0;\"><h2 style=\"color:#fff;margin:0;\">SafeBrgy</h2><p style=\"color:#cbd5e1;margin:4px 0 0;\">Barangay Announcement</p></div>"
        . "<div style=\"padding:24px;border:1px solid #e5e7eb;border-top:none;border-radius:0 0 8px 8px;\">"
        . "<p>{$greeting}</p>"
        . "<p>A new announcement has been posted by your barangay office:</p>"
        . "<div style=\"margin:16px 0;padding:16px;border-radius:8px;border:1px solid #e5e7eb;\">"
        . "<div style=\"display:flex;align-items:center;gap:8px;margin-bottom:12px;\">"
        . "<span style=\"display:inline-block;padding:4px 12px;border-radius:9999px;background:{$priorityColor};color:#fff;font-size:12px;font-weight:600;\">{$priorityLabel}</span>"
        . "<h3 style=\"margin:0;color:#1e3a5f;\">" . htmlspecialchars($title) . "</h3>"
        . "</div>"
        . "<div style=\"line-height:1.6;\">{$body}</div>"
        . "</div>"
        . $attachmentsHtml
        . "<p style=\"margin-top:24px;color:#6b7280;font-size:13px;\">This is an automated message from the SafeBrgy system. Please do not reply to this email.</p>"
        . "<p>Thank you,<br>SafeBrgy Team</p>"
        . "</div></div>";

    $plainBody = "{$greeting}\n\n"
        . "A new announcement has been posted by your barangay office:\n\n"
        . "Priority: {$priorityLabel}\n"
        . "Title: {$title}\n\n"
        . strip_tags($body) . "\n"
        . (!empty($attachments) ? "\nAttachments:\n" . implode("\n", array_map(fn($a) => ($a['name'] ?? basename($a['url'] ?? '')) . ': ' . ($a['url'] ?? ''), $attachments)) . "\n" : '')
        . "\nThis is an automated message from the SafeBrgy system. Please do not reply to this email.\n\nThank you,\nSafeBrgy Team";

    return sendMail($recipient, $subject, $htmlBody, $plainBody);
}
