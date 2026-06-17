<?php

function hawkergo_column_exists($db, $table, $column)
{
    $stmt = $db->prepare(
        "SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?"
    );
    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return !empty($row['cnt']);
}

function hawkergo_add_column_if_missing($db, $table, $column, $definition)
{
    if (!hawkergo_column_exists($db, $table, $column)) {
        mysqli_query($db, "ALTER TABLE `$table` ADD COLUMN $definition");
    }
}

function hawkergo_table_exists($db, $table)
{
    $stmt = $db->prepare(
        "SELECT COUNT(*) AS cnt FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?"
    );
    $stmt->bind_param('s', $table);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return !empty($row['cnt']);
}

function hawkergo_rename_restaurant_table($db)
{
    if (hawkergo_table_exists($db, 'restaurant') && !hawkergo_table_exists($db, 'hawkerstalls')) {
        mysqli_query($db, 'RENAME TABLE `restaurant` TO `hawkerstalls`');
    }
}

function hawkergo_ensure_schema($db)
{
    hawkergo_rename_restaurant_table($db);

    hawkergo_add_column_if_missing($db, 'users', 'address', '`address` TEXT DEFAULT NULL');
    hawkergo_add_column_if_missing($db, 'dishes', 'is_sold_out', '`is_sold_out` TINYINT(1) NOT NULL DEFAULT 0');
    hawkergo_add_column_if_missing($db, 'hawkerstalls', 'accepting_orders', '`accepting_orders` TINYINT(1) NOT NULL DEFAULT 1');
    hawkergo_add_column_if_missing($db, 'users_orders', 'rs_id', '`rs_id` INT(11) DEFAULT NULL');
    hawkergo_add_column_if_missing($db, 'users_orders', 'queue_number', '`queue_number` INT(11) DEFAULT NULL');
    hawkergo_add_column_if_missing($db, 'users_orders', 'verification_code', '`verification_code` VARCHAR(10) DEFAULT NULL');
    hawkergo_add_column_if_missing($db, 'users_orders', 'guest_phone', '`guest_phone` VARCHAR(20) DEFAULT NULL');
    hawkergo_add_column_if_missing($db, 'users_orders', 'guest_name', '`guest_name` VARCHAR(100) DEFAULT NULL');
    hawkergo_add_column_if_missing($db, 'users_orders', 'special_request', '`special_request` TEXT DEFAULT NULL');
    hawkergo_add_column_if_missing($db, 'users_orders', 'order_batch_id', '`order_batch_id` VARCHAR(32) DEFAULT NULL');
    hawkergo_add_column_if_missing($db, 'users_orders', 'payment_method', '`payment_method` VARCHAR(30) DEFAULT NULL');
    hawkergo_add_column_if_missing($db, 'users_orders', 'order_type', '`order_type` VARCHAR(20) DEFAULT NULL');
    hawkergo_add_column_if_missing($db, 'users_orders', 'ordered_at', '`ordered_at` DATETIME DEFAULT CURRENT_TIMESTAMP');
    hawkergo_add_column_if_missing($db, 'users_orders', 'd_id', '`d_id` INT(11) DEFAULT NULL');
    hawkergo_add_column_if_missing($db, 'admin', 'reset_expires', '`reset_expires` DATETIME DEFAULT NULL');
    hawkergo_add_column_if_missing($db, 'hawker_users', 'reset_token', '`reset_token` VARCHAR(64) DEFAULT NULL');
    hawkergo_add_column_if_missing($db, 'hawker_users', 'reset_expires', '`reset_expires` DATETIME DEFAULT NULL');
    hawkergo_add_column_if_missing($db, 'users', 'reset_token', '`reset_token` VARCHAR(64) DEFAULT NULL');
    hawkergo_add_column_if_missing($db, 'users', 'reset_expires', '`reset_expires` DATETIME DEFAULT NULL');

    require_once __DIR__ . '/review_helpers.php';
    hawkergo_ensure_review_tables($db);

    require_once __DIR__ . '/hawker_helpers.php';
    hawkergo_ensure_hawker_portal_tables($db);

    mysqli_query(
        $db,
        "UPDATE users_orders o
         INNER JOIN dishes d ON d.title = o.title AND d.price = o.price
         SET o.rs_id = d.rs_id
         WHERE o.rs_id IS NULL OR o.rs_id = 0 OR o.rs_id <> d.rs_id"
    );

    mysqli_query(
        $db,
        "UPDATE users_orders o
         INNER JOIN dishes d ON d.rs_id = o.rs_id AND d.title = o.title
         SET o.d_id = d.d_id
         WHERE o.d_id IS NULL OR o.d_id = 0"
    );
}
