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

                if (preg_match('/^CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?/i', $statement)) {
                    $statement = preg_replace('/^CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?/i', 'CREATE TABLE IF NOT EXISTS ', $statement, 1);
                }

                $pdo->exec($statement);
            }
        }

        $pdo->exec(sprintf('USE `%s`', DB_NAME));

        // Ensure requests.status enum supports the full workflow statuses.
        $statusColumn = $pdo->query("SHOW COLUMNS FROM requests WHERE Field = 'status'")->fetch(PDO::FETCH_ASSOC);
        if ($statusColumn && preg_match('/^enum\((.*)\)$/', $statusColumn['Type'], $matches)) {
            $currentValues = array_map(function ($value) {
                return trim($value, "'\"");
            }, explode(',', $matches[1]));

            $expectedValues = ['Pending', 'Approved', 'Rejected', 'Ready for Pickup', 'Processing', 'Received'];
            $missingValues = array_values(array_diff($expectedValues, $currentValues));

            if (!empty($missingValues)) {
                $allValues = array_unique(array_merge($currentValues, $expectedValues));
                $enumType = "ENUM('" . implode("','", $allValues) . "')";
                $pdo->exec("ALTER TABLE requests MODIFY status $enumType NOT NULL DEFAULT 'Pending'");
            }
        }

        // Ensure requests.date_received exists for received-status timestamps.
        $columns = $pdo->query('SHOW COLUMNS FROM requests')->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('date_received', $columns, true)) {
            $pdo->exec('ALTER TABLE requests ADD COLUMN date_received DATETIME NULL AFTER updated_at');
        }

        return $pdo;
    } catch (PDOException $exception) {
        http_response_code(500);
        echo '<h1>Database connection failed</h1>';
        echo '<p>' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
        exit;
    }
}

/**
 * Generate a unique 7-digit resident ID
 * @return string A unique 7-digit resident ID
 */
function generateResidentId(): string
{
    $pdo = safeBrgy_db_connect();
    
    $maxAttempts = 100;
    $attempts = 0;
    
    while ($attempts < $maxAttempts) {
        // Generate a random 7-digit number (1000000 to 9999999)
        $residentId = str_pad(random_int(1000000, 9999999), 7, '0', STR_PAD_LEFT);
        
        // Check if this ID already exists
        $stmt = $pdo->prepare('SELECT id FROM residents WHERE resident_id = ?');
        $stmt->execute([$residentId]);
        
        if (!$stmt->fetch()) {
            // ID is unique
            return $residentId;
        }
        
        $attempts++;
    }
    
    // Fallback - should rarely happen
    throw new Exception('Failed to generate unique resident ID after maximum attempts');
}

$pdo = safeBrgy_db_connect();
