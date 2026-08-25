<?php
/**
 * Environment bootstrap for SafeBrgy.
 * Reads .env values if present and sets default DB settings.
 */
if (!defined('SAFE_BRGY_ENV_LOADED')) {
    define('SAFE_BRGY_ENV_LOADED', true);

    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile) && is_readable($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $value = trim($value, "\"'");
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
    define('DB_PORT', getenv('DB_PORT') ?: '3306');
    define('DB_NAME', getenv('DB_NAME') ?: (getenv('DB_DATABASE') ?: 'safebrgy'));
    define('DB_USER', getenv('DB_USER') ?: (getenv('DB_USERNAME') ?: 'root'));
    define('DB_PASS', getenv('DB_PASS') ?: (getenv('DB_PASSWORD') ?: ''));
    define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');
    define('DB_SSL_CA', getenv('DB_SSL_CA') ?: '');
    define('DB_SSL_VERIFY', filter_var(getenv('DB_SSL_VERIFY') ?: 'true', FILTER_VALIDATE_BOOLEAN));
    define('DB_INIT_SCHEMA', filter_var(getenv('DB_INIT_SCHEMA') ?: 'false', FILTER_VALIDATE_BOOLEAN));
    define('APP_DEBUG', filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN));
}
