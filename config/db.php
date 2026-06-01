<?php
/**
 * Database bootstrap for SafeBrgy.
 * Connects to MySQL, creates the safebrgy database if needed,
 * and initializes the schema from sql/safebrgy_schema.sql.
 */
require_once __DIR__ . '/env.php';

if (!defined('SAFE_BRGY_DB_LOADED')) {
    define('SAFE_BRGY_DB_LOADED', true);
}

function safeBrgy_db_connect(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        $schemaFile = __DIR__ . '/../sql/safebrgy_schema.sql';
        if (DB_INIT_SCHEMA && file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            $sql = str_replace(["\r\n", "\r"], "\n", $sql);
            $sql = preg_replace('/--.*$/m', '', $sql);
            $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $statement) {
                if ($statement === '') {
                    continue;
                }
                $pdo->exec($statement);
            }
        }

        $pdo->exec(sprintf('USE `%s`', DB_NAME));
        return $pdo;
    } catch (PDOException $exception) {
        http_response_code(500);
        echo '<h1>Database connection failed</h1>';
        echo '<p>' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
        exit;
    }
}

$pdo = safeBrgy_db_connect();
