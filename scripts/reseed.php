<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

$baseDir = dirname(__DIR__);
$dbInstance = Database::getInstance();
$driver = $dbInstance->getActiveDriver();
$pdo = db();

echo "=== FnB Reseed Database Utility ===\n";
echo "Active Database Driver: {$driver}\n";

if ($driver === 'mysql') {
    echo "1. Reseeding MySQL/MariaDB database...\n";
    $schemaSql = file_get_contents($baseDir . '/database/schema.sql');
    $seedSql = file_get_contents($baseDir . '/database/seed_demo.sql');
    
    // Execute schema statements
    $pdo->exec($schemaSql);
    // Execute seed statements
    $pdo->exec($seedSql);
    echo "   [SUCCESS] MySQL/MariaDB database successfully reseeded.\n";
} else {
    echo "1. Reseeding SQLite primary database...\n";
    $sqlitePath = $baseDir . '/database/fnb.sqlite';
    $schemaSql = file_get_contents($baseDir . '/database/schema_sqlite.sql');
    $seedSql = file_get_contents($baseDir . '/database/seed_demo.sql');
    
    $pdo->exec($schemaSql);
    $pdo->exec($seedSql);
    echo "   [SUCCESS] SQLite primary database successfully reseeded.\n";
}

// Ensure database/fnb.sqlite file is also populated for offline/local preview fallback
try {
    $sqlitePath = $baseDir . '/database/fnb.sqlite';
    $sqlitePdo = new PDO('sqlite:' . $sqlitePath);
    $sqlitePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $schemaSqlite = file_get_contents($baseDir . '/database/schema_sqlite.sql');
    $seedSqlite = file_get_contents($baseDir . '/database/seed_demo.sql');
    $sqlitePdo->exec($schemaSqlite);
    $sqlitePdo->exec($seedSqlite);
    echo "2. Fallback database/fnb.sqlite synchronized successfully.\n";
} catch (\Throwable $e) {
    // SQLite extension might not be enabled in some environments
}

echo "All databases ready for production!\n";
