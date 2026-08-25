<?php
/**
 * Submit Report API Endpoint
 * POST /api/reports/submit - Submit new guest report
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../../config/mailer.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

try {
    $pdo = getDBConnection();
    
    // Get form data
    $reportType = sanitizeInput($_POST['report_type'] ?? '');
    $title = sanitizeInput($_POST['title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $location = sanitizeInput($_POST['location'] ?? '');
    $guestAka = sanitizeInput($_POST['guest_aka'] ?? '');
    $contactMethod = sanitizeInput($_POST['contact_method'] ?? '');
    $contactEmail = sanitizeInput($_POST['contact_email'] ?? '');
    $contactMobile = sanitizeInput($_POST['contact_mobile'] ?? '');
    
    // Validate required fields
    $errors = [];
    if (empty($reportType) || !in_array($reportType, ['Incident', 'Lost Property', 'Blotter'])) {
        $errors[] = 'Invalid report type';
    }
    if (empty($title)) {
        $errors[] = 'Title is required';
    }
    if (empty($description)) {
        $errors[] = 'Description is required';
    }
    if (empty($location)) {
        $errors[] = 'Location is required';
    }
    if (empty($guestAka)) {
        $errors[] = 'Your name/alias is required';
    }
    if (empty($contactMethod) || !in_array($contactMethod, ['email', 'mobile'])) {
        $errors[] = 'Contact method is required';
    }
    if ($contactMethod === 'email') {
        if (empty($contactEmail) || !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }
    } elseif ($contactMethod === 'mobile') {
        if (empty($contactMobile) || !preg_match('/^\d{10}$/', $contactMobile)) {
            $errors[] = 'Valid Philippine mobile number (10 digits) is required';
        }
    }
    
    if (!empty($errors)) {
        jsonResponse(['success' => false, 'message' => implode(', ', $errors)], 400);
    }
    
    // Handle file uploads
    $attachments = [];
    if (isset($_FILES['pictures']) && is_array($_FILES['pictures']['name'])) {
        $files = $_FILES['pictures'];
        $fileCount = count($files['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
                $result = handleFileUpload($file);
                if ($result['success']) {
                    $attachments[] = $result['path'];
                }
            }
        }
    }
    
    // Generate case number (ensure uniqueness)
    $caseNumber = generateCaseNumber();
    $attempts = 0;
    while ($attempts < 10) {
        $stmt = $pdo->prepare("SELECT id FROM guest_reports WHERE case_number = ?");
        $stmt->execute([$caseNumber]);
        if (!$stmt->fetch()) {
            break;
        }
        $caseNumber = generateCaseNumber();
        $attempts++;
    }
    
    // Calculate expiration date (90 days from now)
    $expiresAt = date('Y-m-d H:i:s', strtotime('+90 days'));
    
    // Insert into guest_reports table
    $stmt = $pdo->prepare("
        INSERT INTO guest_reports (
            case_number, report_type, title, description, location,
            attachments, guest_aka, contact_method, contact_email, contact_mobile,
            status, expires_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', ?)
    ");
    
    $attachmentsJson = !empty($attachments) ? json_encode($attachments) : null;
    
    $stmt->execute([
        $caseNumber,
        $reportType,
        $title,
        $description,
        $location,
        $attachmentsJson,
        $guestAka,
        $contactMethod,
        $contactMethod === 'email' ? $contactEmail : null,
        $contactMethod === 'mobile' ? $contactMobile : null,
        $expiresAt
    ]);

    $notificationSent = false;
    if ($contactMethod === 'email') {
        $safeName = htmlspecialchars($guestAka, ENT_QUOTES, 'UTF-8');
        $safeCaseNumber = htmlspecialchars($caseNumber, ENT_QUOTES, 'UTF-8');
        $notificationSent = sendMail(
            $contactEmail,
            'SafeBrgy Report Submitted',
            "<p>Hello {$safeName},</p><p>Your report reference number is <strong>{$safeCaseNumber}</strong>. Your report has been submitted and is pending review.</p><p>Thank you,<br>SafeBrgy Team</p>",
            "Hello {$guestAka},\n\nYour report reference number is {$caseNumber}. Your report has been submitted and is pending review.\n\nThank you,\nSafeBrgy Team"
        );
    } else {
        $notificationSent = sendSms(
            '+63' . $contactMobile,
            "Your SafeBrgy report reference number is {$caseNumber}. Your report has been submitted and is pending review."
        );
    }

    try {
        logNotificationEvent([
            'event_type' => 'guest_report_submission',
            'email' => $contactMethod === 'email' ? $contactEmail : null,
            'mobile_number' => $contactMethod === 'mobile' ? '+63' . $contactMobile : null,
            'event_meta' => ['case_number' => $caseNumber, 'channel' => $contactMethod],
            'email_sent' => $contactMethod === 'email' && $notificationSent,
            'sms_sent' => $contactMethod === 'mobile' && $notificationSent,
            'status' => $notificationSent ? 'sent' : 'failed',
        ]);
    } catch (Exception $notificationLogException) {
        error_log('Guest report notification log error: ' . $notificationLogException->getMessage());
    }
    
    jsonResponse([
        'success' => true,
        'message' => 'Report submitted successfully',
        'data' => [
            'case_number' => $caseNumber,
            'guest_aka' => $guestAka,
            'notification_channel' => $contactMethod,
            'notification_sent' => $notificationSent
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Submit Report API error: " . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Failed to submit report'], 500);
}