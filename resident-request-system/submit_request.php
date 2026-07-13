<?php
/**
 * Handles submission of all 4 request forms:
 * Barangay Clearance, Residency, Indigency, Business Clearance.
 *
 * Flow:
 * 1. Validate the posted document_type + required fields.
 * 2. Insert the common info into `requests` (master table).
 * 3. Insert the document-specific fields into its own table.
 * 4. Send the resident a "pending review" notification email.
 * 5. Return JSON so the page can show the confirmation modal
 *    without a full page reload.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/includes/functions.php';

function respond(bool $success, string $message = '', array $extra = []): void
{
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    respond(false, 'Database connection failed.');
}
$conn->set_charset('utf8mb4');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.');
}

$documentType  = trim($_POST['document_type'] ?? '');
$residentName  = trim($_POST['resident_name'] ?? '');
$residentEmail = trim($_POST['resident_email'] ?? '');

$validTypes = [
    'Barangay Clearance',
    'Barangay Residency',
    'Barangay Indigency',
    'Barangay Business Clearance',
];

if (!in_array($documentType, $validTypes, true)) {
    respond(false, 'Unknown document type.');
}

if ($residentName === '' || $residentEmail === '') {
    respond(false, 'Full name and email are required.');
}

if (!filter_var($residentEmail, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Please provide a valid email address.');
}

try {
    // ---- Shared supporting document / image upload ----
    $supportingFile = handleFileUpload('supporting_file');

    $conn->begin_transaction();

    $referenceNo = generateReferenceNo($conn);

    // ---- 1. Insert into master `requests` table ----
    $stmt = $conn->prepare(
        'INSERT INTO requests (reference_no, document_type, resident_name, resident_email, supporting_file, status)
         VALUES (?, ?, ?, ?, ?, "Pending")'
    );
    $stmt->bind_param('sssss', $referenceNo, $documentType, $residentName, $residentEmail, $supportingFile);
    $stmt->execute();
    $requestId = $conn->insert_id;
    $stmt->close();

    // ---- 2. Insert into the document-specific table ----
    switch ($documentType) {

        case 'Barangay Clearance':
            $purpose = trim($_POST['purpose'] ?? '');
            if ($purpose === '') {
                throw new RuntimeException('Purpose of request is required.');
            }
            $stmt = $conn->prepare('INSERT INTO barangay_clearance (request_id, purpose) VALUES (?, ?)');
            $stmt->bind_param('is', $requestId, $purpose);
            $stmt->execute();
            $stmt->close();
            break;

        case 'Barangay Residency':
            $years       = (int) ($_POST['years_of_residency'] ?? 0);
            $dateStarted = trim($_POST['date_started'] ?? '');
            $purpose     = trim($_POST['purpose'] ?? '');

            if ($dateStarted === '' || $purpose === '') {
                throw new RuntimeException('All required residency fields must be filled out.');
            }

            $stmt = $conn->prepare(
                'INSERT INTO barangay_residency (request_id, years_of_residency, date_started, purpose)
                 VALUES (?, ?, ?, ?)'
            );
            $stmt->bind_param('iiss', $requestId, $years, $dateStarted, $purpose);
            $stmt->execute();
            $stmt->close();
            break;

        case 'Barangay Indigency':
            $monthlyIncome    = (float) ($_POST['monthly_income'] ?? 0);
            $householdMembers = (int) ($_POST['household_members'] ?? 0);
            $purpose          = trim($_POST['purpose'] ?? '');
            $purposeOther     = trim($_POST['purpose_other'] ?? '');

            $allowedPurposes = ['Medical Assistance', 'Educational Assistance', 'Financial Assistance', 'Burial Assistance', 'Other'];
            if (!in_array($purpose, $allowedPurposes, true)) {
                throw new RuntimeException('Please select a valid purpose of request.');
            }
            if ($purpose === 'Other' && $purposeOther === '') {
                // "Other" specification is optional per requirements (not required),
                // so we simply store an empty string / null if left blank.
                $purposeOther = null;
            }
            if ($purpose !== 'Other') {
                $purposeOther = null;
            }

            $stmt = $conn->prepare(
                'INSERT INTO barangay_indigency (request_id, monthly_income, household_members, purpose, purpose_other)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->bind_param('idiss', $requestId, $monthlyIncome, $householdMembers, $purpose, $purposeOther);
            $stmt->execute();
            $stmt->close();
            break;

        case 'Barangay Business Clearance':
            $businessName        = trim($_POST['business_name'] ?? '');
            $businessDescription = trim($_POST['business_description'] ?? '');
            $businessAddress     = trim($_POST['business_address'] ?? '');
            $contactNumber       = trim($_POST['contact_number'] ?? '');
            $tinNumber           = trim($_POST['tin_number'] ?? '');
            $businessStarted     = trim($_POST['business_started'] ?? '');
            $purpose             = trim($_POST['purpose'] ?? '');

            if ($businessName === '' || $businessDescription === '' || $businessAddress === ''
                || $contactNumber === '' || $businessStarted === '' || $purpose === '') {
                throw new RuntimeException('All required business clearance fields must be filled out.');
            }

            $tinNumber   = $tinNumber !== '' ? $tinNumber : null;
            $businessLogo = handleFileUpload('business_logo');

            $stmt = $conn->prepare(
                'INSERT INTO barangay_business_clearance
                    (request_id, business_name, business_description, business_logo, business_address,
                     contact_number, tin_number, business_started, purpose)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->bind_param(
                'issssssss',
                $requestId,
                $businessName,
                $businessDescription,
                $businessLogo,
                $businessAddress,
                $contactNumber,
                $tinNumber,
                $businessStarted,
                $purpose
            );
            $stmt->execute();
            $stmt->close();
            break;
    }

    $conn->commit();

    // ---- 3. Notify the resident by email ----
    sendNotificationEmail($residentEmail, $residentName, $documentType);

    // ---- 4. Respond with data needed to update the UI ----
    respond(true, 'Request submitted successfully.', [
        'reference_no' => $referenceNo,
        'submitted_at' => date('M d, Y g:i A'),
    ]);

} catch (Throwable $e) {
    $conn->rollback();
    respond(false, $e->getMessage());
}
