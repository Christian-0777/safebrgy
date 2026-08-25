<?php
/**
 * api/register.php
 * Final submission handler. Re-validates every field server-side
 * (never trust the client), checks and consumes the OTP, stores the
 * uploaded photos, hashes the password, and inserts the resident
 * record with status = 'pending'.
 */

declare(strict_types=1);

require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../config/mailer.php';

require_post();

if (!verify_csrf((string) ($_POST['csrf_token'] ?? ''))) {
    json_response(false, 'Your session has expired. Please refresh the page and try again.', [], 419);
}

$errors = [];

/* ---------------------------------------------------------------
 * Step 1 — Basic personal information
 * ------------------------------------------------------------- */
$firstName  = clean_str($_POST['first_name'] ?? '');
$middleName = clean_str($_POST['middle_name'] ?? '');
$lastName   = clean_str($_POST['last_name'] ?? '');
$birthdate  = clean_str($_POST['birthdate'] ?? '');
$placeOfBirth = clean_str($_POST['place_of_birth'] ?? '');
$gender       = clean_str($_POST['gender'] ?? '');
$civilStatus  = clean_str($_POST['civil_status'] ?? '');
$nationality  = clean_str($_POST['nationality'] ?? '');
$religion     = clean_str($_POST['religion'] ?? '');

if ($firstName === '') $errors['first_name'] = 'First name is required.';
if ($lastName === '')  $errors['last_name'] = 'Last name is required.';

$age = $birthdate !== '' ? calculate_age_from_birthdate($birthdate) : null;
if ($age === null) {
    $errors['birthdate'] = 'Enter a valid birthdate.';
} elseif ($age < 0 || $age > 130) {
    $errors['birthdate'] = 'That birthdate does not look right.';
}

if ($placeOfBirth === '') $errors['place_of_birth'] = 'Place of birth is required.';
if (!in_array($gender, GENDER_OPTIONS, true)) $errors['gender'] = 'Select a gender.';
if (!in_array($civilStatus, CIVIL_STATUS_OPTIONS, true)) $errors['civil_status'] = 'Select a civil status.';
if (!in_array($nationality, NATIONALITY_OPTIONS, true)) $errors['nationality'] = 'Select a nationality.';
if (!in_array($religion, RELIGION_OPTIONS, true)) $errors['religion'] = 'Select a religion.';

/* ---------------------------------------------------------------
 * Step 2 — Contact and location
 * ------------------------------------------------------------- */
$purok = clean_str($_POST['purok'] ?? '');
$completeAddress = clean_str($_POST['complete_address'] ?? '');
$yearsOfResidency = filter_var($_POST['years_of_residency'] ?? '', FILTER_VALIDATE_INT);
$mobileNumber = normalize_ph_mobile((string) ($_POST['mobile_number'] ?? ''));
$email = clean_str($_POST['email'] ?? '');

if (!in_array($purok, PUROK_OPTIONS, true)) $errors['purok'] = 'Select a street/purok.';
if ($completeAddress === '') $errors['complete_address'] = 'Complete address is required.';
if ($yearsOfResidency === false || $yearsOfResidency === null || $yearsOfResidency < 0) {
    $errors['years_of_residency'] = 'Enter a valid number of years.';
}
if ($mobileNumber === null) $errors['mobile_number'] = 'Enter a valid +63 mobile number.';
if (!is_valid_email($email)) $errors['email'] = 'Enter a valid email address.';

/* ---------------------------------------------------------------
 * Step 3 — Economic profile
 * ------------------------------------------------------------- */
$voterStatus = clean_str($_POST['voter_status'] ?? '');
$education = clean_str($_POST['educational_attainment'] ?? '');
$employmentStatus = clean_str($_POST['employment_status'] ?? '');
$isOccupationNA = isset($_POST['occupation_na']) && $_POST['occupation_na'] === '1';
$occupation = $isOccupationNA ? null : clean_str($_POST['occupation'] ?? '');
$householdHead = clean_str($_POST['household_head'] ?? '');
$familyMembers = filter_var($_POST['number_of_family_members'] ?? '', FILTER_VALIDATE_INT);

if (!in_array($voterStatus, VOTER_STATUS_OPTIONS, true)) $errors['voter_status'] = 'Select a voter status.';
if (!in_array($education, EDUCATION_OPTIONS, true)) $errors['educational_attainment'] = 'Select educational attainment.';
if (!in_array($employmentStatus, EMPLOYMENT_OPTIONS, true)) $errors['employment_status'] = 'Select an employment status.';
if (!$isOccupationNA && $occupation === '') $errors['occupation'] = 'Enter an occupation, or check N/A.';
if ($householdHead === '') $errors['household_head'] = 'Household head is required.';
if ($familyMembers === false || $familyMembers === null || $familyMembers < 1) {
    $errors['number_of_family_members'] = 'Enter a valid number of family members.';
}

/* ---------------------------------------------------------------
 * Step 4 — Other information
 * ------------------------------------------------------------- */
$emergencyContactName = clean_str($_POST['emergency_contact_name'] ?? '');
$emergencyContactNumber = normalize_ph_mobile((string) ($_POST['emergency_contact_number'] ?? ''));
$bloodType = clean_str($_POST['blood_type'] ?? '');
$disability = clean_str($_POST['disability'] ?? '');

if ($emergencyContactName === '') $errors['emergency_contact_name'] = 'Emergency contact name is required.';
if ($emergencyContactNumber === null) $errors['emergency_contact_number'] = 'Enter a valid +63 mobile number.';
if (!in_array($bloodType, BLOOD_TYPE_OPTIONS, true)) $errors['blood_type'] = 'Select a blood type.';
if (!in_array($disability, DISABILITY_OPTIONS, true)) $errors['disability'] = 'Select a disability status.';

/* ---------------------------------------------------------------
 * Step 6 — Password
 * ------------------------------------------------------------- */
$password = (string) ($_POST['password'] ?? '');
$confirmPassword = (string) ($_POST['confirm_password'] ?? '');
$termsAccepted = isset($_POST['terms']) && $_POST['terms'] === '1';

if (strlen($password) < 8) $errors['password'] = 'Password must be at least 8 characters.';
if ($password !== $confirmPassword) $errors['confirm_password'] = 'Passwords do not match.';
if (!$termsAccepted) $errors['terms'] = 'You must accept the Terms & Conditions.';

/* ---------------------------------------------------------------
 * Step 7 — OTP
 * ------------------------------------------------------------- */
$otp = clean_str($_POST['otp'] ?? '');
if (!preg_match('/^\d{6}$/', $otp)) {
    $errors['otp'] = 'Enter the 6-digit code sent to your email.';
}

/* ---------------------------------------------------------------
 * Uploaded photos — presence check (content is validated when stored)
 * ------------------------------------------------------------- */
foreach (['valid_id_front', 'valid_id_back', 'profile_photo'] as $field) {
    if (!isset($_FILES[$field])) {
        $errors[$field] = 'This photo is required.';
        continue;
    }

    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        $uploadErrorMessages = [
            UPLOAD_ERR_INI_SIZE => 'This photo exceeds the server upload limit.',
            UPLOAD_ERR_FORM_SIZE => 'This photo exceeds the form upload limit.',
            UPLOAD_ERR_PARTIAL => 'This photo upload was incomplete. Please try again.',
            UPLOAD_ERR_NO_FILE => 'This photo is required.',
        ];
        $errors[$field] = $uploadErrorMessages[$_FILES[$field]['error']]
            ?? 'This photo could not be uploaded. Please try again.';
    }
}

if (!empty($errors)) {
    json_response(false, 'Please correct the highlighted fields.', ['errors' => $errors], 422);
}

$pdo = safeBrgy_db_connect();

/* ---------------------------------------------------------------
 * Re-check email uniqueness and OTP validity right before writing.
 * ------------------------------------------------------------- */
try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        json_response(false, 'An account with this email already exists.', ['errors' => ['email' => 'Email already registered.']], 422);
    }

    $stmt = $pdo->prepare(
        'SELECT id, otp_hash FROM registration_otps
         WHERE email = :email AND consumed_at IS NULL AND expires_at >= UTC_TIMESTAMP()
         ORDER BY id DESC LIMIT 1'
    );
    $stmt->execute(['email' => $email]);
    $otpRow = $stmt->fetch();
} catch (Throwable $e) {
    error_log('Resident registration lookup failed: ' . $e->getMessage());
    $debug = defined('APP_DEBUG') && APP_DEBUG ? ['debug' => $e->getMessage()] : [];
    json_response(false, 'Could not verify your registration right now. Please try again.', $debug, 500);
}

if (!$otpRow || !hash_equals($otpRow['otp_hash'], hash_hmac('sha256', $otp, $email))) {
    json_response(false, 'That verification code is invalid or has expired.', ['errors' => ['otp' => 'Invalid or expired code.']], 422);
}

/* ---------------------------------------------------------------
 * Store uploaded photos
 * ------------------------------------------------------------- */
$idFrontPath = store_uploaded_image($_FILES['valid_id_front'], 'id', 'id_front');
$idBackPath  = store_uploaded_image($_FILES['valid_id_back'], 'id', 'id_back');
$profilePath = store_uploaded_image($_FILES['profile_photo'], 'profile', 'profile');
$coverPath   = null;
if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] === UPLOAD_ERR_OK) {
    $coverPath = store_uploaded_image($_FILES['cover_photo'], 'cover', 'cover');
}

if (!$idFrontPath || !$idBackPath || !$profilePath) {
    json_response(false, 'One or more photos could not be processed. Please use a JPG, PNG, or WebP under 5MB.', [], 422);
}

/* ---------------------------------------------------------------
 * Insert the resident record + consume the OTP, as one transaction.
 * ------------------------------------------------------------- */
try {
    $pdo->beginTransaction();

    $username = strtolower(preg_replace('/[^a-z0-9]/i', '', $firstName . $lastName)) . random_int(100, 999);
    $userStmt = $pdo->prepare(
        'INSERT INTO users (role, username, email, phone, password_hash, profile_image, cover_photo, is_verified)
         VALUES ("resident", :username, :email, :phone, :password_hash, :profile_image, :cover_photo, 0)'
    );
    $userStmt->execute([
        'username' => $username,
        'email' => $email,
        'phone' => $mobileNumber,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'profile_image' => $profilePath,
        'cover_photo' => $coverPath,
    ]);
    $userId = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare(
        'INSERT INTO residents (
            resident_id, user_id, first_name, middle_name, last_name, birthdate, age, place_of_birth,
            gender, civil_status, nationality, religion, purok, complete_address, years_of_residency,
            mobile_number, voter_status, educational_attainment, employment_status, occupation,
            household_head, emergency_contact_name, emergency_contact_number, number_of_family_member,
            blood_type, disabilities, valid_id_path, valid_id_back_path, profile_image_path, cover_photo_path
        ) VALUES (
            :resident_id, :user_id, :first_name, :middle_name, :last_name, :birthdate, :age, :place_of_birth,
            :gender, :civil_status, :nationality, :religion, :purok, :complete_address, :years_of_residency,
            :mobile_number, :voter_status, :educational_attainment, :employment_status, :occupation,
            :household_head, :emergency_contact_name, :emergency_contact_number, :number_of_family_members,
            :blood_type, :disabilities, :valid_id_path, :valid_id_back_path, :profile_image_path, :cover_photo_path
        )'
    );
    $stmt->execute([
        'resident_id' => generateResidentId(),
        'user_id' => $userId,
        'first_name'  => $firstName,
        'middle_name' => $middleName !== '' ? $middleName : null,
        'last_name'   => $lastName,
        'birthdate'   => $birthdate,
        'age'         => $age,
        'place_of_birth' => $placeOfBirth,
        'gender' => $gender,
        'civil_status' => $civilStatus,
        'nationality' => $nationality,
        'religion' => $religion,
        'purok' => $purok,
        'complete_address' => $completeAddress,
        'years_of_residency' => $yearsOfResidency,
        'mobile_number' => $mobileNumber,
        'voter_status' => $voterStatus,
        'educational_attainment' => $education,
        'employment_status' => $employmentStatus,
        'occupation' => $occupation !== '' ? $occupation : null,
        'household_head' => $householdHead,
        'number_of_family_members' => $familyMembers,
        'emergency_contact_name' => $emergencyContactName,
        'emergency_contact_number' => $emergencyContactNumber,
        'blood_type' => $bloodType,
        'disabilities' => $disability,
        'valid_id_path' => $idFrontPath,
        'valid_id_back_path' => $idBackPath,
        'profile_image_path' => $profilePath,
        'cover_photo_path' => $coverPath,
    ]);

    $update = $pdo->prepare('UPDATE registration_otps SET consumed_at = UTC_TIMESTAMP() WHERE id = :id');
    $update->execute(['id' => $otpRow['id']]);

    $pdo->commit();
} catch (Throwable $e) {
    error_log('Resident registration insert failed: ' . $e->getMessage());
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $debug = defined('APP_DEBUG') && APP_DEBUG ? ['debug' => $e->getMessage()] : [];
    json_response(false, 'Could not create your account right now. Please try again.', $debug, 500);
}

$residentName = trim($firstName . ' ' . ($middleName !== '' ? $middleName . ' ' : '') . $lastName);
$notificationMessage = 'Your resident account was created and pending to review. We will sent an email/sms once your account is activated.';
$emailSent = sendMail(
    $email,
    'SafeBrgy resident account registration received',
    '<p>Hello ' . htmlspecialchars($residentName, ENT_QUOTES, 'UTF-8') . ',</p>'
    . '<p>' . htmlspecialchars($notificationMessage, ENT_QUOTES, 'UTF-8') . '</p>'
);
$smsSent = sendSms($mobileNumber, $notificationMessage);

try {
    logNotificationEvent([
        'user_id' => $userId,
        'email' => $email,
        'mobile_number' => $mobileNumber,
        'event_type' => 'resident_registration_received',
        'event_meta' => ['resident_name' => $residentName],
        'email_sent' => $emailSent,
        'sms_sent' => $smsSent,
        'status' => ($emailSent || $smsSent) ? 'sent' : 'failed',
    ]);
} catch (Throwable $e) {
    error_log('Resident registration notification logging failed: ' . $e->getMessage());
}

json_response(
    true,
    $notificationMessage,
    ['notifications' => ['email' => $emailSent, 'sms' => $smsSent]]
);
