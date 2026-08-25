<?php
/**
 * functions.php
 * Shared helpers + reference data (dropdown options) for the resident
 * registration wizard. Included by index.php and every /api endpoint.
 */

declare(strict_types=1);

/* --------------------------------------------------------------------
 * Reference data — keep option lists in one place so the markup,
 * the client-side review step, and server-side validation always agree.
 * ------------------------------------------------------------------ */

const GENDER_OPTIONS = ['Male', 'Female'];

const CIVIL_STATUS_OPTIONS = [
    'Single', 'Married', 'Widowed', 'Separated', 'Divorced', 'Annulled', 'Live-in / Common-law',
];

const NATIONALITY_OPTIONS = [
    'Filipino', 'American', 'Australian', 'British', 'Canadian', 'Chinese',
    'Indian', 'Indonesian', 'Japanese', 'Korean', 'Malaysian', 'Singaporean',
    'Spanish', 'Others',
];

const RELIGION_OPTIONS = [
    'Roman Catholic', 'Iglesia ni Cristo', 'Islam', 'Christian - Born Again',
    'Protestant', 'Seventh-Day Adventist', "Jehovah's Witness", 'Baptist',
    'Methodist', 'Buddhist', 'Hindu', 'Others', 'Prefer not to say',
];

const PUROK_OPTIONS = [
    'Miranda', 'Musni', 'Manena', 'Proper', 'Manggahan', 'P. Kawayan', 'P. Salas', 'Tabon',
];

const VOTER_STATUS_OPTIONS = [
    'Registered Voter', 'Not a Registered Voter', 'Registered but Inactive',
];

const EDUCATION_OPTIONS = [
    'No Formal Education', 'Elementary Undergraduate', 'Elementary Graduate',
    'High School Undergraduate', 'High School Graduate', 'Senior High School Graduate',
    'Vocational / Technical', 'College Undergraduate', 'College Graduate',
    'Post Graduate', 'Doctorate',
];

const EMPLOYMENT_OPTIONS = [
    'Employed - Full time', 'Employed - Part time', 'Self-Employed',
    'Unemployed', 'Student', 'Retired', 'OFW (Overseas Filipino Worker)', 'Not in Labor Force',
];

const BLOOD_TYPE_OPTIONS = [
    'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Unknown',
];

const DISABILITY_OPTIONS = [
    'N/A (None)', 'Visual Impairment', 'Hearing Impairment', 'Speech Impairment',
    'Physical / Orthopedic Disability', 'Intellectual Disability',
    'Psychosocial Disability', 'Learning Disability', 'Multiple Disabilities', 'Others',
];

/* --------------------------------------------------------------------
 * Rendering helper
 * ------------------------------------------------------------------ */

/**
 * Echo <option> tags for a plain list of string values.
 */
function render_options(array $options, string $placeholder = 'Select an option'): void
{
    echo '<option value="" selected disabled>' . htmlspecialchars($placeholder) . '</option>';
    foreach ($options as $option) {
        echo '<option value="' . htmlspecialchars($option) . '">' . htmlspecialchars($option) . '</option>';
    }
}

/* --------------------------------------------------------------------
 * CSRF protection
 * ------------------------------------------------------------------ */

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(string $token): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/* --------------------------------------------------------------------
 * Validation / sanitation helpers (used by the api/ endpoints)
 * ------------------------------------------------------------------ */

function clean_str(mixed $value): string
{
    return trim(htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8'));
}

/**
 * Accepts a PH mobile number in either "9XXXXXXXXX" (10 digits) or
 * "+639XXXXXXXXX" form and returns it normalised to "+639XXXXXXXXX",
 * or null if it does not match a valid PH mobile pattern.
 */
function normalize_ph_mobile(string $raw): ?string
{
    $digits = preg_replace('/\D/', '', $raw);

    // Strip a leading country/trunk prefix so we're left with 10 digits.
    if (str_starts_with($digits, '63') && strlen($digits) === 12) {
        $digits = substr($digits, 2);
    } elseif (str_starts_with($digits, '0') && strlen($digits) === 11) {
        $digits = substr($digits, 1);
    }

    if (preg_match('/^9\d{9}$/', $digits) === 1) {
        return '+63' . $digits;
    }

    return null;
}

function is_valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function calculate_age_from_birthdate(string $birthdate): ?int
{
    $dob = DateTime::createFromFormat('Y-m-d', $birthdate);
    if (!$dob) {
        return null;
    }
    $today = new DateTime('today');
    if ($dob > $today) {
        return null;
    }
    return $dob->diff($today)->y;
}

/**
 * Standard JSON response + exit, used by every api/ endpoint.
 */
function json_response(bool $success, string $message, array $extra = [], int $httpCode = 200): never
{
    http_response_code($httpCode);
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}

function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(false, 'Invalid request method.', [], 405);
    }
}

function ensure_upload_dirs(): void
{
    $base = __DIR__ . '/../uploads';
    foreach (['id', 'profile', 'cover'] as $sub) {
        $dir = $base . '/' . $sub;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

/**
 * Move an uploaded image into /uploads/<subfolder> under a random-safe
 * name, after checking it really is an image and within size limits.
 * Returns the relative path to store in the database, or null on failure.
 */
function store_uploaded_image(array $file, string $subfolder, string $prefix, int $maxBytes = 5 * 1024 * 1024): ?string
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if ($file['size'] > $maxBytes) {
        return null;
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return null;
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = $imageInfo['mime'];
    if (!isset($allowed[$mime])) {
        return null;
    }

    ensure_upload_dirs();

    $filename = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    $uploadBase = $subfolder === 'id' ? dirname(__DIR__) . '/../uploads' : __DIR__ . '/../uploads';
    $destination = $uploadBase . '/' . $subfolder . '/' . $filename;

    $destinationDirectory = dirname($destination);
    if (!is_dir($destinationDirectory)) {
        mkdir($destinationDirectory, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return null;
    }

    return 'uploads/' . $subfolder . '/' . $filename;
}
