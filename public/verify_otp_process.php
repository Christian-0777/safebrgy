<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mailer.php';
session_start();

$type = $_GET['type'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp_code = $_POST['otp_code'] ?? '';

    if ($type === 'registration') {
        if (!isset($_SESSION['pending_registration'])) {
            echo json_encode(['success' => false, 'message' => 'No pending registration']);
            exit;
        }

        $pending = $_SESSION['pending_registration'];

        if ($otp_code === $pending['otp'] && strtotime($pending['otp_expiry']) > time()) {
            // OTP valid, create user and resident
            $pdo = safeBrgy_db_connect();

            try {
                $pdo->beginTransaction();

                // Insert user
                $stmt = $pdo->prepare('
                    INSERT INTO users (role, username, email, phone, password_hash, profile_image, is_verified)
                    VALUES (?, ?, ?, ?, ?, ?, 0)
                ');
                $username = strtolower(str_replace(' ', '', $pending['first_name'] . $pending['last_name'])) . rand(100, 999);
                $stmt->execute([
                    'resident',
                    $username,
                    $pending['email'],
                    $pending['mobile_number'],
                    $pending['password'],
                    $pending['profile_image_path']
                ]);
                $user_id = $pdo->lastInsertId();

                // Insert resident
                $stmt = $pdo->prepare('
                    INSERT INTO residents (
                        resident_id, user_id, first_name, middle_name, last_name, birthdate, age, place_of_birth,
                        gender, civil_status, nationality, religion, complete_address, purok,
                        years_of_residency, mobile_number, voter_status, employment_status, occupation,
                        household_head, emergency_contact_name, number_of_family_member,
                        educational_attainment, blood_type, disabilities, valid_id_path, profile_image_path
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ');
                $stmt->execute([
                    $pending['resident_id'],
                    $user_id,
                    $pending['first_name'],
                    $pending['middle_name'],
                    $pending['last_name'],
                    $pending['birthdate'],
                    $pending['age'],
                    $pending['place_of_birth'],
                    $pending['gender'],
                    $pending['civil_status'],
                    $pending['nationality'],
                    $pending['religion'],
                    $pending['complete_address'],
                    $pending['purok'],
                    $pending['years_of_residency'],
                    $pending['mobile_number'],
                    $pending['voter_status'],
                    $pending['employment_status'],
                    $pending['occupation'],
                    $pending['household_head'],
                    $pending['emergency_contact_name'],
                    $pending['number_of_family_member'],
                    $pending['educational_attainment'],
                    $pending['blood_type'],
                    $pending['disabilities'],
                    $pending['valid_id_path'],
                    $pending['profile_image_path']
                ]);

                $pdo->commit();

                // Clear session
                unset($_SESSION['pending_registration']);

                echo json_encode(['success' => true, 'message' => 'Account created successfully. Please wait for admin approval.']);
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'Failed to create account: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP']);
        }
    } elseif ($type === 'login') {
        // Handle admin login OTP
        if (!isset($_SESSION['pending_verification'])) {
            echo json_encode(['success' => false, 'message' => 'No pending verification']);
            exit;
        }

        $pending = $_SESSION['pending_verification'];

        if ($otp_code === $pending['otp'] && strtotime($pending['otp_expiry']) > time()) {
            // OTP valid, log in user
            $_SESSION['user'] = $pending['user'];
            unset($_SESSION['pending_verification']);

            echo json_encode(['success' => true, 'redirect' => '../admin/main-pages/dashboard.php']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid type']);
    }
} else {
    header('Location: login.php');
}
?>