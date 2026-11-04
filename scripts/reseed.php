<?php
$baseDir = dirname(__DIR__);
$sqlitePath = $baseDir . '/database/fnb.sqlite';
$schemaSql = file_get_contents($baseDir . '/database/schema_sqlite.sql');
$seedSql = file_get_contents($baseDir . '/database/seed_demo.sql');

if (file_exists($sqlitePath)) {
    unlink($sqlitePath);
}

$pdo = new PDO('sqlite:' . $sqlitePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec($schemaSql);
$pdo->exec($seedSql);
echo "Successfully reseeded database/fnb.sqlite\n";
