<?php
/**
 * migrate.php
 * One-shot DB migration: runs database/schema.sql against the configured DB.
 * Usage:  php migrate.php
 *
 * Idempotent for the *roles/permissions/settings* INSERTs only if you use
 * a fresh DB. For an existing DB it will fail on duplicate keys — that's
 * intentional: re-running it would otherwise reset the admin password.
 */

require_once __DIR__ . '/config/constants.php';

$sqlFile = __DIR__ . '/database/schema.sql';
if (!is_readable($sqlFile)) {
    fwrite(STDERR, "schema.sql not readable at $sqlFile\n");
    exit(1);
}

// Connect WITHOUT a database name first (schema.sql creates it).
$dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=' . DB_CHARSET;
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    fwrite(STDERR, 'Connection failed: ' . $e->getMessage() . "\n");
    exit(1);
}

$sql = file_get_contents($sqlFile);

// Regenerate a REAL bcrypt hash for the default admin password so the
// schema doesn't ship a placeholder hash.
$hash = password_hash('password', PASSWORD_BCRYPT, ['cost' => 12]);
$sql = preg_replace(
    '/\$2y\$12\$G8C3bE4Xx8\/cZ9KvI8E5Oe2L8V3xR6W9P4Q5S6T7U8V9W0X1Y2Z3/',
    str_replace('$', '\\$', $hash),
    $sql
);

// MySQL doesn't support multiple statements in one prepare; split naively.
foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
    if ($stmt === '') continue;
    try {
        $pdo->exec($stmt);
    } catch (PDOException $e) {
        fwrite(STDERR, "Statement failed: " . substr($stmt, 0, 80) . "...\n  -> " . $e->getMessage() . "\n");
    }
}

echo "Migration complete.\n";
echo "Default admin: admin@example.com / password (CHANGE IMMEDIATELY)\n";
