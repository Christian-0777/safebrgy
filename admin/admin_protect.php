<?php
require_once __DIR__ . '/../config/db.php';

session_start();

if (!function_exists('adminDisplayName')) {
    function adminDisplayName(string $name): string
    {
        $name = trim($name);
        return preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $name) ?: $name;
    }
}

if (!function_exists('adminAssetUrl')) {
    function adminAssetUrl($path): string
    {
        $path = trim((string) $path);
        if ($path === '') return '';
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;

        $path = '/' . ltrim(str_replace('\\', '/', $path), '/');
        $scriptDirectory = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        $applicationRoot = preg_replace('#/admin(?:/.*)?$#', '', $scriptDirectory) ?: '';
        return strpos($path, $applicationRoot . '/') === 0 ? $path : $applicationRoot . $path;
    }
}

if (empty($_SESSION['admin_user'])) {
    header('Location: login.php');
    exit;
}

if (
    !is_array($_SESSION['user'] ?? null)
    || ($_SESSION['user']['role'] ?? '') !== 'admin'
    || (int) ($_SESSION['user']['id'] ?? 0) !== (int) ($_SESSION['admin_user']['id'] ?? 0)
) {
    $_SESSION['user'] = [
        'id' => (int) ($_SESSION['admin_user']['id'] ?? 0),
        'role' => 'admin',
        'name' => adminDisplayName($_SESSION['admin_user']['username'] ?? (
            !empty($_SESSION['admin_user']['email']) ? strtok($_SESSION['admin_user']['email'], '@') : 'Admin'
        )),
        'email' => $_SESSION['admin_user']['email'] ?? '',
    ];
}

if (empty($_SESSION['email']) && !empty($_SESSION['admin_user']['email'])) {
    $_SESSION['email'] = $_SESSION['admin_user']['email'];
}

if (empty($_SESSION['admin_id']) && !empty($_SESSION['admin_user']['id'])) {
    $_SESSION['admin_id'] = $_SESSION['admin_user']['id'];
}
