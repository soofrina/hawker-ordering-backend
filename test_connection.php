<?php
// Run this FIRST. Open it in your browser (or `php test_connection.php`).
// It tells you whether PHP can reach your filess.io database.
require __DIR__ . '/db.php';
header('Content-Type: text/plain');

try {
    $pdo = db();
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    echo "SUCCESS — connected to filess.io.\n";
    echo "MySQL version: {$version}\n\n";

    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in your database: " . ($tables ? implode(', ', $tables) : "(none yet — run schema.sql)") . "\n";
} catch (Throwable $e) {
    echo "FAILED — could not connect.\n";
    echo "Reason: " . $e->getMessage() . "\n\n";
    echo "Checklist:\n";
    echo " 1. Are host / port / name / user / pass in config.php EXACTLY as shown on filess.io?\n";
    echo " 2. Did you copy the port (filess.io usually isn't 3306)?\n";
    echo " 3. Is your filess.io database finished provisioning (give it a minute after creating)?\n";
}
