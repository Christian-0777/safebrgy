<?php
require_once __DIR__ . '/../../config/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'resident') {
    header('Location: ../../public/login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = safeBrgy_db_connect();

    try {
        $firstName = trim($_POST['firstName'] ?? '');
        $middleName = trim($_POST['middleName'] ?? '');
        $lastName = trim($_POST['lastName'] ?? '');
        $suffix = trim($_POST['suffix'] ?? '');
        $gender = trim($_POST['gender'] ?? '');
        $birthdate = trim($_POST['birthdate'] ?? '');
        $civilStatus = trim($_POST['civilStatus'] ?? '');
        $nationality = trim($_POST['nationality'] ?? '');
        $occupation = trim($_POST['occupation'] ?? '');

        // Validation
        if (empty($firstName) || empty($lastName)) {
            throw new Exception('First name and last name are required');
        }

        // Validate birthdate format if provided
        if (!empty($birthdate)) {
            $birthdateObj = DateTime::createFromFormat('Y-m-d', $birthdate);
            if (!$birthdateObj) {
                throw new Exception('Invalid birthdate format');
            }
        }

        // Update residents table
        $stmt = $pdo->prepare('
            UPDATE residents 
            SET first_name = ?, middle_name = ?, last_name = ?, 
                gender = ?, birthdate = ?, civil_status = ?, 
                nationality = ?, occupation = ?, updated_at = NOW()
            WHERE user_id = ?
        ');
        
        $stmt->execute([
            $firstName,
            $middleName,
            $lastName,
            $gender,
            $birthdate,
            $civilStatus,
            $nationality,
            $occupation,
            $user_id
        ]);

        // Update session with new name
        $_SESSION['user']['name'] = trim("$firstName $lastName");
        
        $_SESSION['account_success'] = 'Personal information updated successfully';
        
    } catch (Exception $e) {
        $_SESSION['account_error'] = $e->getMessage();
    }

    header('Location: ../../public/public-pages/account.php');
    exit;
} else {
    header('Location: ../../public/public-pages/account.php');
    exit;
}
?>
