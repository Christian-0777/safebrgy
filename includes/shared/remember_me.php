<?php

const REMEMBER_ME_COOKIE = 'safebrgy_remember';
const REMEMBER_ME_TTL = 2592000;

function rememberMeCookieOptions(int $expires): array
{
    return [
        'expires' => $expires,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

function clearRememberMeCookie(): void
{
    setcookie(REMEMBER_ME_COOKIE, '', rememberMeCookieOptions(time() - 3600));
    unset($_COOKIE[REMEMBER_ME_COOKIE]);
}

function issueRememberMeToken(PDO $pdo, int $userId): void
{
    $selector = bin2hex(random_bytes(16));
    $validator = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    $expiresAt = date('Y-m-d H:i:s', time() + REMEMBER_ME_TTL);

    $stmt = $pdo->prepare(
        'INSERT INTO remember_tokens (user_id, selector, token_hash, expires_at)
         VALUES (:user_id, :selector, :token_hash, :expires_at)'
    );
    $stmt->execute([
        'user_id' => $userId,
        'selector' => $selector,
        'token_hash' => hash('sha256', $validator),
        'expires_at' => $expiresAt,
    ]);

    setcookie(
        REMEMBER_ME_COOKIE,
        $selector . '.' . $validator,
        rememberMeCookieOptions(time() + REMEMBER_ME_TTL)
    );
}

function restoreRememberedLogin(PDO $pdo): ?string
{
    if (!empty($_SESSION['user']) || !empty($_SESSION['admin_user'])) {
        return null;
    }

    $cookie = $_COOKIE[REMEMBER_ME_COOKIE] ?? '';
    if (!preg_match('/^([a-f0-9]{32})\.([A-Za-z0-9_-]{43})$/', $cookie, $matches)) {
        if ($cookie !== '') {
            clearRememberMeCookie();
        }
        return null;
    }

    [$selector, $validator] = [$matches[1], $matches[2]];
    $stmt = $pdo->prepare(
        'SELECT rt.id AS token_id, rt.token_hash, u.id AS user_id, u.role, u.username, u.email, u.is_verified,
                r.first_name, r.last_name, r.mobile_number
         FROM remember_tokens rt
         INNER JOIN users u ON u.id = rt.user_id
         LEFT JOIN residents r ON r.user_id = u.id
         WHERE rt.selector = :selector AND rt.expires_at > NOW()'
    );
    $stmt->execute(['selector' => $selector]);
    $user = $stmt->fetch();

    if (!$user || !hash_equals($user['token_hash'] ?? '', hash('sha256', $validator)) || (int) $user['is_verified'] !== 1) {
        if ($user) {
            $pdo->prepare('DELETE FROM remember_tokens WHERE id = ?')->execute([$user['token_id']]);
        }
        clearRememberMeCookie();
        return null;
    }

    $pdo->prepare('DELETE FROM remember_tokens WHERE id = ?')->execute([$user['token_id']]);
    clearRememberMeCookie();
    session_regenerate_id(true);

    if ($user['role'] === 'admin') {
        $_SESSION['admin_user'] = [
            'id' => $user['user_id'],
            'email' => $user['email'],
            'username' => $user['username'] ?: strtok($user['email'], '@'),
        ];
        return 'admin';
    }

    $_SESSION['user'] = [
        'id' => $user['user_id'],
        'email' => $user['email'],
        'role' => 'resident',
        'name' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: $user['username'],
        'phone' => $user['mobile_number'] ?? '',
    ];
    return 'resident';
}

function revokeRememberMeToken(PDO $pdo): void
{
    $cookie = $_COOKIE[REMEMBER_ME_COOKIE] ?? '';
    if (preg_match('/^([a-f0-9]{32})\./', $cookie, $matches)) {
        $pdo->prepare('DELETE FROM remember_tokens WHERE selector = ?')->execute([$matches[1]]);
    }
    clearRememberMeCookie();
}