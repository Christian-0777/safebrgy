<?php
header('Location: /safebrgy/public/register.php');
exit;

/* Legacy handler retained below only for compatibility with old bookmarks. */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/mailer.php';
session_start();

// Function to generate OTP
function generateOTP() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Function to send OTP email
function sendOTPEmail($email, $otp) {
    $subject = 'SafeBrgy - Verify Your Account';
    $htmlBody = "
    <html>
    <body>
        <h2>Welcome to SafeBrgy!</h2>
        <p>Your OTP code is: <strong>$otp</strong></p>
        <p>This code will expire in 10 minutes.</p>
        <p>If you didn't request this, please ignore this email.</p>
    </body>
    </html>
    ";
    return sendMail($email, $subject, $htmlBody);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $required_fields = [
        'first_name', 'last_name', 'birthdate', 'age', 'place_of_birth', 'gender',
        'civil_status', 'nationality', 'religion', 'address', 'purok', 'years_residency',
        'mobile', 'email', 'voter_status', 'employment_status', 'occupation',
        'household_head', 'emergency_contact', 'family_members', 'educational_attainment',
        'blood_type', 'password', 'confirm_password'
    ];

    $errors = [];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }

    if (!isset($_POST['terms'])) {
        $errors[] = 'You must agree to the terms and conditions';
    }

    if ($_POST['password'] !== $_POST['confirm_password']) {
        $errors[] = 'Passwords do not match';
    }

    // Normalize and validate mobile number
    $mobileInput = trim($_POST['mobile'] ?? '');
    $mobileNormalized = null;
    if ($mobileInput !== '') {
        $mobileOnly = preg_replace('/[^0-9\+]/', '', $mobileInput);
        if (str_starts_with($mobileOnly, '0')) {
            $mobileOnly = '+63' . substr($mobileOnly, 1);
        }
        if (str_starts_with($mobileOnly, '63')) {
            $mobileOnly = '+' . $mobileOnly;
        }
        if (preg_match('/^\+63[0-9]{10}$/', $mobileOnly)) {
            $mobileNormalized = $mobileOnly;
        }
    }

    if (!$mobileNormalized) {
        $errors[] = 'Please enter a valid Philippine mobile number in +63 format';
    }

    // Check if email already exists
    $pdo = safeBrgy_db_connect();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$_POST['email']]);
    if ($stmt->fetch()) {
        $errors[] = 'Email already registered';
    }

    // Handle file uploads
    $valid_id_path = '';
    $profile_image_path = '';

    if (isset($_FILES['valid_id']) && $_FILES['valid_id']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/id/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $filename = uniqid() . '_' . basename($_FILES['valid_id']['name']);
        $valid_id_path = 'uploads/id/' . $filename;
        move_uploaded_file($_FILES['valid_id']['tmp_name'], __DIR__ . '/' . $valid_id_path);
    }

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/profile_images/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $filename = uniqid() . '_' . basename($_FILES['profile_image']['name']);
        $profile_image_path = 'uploads/profile_images/' . $filename;
        move_uploaded_file($_FILES['profile_image']['tmp_name'], __DIR__ . '/' . $profile_image_path);
    }

    if (empty($errors)) {
        // Generate OTP
        $otp = generateOTP();
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Generate Resident ID
        $resident_id = generateResidentId();

        // Store registration data in session
        $_SESSION['pending_registration'] = [
            'resident_id' => $resident_id,
            'first_name' => $_POST['first_name'],
            'middle_name' => $_POST['middle_name'] ?? '',
            'last_name' => $_POST['last_name'],
            'birthdate' => $_POST['birthdate'],
            'age' => (int)$_POST['age'],
            'place_of_birth' => $_POST['place_of_birth'],
            'gender' => $_POST['gender'],
            'civil_status' => $_POST['civil_status'],
            'nationality' => $_POST['nationality'],
            'religion' => $_POST['religion'],
            'complete_address' => $_POST['address'],
            'purok' => $_POST['purok'],
            'years_of_residency' => (int)$_POST['years_residency'],
            'mobile_number' => $mobileNormalized,
            'email' => $_POST['email'],
            'voter_status' => $_POST['voter_status'],
            'employment_status' => $_POST['employment_status'],
            'occupation' => $_POST['occupation'],
            'household_head' => $_POST['household_head'],
            'emergency_contact_name' => $_POST['emergency_contact'],
            'number_of_family_member' => (int)$_POST['family_members'],
            'educational_attainment' => $_POST['educational_attainment'],
            'blood_type' => $_POST['blood_type'],
            'disabilities' => $_POST['disabilities'] ?? '',
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'valid_id_path' => $valid_id_path,
            'profile_image_path' => $profile_image_path,
            'otp' => $otp,
            'otp_expiry' => $otp_expiry
        ];

        // Send OTP email
        if (sendOTPEmail($_POST['email'], $otp)) {
            // Redirect to OTP verification page
            header('Location: public/otp-view.php?type=registration');
            exit;
        } else {
            $errors[] = 'Failed to send OTP email';
        }
    }

    if (!empty($errors)) {
        $_SESSION['registration_errors'] = $errors;
        header('Location: index.php#register');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?>