<?php
require 'config/db.php';
$pdo = safeBrgy_db_connect();
$pdo->exec("CREATE TABLE IF NOT EXISTS requests (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, reference_no VARCHAR(30) NOT NULL UNIQUE, document_type ENUM('Barangay Clearance','Barangay Residency','Barangay Indigency','Barangay Business Clearance') NOT NULL, resident_name VARCHAR(150) NOT NULL, resident_email VARCHAR(150) NOT NULL, supporting_file VARCHAR(255) NULL, status ENUM('Pending','Approved','Rejected','Ready for Pickup') NOT NULL DEFAULT 'Pending', submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB");
$pdo->exec("CREATE TABLE IF NOT EXISTS barangay_clearance (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, request_id INT UNSIGNED NOT NULL, purpose TEXT NOT NULL, CONSTRAINT fk_clearance_request FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE) ENGINE=InnoDB");
$pdo->prepare("INSERT INTO requests (reference_no, document_type, resident_name, resident_email, supporting_file, status) VALUES (?, ?, ?, ?, ?, 'Pending')")->execute(['TEST-REF','Barangay Clearance','Test Resident','test@example.com',null]);
echo 'ok';
