<?php
require_once __DIR__ . '/../config/db.php';

session_start();

if (empty($_SESSION['admin_user'])) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['user'])) {
    $_SESSION['user'] = $_SESSION['admin_user']['username'] ?? (
        !empty($_SESSION['admin_user']['email']) ? strtok($_SESSION['admin_user']['email'], '@') : 'Admin'
    );
}

if (empty($_SESSION['email']) && !empty($_SESSION['admin_user']['email'])) {
    $_SESSION['email'] = $_SESSION['admin_user']['email'];
}

if (empty($_SESSION['admin_id']) && !empty($_SESSION['admin_user']['id'])) {
    $_SESSION['admin_id'] = $_SESSION['admin_user']['id'];
}
