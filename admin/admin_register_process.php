<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mailer.php';
session_start();

function redirectWithError(string $message)
{
    $_SESSION['flash_error'] = $message;
    header('Location: /safebrgy/admin/register.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['number'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';
$fullName = trim($_POST['fullName'] ?? '');
$agreeTerms = isset($_POST['agreeTerms']);

if ($email === '' || $phone === '' || $password === '' || $confirmPassword === '' || $fullName === '') {
    redirectWithError('Please fill in all required fields.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWithError('Please enter a valid email address.');
}

if ($password !== $confirmPassword) {
    redirectWithError('Passwords do not match.');
}

if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
    redirectWithError('Password must be at least 8 characters and contain uppercase, lowercase, and numbers.');
}

$normalizedPhone = preg_replace('/[^0-9\+]/', '', $phone);
if (str_starts_with($normalizedPhone, '0')) {
    $normalizedPhone = '+63' . substr($normalizedPhone, 1);
}
if (str_starts_with($normalizedPhone, '63')) {
    $normalizedPhone = '+' . $normalizedPhone;
}
if (!preg_match('/^\+63[0-9]{10}$/', $normalizedPhone)) {
    redirectWithError('Please enter a valid Philippine mobile number in +63 format.');
}

if (!$agreeTerms) {
    redirectWithError('You must agree to the Terms of Use and Privacy Policy.');
}

$username = preg_replace('/\s+/', ' ', $fullName);
$pdo = safeBrgy_db_connect();
$check = $pdo->prepare('SELECT id FROM users WHERE email = :email');
$check->execute(['email' => $email]);
if ($check->fetch()) {
    redirectWithError('This email is already registered. Please log in or use a different email.');
}

$checkUsername = $pdo->prepare('SELECT id FROM users WHERE username = :username');
$checkUsername->execute(['username' => $username]);
if ($checkUsername->fetch()) {
    redirectWithError('This username is already taken. Please use a different full name.');
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$insert = $pdo->prepare(
    'INSERT INTO users (role, username, email, phone, password_hash, is_verified) VALUES (:role, :username, :email, :phone, :password_hash, 0)'
);
$insert->execute([
    'role' => 'admin',
    'username' => $username,
    'email' => $email,
    'phone' => $normalizedPhone,
    'password_hash' => $passwordHash,
]);

$userId = (int) $pdo->lastInsertId();
$otpCode = (string) random_int(1000000, 9999999);

if (!sendAdminOtpEmail($email, $otpCode)) {
    $delete = $pdo->prepare('DELETE FROM users WHERE id = :id');
    $delete->execute(['id' => $userId]);
    redirectWithError('Unable to send verification email at this time. Please try again later.');
}

function maskEmail(string $email): string
{
    [$local, $domain] = explode('@', $email, 2) + ['', ''];
    $visible = substr($local, 0, 2);
    $hidden = str_repeat('*', max(0, strlen($local) - 2));
    return $visible . $hidden . '@' . $domain;
}

$_SESSION['pending_verification'] = true;
$_SESSION['verification_method'] = 'email';
$_SESSION['masked_target'] = maskEmail($email);
$_SESSION['pending_user_id'] = $userId;
$_SESSION['pending_email'] = $email;
$_SESSION['otp_code'] = $otpCode;
$_SESSION['otp_expires'] = time() + 300;

header('Location: /safebrgy/admin/otp-view.php');
exit;
