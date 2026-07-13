<?php
/**
 * Shared helper functions used across the resident request system.
 */

/**
 * Generate a unique, human-readable reference number.
 * Format: BRGY-YYYYMMDD-XXXXX  (XXXXX = random 5-digit number)
 */
function generateReferenceNo(mysqli $conn): string
{
    do {
        $refNo = 'BRGY-' . date('Ymd') . '-' . str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
        $stmt = $conn->prepare('SELECT id FROM requests WHERE reference_no = ?');
        $stmt->bind_param('s', $refNo);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
    } while ($exists);

    return $refNo;
}

/**
 * Handle the optional "supporting document / image" upload.
 * Returns the relative file path to store in the DB, or null if no file was sent.
 */
function handleFileUpload(string $inputName, string $uploadDir = __DIR__ . '/../uploads/'): ?string
{
    if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('File upload failed. Please try again.');
    }

    $allowedExt = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
    $maxSize    = 5 * 1024 * 1024; // 5MB

    $originalName = basename($_FILES[$inputName]['name']);
    $ext          = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExt, true)) {
        throw new RuntimeException('Invalid file type. Allowed: jpg, jpeg, png, pdf, doc, docx.');
    }

    if ($_FILES[$inputName]['size'] > $maxSize) {
        throw new RuntimeException('File is too large. Maximum size is 5MB.');
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $newFileName = uniqid('doc_', true) . '.' . $ext;
    $destination = $uploadDir . $newFileName;

    if (!move_uploaded_file($_FILES[$inputName]['tmp_name'], $destination)) {
        throw new RuntimeException('Could not save the uploaded file.');
    }

    // Path stored relative to the project root (used for links in the UI)
    return 'uploads/' . $newFileName;
}

/**
 * Send a notification email to the resident.
 * Uses PHP's built-in mail(). On most local dev setups (XAMPP/WAMP) this
 * requires an SMTP relay (e.g. Mercury Mail, Papercut, or a tool like
 * "Sendmail for Windows") to actually deliver. For production, swapping
 * this out for PHPMailer + real SMTP credentials is recommended.
 */
function sendNotificationEmail(string $toEmail, string $toName, string $documentType): bool
{
    $fromName = defined('MAIL_FROM_NAME') && MAIL_FROM_NAME !== '' ? MAIL_FROM_NAME : 'Barangay Resident Portal';
    $fromEmail = defined('MAIL_FROM') && MAIL_FROM !== '' ? MAIL_FROM : 'no-reply@barangay-portal.test';

    $subject = "Your {$documentType} Request";
    $message = "Hi {$toName},\n\n"
             . "Your {$documentType} has been requested and pending to review.\n\n"
             . "We will send you an email once our barangay officials have updated the status of your request.\n\n"
             . "Thank you,\n"
             . $fromName;

    $headers  = 'From: ' . $fromName . ' <' . $fromEmail . '>' . "\r\n";
    $headers .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";

    // mail() returns false on failure but does not throw - we don't want
    // a broken local mail server to block the actual request submission.
    return @mail($toEmail, $subject, $message, $headers);
}

/**
 * Fetch all submitted requests for the request table section,
 * ordered by most recent first.
 */
function getAllRequests(mysqli $conn): array
{
    $sql = 'SELECT reference_no, document_type, status, submitted_at
            FROM requests
            ORDER BY submitted_at DESC';
    $result = $conn->query($sql);

    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

/**
 * Small helper to keep output safe from XSS.
 */
function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
