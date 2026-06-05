<?php
require_once __DIR__ . '/../../config/db.php';
session_start();

// Check if user is logged in and is a resident
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'resident') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user']['id'];
$pdo = safeBrgy_db_connect();

// Validate input
$request_type = $_POST['request_type'] ?? null;
$purpose = $_POST['purpose'] ?? null;

if (!$request_type || !$purpose) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Validate request type
$allowed_types = ['Barangay Clearance', 'Barangay Residency', 'Barangay Indigency', 'Barangay Business Clearance'];
if (!in_array($request_type, $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request type']);
    exit;
}

// Prepare document data based on request type
$document_data = [];

if ($request_type === 'Barangay Clearance') {
    $document_data = [
        'purpose' => $purpose
    ];
} elseif ($request_type === 'Barangay Residency') {
    $years_residency = $_POST['years_residency'] ?? null;
    $date_started_living = $_POST['date_started_living'] ?? null;
    
    if (!$years_residency || !$date_started_living) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields for Barangay Residency']);
        exit;
    }
    
    $document_data = [
        'years_residency' => $years_residency,
        'date_started_living' => $date_started_living,
        'purpose' => $purpose
    ];
} elseif ($request_type === 'Barangay Indigency') {
    $monthly_income = $_POST['monthly_income'] ?? null;
    $household_members = $_POST['household_members'] ?? null;
    $indigency_purpose = $_POST['indigency_purpose'] ?? null;
    
    if (!$monthly_income || !$household_members || !$indigency_purpose) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields for Barangay Indigency']);
        exit;
    }
    
    $document_data = [
        'monthly_income' => $monthly_income,
        'household_members' => $household_members,
        'indigency_purpose' => $indigency_purpose,
        'purpose' => $purpose
    ];
    
    // If indigency_purpose is "Others", add the text input
    if ($indigency_purpose === 'Others') {
        $others_reason = $_POST['others_reason'] ?? null;
        if ($others_reason) {
            $document_data['others_reason'] = $others_reason;
        }
    }
} elseif ($request_type === 'Barangay Business Clearance') {
    $business_name = $_POST['business_name'] ?? null;
    $business_address = $_POST['business_address'] ?? null;
    $contact_number = $_POST['contact_number'] ?? null;
    $business_started = $_POST['business_started'] ?? null;
    
    if (!$business_name || !$business_address || !$contact_number || !$business_started) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields for Business Clearance']);
        exit;
    }
    
    $document_data = [
        'business_name' => $business_name,
        'business_description' => $_POST['business_description'] ?? null,
        'business_address' => $business_address,
        'contact_number' => $contact_number,
        'tin' => $_POST['tin'] ?? null,
        'business_started' => $business_started,
        'purpose' => $purpose
    ];
}

// Handle file uploads for Barangay Indigency
$attachments = null;
if ($request_type === 'Barangay Indigency' && isset($_FILES['documents'])) {
    $uploads_dir = __DIR__ . '/../../uploads/requests/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploads_dir)) {
        mkdir($uploads_dir, 0755, true);
    }
    
    $uploaded_files = [];
    $files = $_FILES['documents'];
    
    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }
        
        $file_tmp = $files['tmp_name'][$i];
        $file_name = $files['name'][$i];
        $file_size = $files['size'][$i];
        
        // Validate file size (5MB limit)
        if ($file_size > 5 * 1024 * 1024) {
            continue;
        }
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        $file_type = mime_content_type($file_tmp);
        
        if (!in_array($file_type, $allowed_types)) {
            continue;
        }
        
        // Generate unique filename
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $unique_name = uniqid('doc_') . '_' . time() . '.' . $ext;
        $file_path = $uploads_dir . $unique_name;
        
        if (move_uploaded_file($file_tmp, $file_path)) {
            $uploaded_files[] = 'uploads/requests/' . $unique_name;
        }
    }
    
    if (!empty($uploaded_files)) {
        $attachments = json_encode($uploaded_files);
    }
}

// Handle image uploads for Business Clearance logo
if ($request_type === 'Barangay Business Clearance' && isset($_FILES['business_logo'])) {
    $uploads_dir = __DIR__ . '/../../uploads/requests/';
    
    if (!is_dir($uploads_dir)) {
        mkdir($uploads_dir, 0755, true);
    }
    
    $file_tmp = $_FILES['business_logo']['tmp_name'];
    $file_name = $_FILES['business_logo']['name'];
    $file_size = $_FILES['business_logo']['size'];
    
    // Validate file size (5MB limit)
    if ($file_size <= 5 * 1024 * 1024) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $file_type = mime_content_type($file_tmp);
        
        if (in_array($file_type, $allowed_types)) {
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);
            $unique_name = uniqid('logo_') . '_' . time() . '.' . $ext;
            $file_path = $uploads_dir . $unique_name;
            
            if (move_uploaded_file($file_tmp, $file_path)) {
                $document_data['business_logo'] = 'uploads/requests/' . $unique_name;
            }
        }
    }
}

// Generate request number
$request_number = 'REQ-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

try {
    $stmt = $pdo->prepare('
        INSERT INTO requests (user_id, request_number, request_type, purpose, document_data, attachments, status)
        VALUES (?, ?, ?, ?, ?, ?, "Pending")
    ');

    $stmt->execute([
        $userId,
        $request_number,
        $request_type,
        $purpose,
        json_encode($document_data),
        $attachments
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Request created successfully',
        'request_number' => $request_number,
        'created_at' => date('M d, Y H:i A')
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
