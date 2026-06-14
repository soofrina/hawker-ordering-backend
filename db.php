<?php
// One shared database connection, reused by every API file.
// Usage in any file:  require 'db.php';  then call  db()
//
// Credentials come from environment variables when they exist (used on
// hosts like Render), and fall back to config.php for local XAMPP.
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        if (getenv('DB_HOST')) {
            // Live host: read from environment variables.
            $cfg = [
                'host' => getenv('DB_HOST'),
                'port' => getenv('DB_PORT') ?: '3306',
                'name' => getenv('DB_NAME'),
                'user' => getenv('DB_USER'),
                'pass' => getenv('DB_PASS'),
            ];
        } else {
            // Local: read from config.php (kept out of GitHub).
            $cfg = require __DIR__ . '/config.php';
        }
        $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw on errors (easier to debug)
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // rows as clean arrays
            PDO::ATTR_EMULATE_PREPARES   => false,                 // real prepared statements (safer)
        ]);
    }
    return $pdo;
}
