<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/shared/remember_me.php';

session_start();

revokeRememberMeToken(safeBrgy_db_connect());

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

header('Location: index.php');
exit;
