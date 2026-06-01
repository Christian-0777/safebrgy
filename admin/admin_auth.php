<?php
require_once __DIR__ . '/../config/db.php';
session_start();

function redirectWithError(string $message)
{
    $_SESSION['flash_error'] = $message;
    header('Location: /safebrgy/admin/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /safebrgy/admin/login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
if ($email === '' || $password === '') {
    redirectWithError('Please provide both email and password.');
}

$pdo = safeBrgy_db_connect();
$stmt = $pdo->prepare('SELECT id, username, email, password_hash, is_verified FROM users WHERE email = :email AND role = :role');
$stmt->execute(['email' => $email, 'role' => 'admin']);
$user = $stmt->fetch();

if (!$user) {
    redirectWithError('No Account Found! Please Click Register Now to Create Account.');
}

if (!password_verify($password, $user['password_hash'])) {
    redirectWithError('Uh oh! If you forgot your password, click the Forgot Password.');
}

if ((int) $user['is_verified'] !== 1) {
    redirectWithError('Your account is not verified yet. Please complete the OTP verification sent to your email.');
}

$_SESSION['admin_user'] = [
    'id' => $user['id'],
    'email' => $user['email'],
    'username' => $user['username'] ?: strtok($user['email'], '@'),
];

header('Location: /safebrgy/admin/main-pages/dashboard.php');
exit;
