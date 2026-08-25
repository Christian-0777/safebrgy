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
session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mailer.php';
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

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'resident') {
    respond(false, 'Please sign in as a resident to submit a request.');
}

$documentType  = trim($_POST['document_type'] ?? '');
$loggedInUserId = (int) ($_SESSION['user']['id'] ?? 0);

$validTypes = [
    'Barangay Clearance',
    'Barangay Residency',
    'Barangay Indigency',
    'Barangay Business Clearance',
];

if (!in_array($documentType, $validTypes, true)) {
    respond(false, 'Unknown document type.');
}

$residentStmt = $conn->prepare(
    'SELECT u.id, u.email, CONCAT_WS(" ", r.first_name, r.middle_name, r.last_name) AS resident_name,
            r.years_of_residency
       FROM users u
       INNER JOIN residents r ON r.user_id = u.id
      WHERE u.id = ? AND u.role = "resident"'
);
$residentStmt->bind_param('i', $loggedInUserId);
$residentStmt->execute();
$resident = $residentStmt->get_result()->fetch_assoc();
$residentStmt->close();

if (!$resident || !filter_var($resident['email'], FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Resident information could not be found.');
}

$residentName = trim($resident['resident_name']) ?: 'Resident';
$residentEmail = $resident['email'];
$yearsOfResidency = max(0, (int) ($resident['years_of_residency'] ?? 0));

try {
    // ---- Shared supporting document / image upload ----
    $supportingFile = handleFileUpload('supporting_file');

    $conn->begin_transaction();

    $referenceNo = generateReferenceNo($conn);

    // ---- 1. Insert into master `requests` table ----
    $stmt = $conn->prepare(
        'INSERT INTO requests (user_id, reference_no, document_type, resident_name, resident_email, supporting_file, status)
         VALUES (?, ?, ?, ?, ?, ?, "Pending")'
    );
    $stmt->bind_param('isssss', $loggedInUserId, $referenceNo, $documentType, $residentName, $residentEmail, $supportingFile);
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
            $years       = $yearsOfResidency;
            $dateStarted = date('Y-m-d', strtotime('-' . $years . ' years'));
            $purpose     = trim($_POST['purpose'] ?? '');

            if ($purpose === '') {
                throw new RuntimeException('Purpose of request is required.');
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

    // ---- 3. Notify the resident by email and SMS if available ----
    $userStmt = $conn->prepare('SELECT u.id AS user_id, r.mobile_number FROM users u LEFT JOIN residents r ON u.id = r.user_id WHERE u.email = ?');
    $userStmt->bind_param('s', $residentEmail);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $userRow = $userResult ? $userResult->fetch_assoc() : null;
    $userStmt->close();

    $userId = $userRow['user_id'] ?? null;
    $mobileNumber = $userRow['mobile_number'] ?? null;
    sendRequestSubmissionNotification($residentEmail, $residentName, $mobileNumber, $documentType, $userId);

    // ---- 4. Respond with data needed to update the UI ----
    respond(true, 'Request submitted successfully.', [
        'reference_no' => $referenceNo,
        'submitted_at' => date('M d, Y g:i A'),
    ]);

} catch (Throwable $e) {
    $conn->rollback();
    respond(false, $e->getMessage());
}
