<?php
/**
 * Database connection settings.
 * Update these values to match your local MySQL setup (e.g. XAMPP/WAMP).
 */
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'barangay_resident_system');

// Sender used for the notification emails (Section: Email settings)
define('MAIL_FROM', 'no-reply@barangay-portal.test');
define('MAIL_FROM_NAME', 'Barangay Resident Portal');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
