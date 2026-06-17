<?php

// ── Database credentials ────────────────────────────────────────────────────
// Priority order:
//   1. db_config.php in this directory  (copy db_config.example.php → db_config.php on your host)
//   2. config.php at the repository root (used in the shared dev environment)
//   3. XAMPP localhost defaults          (local development fallback)
// ───────────────────────────────────────────────────────────────────────────
$_hg_cfg_local = __DIR__ . '/db_config.php';
$_hg_cfg_root  = dirname(dirname(__DIR__)) . '/config.php';

if (file_exists($_hg_cfg_local)) {
    $_hg_cfg = require $_hg_cfg_local;
} elseif (file_exists($_hg_cfg_root)) {
    $_hg_cfg = require $_hg_cfg_root;
} else {
    $_hg_cfg = [];
}

$servername = isset($_hg_cfg['host']) ? $_hg_cfg['host'] : 'localhost';
$username   = isset($_hg_cfg['user']) ? $_hg_cfg['user'] : 'root';
$password   = isset($_hg_cfg['pass']) ? $_hg_cfg['pass'] : '';
$dbname     = isset($_hg_cfg['name']) ? $_hg_cfg['name'] : 'hawker';
$port       = isset($_hg_cfg['port']) ? (int) $_hg_cfg['port'] : 3306;

unset($_hg_cfg, $_hg_cfg_local, $_hg_cfg_root);

// Create connection (port is required for filess.io which uses 3307)
$db = mysqli_connect($servername, $username, $password, $dbname, $port);
// Check connection
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}

require_once dirname(__DIR__) . '/includes/schema.php';
require_once dirname(__DIR__) . '/includes/order_helpers.php';
require_once dirname(__DIR__) . '/includes/auth_helpers.php';
hawkergo_ensure_schema($db);

?>