<?php
// One shared database connection, reused by every API file.
// Usage in any file:  require 'db.php';  then call  db()
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $cfg = require __DIR__ . '/config.php';
        $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw on errors (easier to debug)
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // rows as clean arrays
            PDO::ATTR_EMULATE_PREPARES   => false,                 // real prepared statements (safer)
        ]);
    }
    return $pdo;
}
