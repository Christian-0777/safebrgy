<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) ? true : false;

    // Validate inputs
    if (empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Email and password are required'
        ]);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email format'
        ]);
        exit;
    }

    // Simple demo user (replace with database query)
    $valid_users = [
        'user@example.com' => 'password123',
        'demo@test.com' => 'demo123'
    ];

    if (isset($valid_users[$email]) && $valid_users[$email] === $password) {
        // Start session
        session_start();
        $_SESSION['user_email'] = $email;
        $_SESSION['login_time'] = time();

        // Set remember me cookie if checked
        if ($remember) {
            setcookie('user_email', $email, time() + (30 * 24 * 60 * 60), '/');
        }

        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => 'dashboard.php'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>
