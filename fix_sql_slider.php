<?php
// Parse .env manually to avoid loading Laravel
$envFile = __DIR__ . '/.env';
$env = [];
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        list($name, $value) = explode('=', $line, 2);
        $env[trim($name)] = trim($value);
    }
}

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$db = $env['DB_DATABASE'] ?? 'sikerja';
$user = $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';

echo "Connecting to database $db at $host:$port...\n";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if columns exist
    $stmt = $pdo->query("SHOW COLUMNS FROM slider LIKE 'desc'");
    if ($stmt->rowCount() == 0) {
        echo "Adding 'desc' column...\n";
        $pdo->exec("ALTER TABLE slider ADD COLUMN `desc` TEXT NULL AFTER `label`");
    } else {
        echo "'desc' column already exists.\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM slider LIKE 'is_active'");
    if ($stmt->rowCount() == 0) {
        echo "Adding 'is_active' column...\n";
        $pdo->exec("ALTER TABLE slider ADD COLUMN `is_active` BOOLEAN DEFAULT TRUE AFTER `kategori`");
    } else {
        echo "'is_active' column already exists.\n";
    }

    echo "Migration completed successfully.\n";

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
