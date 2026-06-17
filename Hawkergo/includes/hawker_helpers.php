<?php

function hawkergo_ensure_hawker_portal_tables($db)
{
    if (!hawkergo_table_exists($db, 'hawker_users')) {
        mysqli_query(
            $db,
            "CREATE TABLE `hawker_users` (
                `h_id` INT(11) NOT NULL AUTO_INCREMENT,
                `rs_id` INT(11) NOT NULL,
                `username` VARCHAR(60) NOT NULL,
                `password` VARCHAR(255) NOT NULL,
                `email` VARCHAR(120) DEFAULT NULL,
                `reset_token` VARCHAR(64) DEFAULT NULL,
                `reset_expires` DATETIME DEFAULT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`h_id`),
                UNIQUE KEY `uniq_hawker_username` (`username`),
                UNIQUE KEY `uniq_hawker_rs_id` (`rs_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    if (!hawkergo_table_exists($db, 'payment_issues')) {
        mysqli_query(
            $db,
            "CREATE TABLE `payment_issues` (
                `issue_id` INT(11) NOT NULL AUTO_INCREMENT,
                `o_id` INT(11) NOT NULL,
                `rs_id` INT(11) NOT NULL,
                `h_id` INT(11) NOT NULL,
                `description` TEXT NOT NULL,
                `status` VARCHAR(20) NOT NULL DEFAULT 'open',
                `admin_note` TEXT DEFAULT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`issue_id`),
                KEY `idx_payment_issues_rs_id` (`rs_id`),
                KEY `idx_payment_issues_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    hawkergo_seed_hawker_accounts($db);
}

function hawkergo_seed_hawker_accounts($db)
{
    $res = mysqli_query($db, 'SELECT rs_id, title FROM hawkerstalls ORDER BY rs_id');
    if (!$res) {
        return;
    }

    $default_password = password_hash('hawker123', PASSWORD_DEFAULT);

    while ($stall = mysqli_fetch_assoc($res)) {
        $rs_id = intval($stall['rs_id']);
        $username = 'stall' . $rs_id;

        $check = $db->prepare('SELECT h_id FROM hawker_users WHERE rs_id = ? LIMIT 1');
        $check->bind_param('i', $rs_id);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        $check->close();

        if ($exists) {
            continue;
        }

        $stmt = $db->prepare(
            'INSERT INTO hawker_users (rs_id, username, password, email) VALUES (?, ?, ?, ?)'
        );
        $email = 'stall' . $rs_id . '@hawker.local';
        $stmt->bind_param('isss', $rs_id, $username, $default_password, $email);
        $stmt->execute();
        $stmt->close();
    }
}

function hawkergo_hawker_is_logged_in()
{
    return !empty($_SESSION['hawker_id']) && !empty($_SESSION['hawker_rs_id']);
}

function hawkergo_require_hawker_login($redirect = 'index.php')
{
    if (!hawkergo_hawker_is_logged_in()) {
        header('Location: ' . $redirect);
        exit;
    }
}

function hawkergo_get_logged_in_hawker($db)
{
    if (!hawkergo_hawker_is_logged_in()) {
        return null;
    }

    $h_id = intval($_SESSION['hawker_id']);
    $stmt = $db->prepare(
        'SELECT hu.*, hs.title AS stall_name, hs.address, hs.phone, hs.o_hr, hs.c_hr, hs.o_days,
                hs.accepting_orders, hs.image
         FROM hawker_users hu
         INNER JOIN hawkerstalls hs ON hs.rs_id = hu.rs_id
         WHERE hu.h_id = ? LIMIT 1'
    );
    $stmt->bind_param('i', $h_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row;
}

function hawkergo_hawker_rs_id()
{
    return hawkergo_hawker_is_logged_in() ? intval($_SESSION['hawker_rs_id']) : 0;
}

function hawkergo_hawker_stall_image_src($image)
{
    $image = trim((string) $image);
    if ($image === '') {
        return '';
    }
    return '../admin/Res_img/' . $image;
}

function hawkergo_get_registered_hawker_stalls($db)
{
    $stalls = array();
    $sql = "SELECT hs.rs_id, hs.title, hs.address, hs.image
            FROM hawkerstalls hs
            INNER JOIN hawker_users hu ON hu.rs_id = hs.rs_id
            ORDER BY hs.title";
    $res = mysqli_query($db, $sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $stalls[] = $row;
        }
    }
    return $stalls;
}

function hawkergo_hawker_login($db, $rs_id, $password)
{
    $rs_id = intval($rs_id);
    if ($rs_id <= 0 || $password === '') {
        return array('success' => false, 'message' => 'Please select your hawker stall and enter password.');
    }

    $stmt = $db->prepare('SELECT h_id, rs_id, password FROM hawker_users WHERE rs_id = ? LIMIT 1');
    $stmt->bind_param('i', $rs_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || !password_verify($password, $row['password'])) {
        return array('success' => false, 'message' => 'Invalid stall or password.');
    }

    $_SESSION['hawker_id'] = intval($row['h_id']);
    $_SESSION['hawker_rs_id'] = intval($row['rs_id']);

    return array('success' => true, 'message' => 'Login successful.');
}

function hawkergo_hawker_logout()
{
    unset($_SESSION['hawker_id'], $_SESSION['hawker_rs_id']);
}

function hawkergo_hawker_order_belongs_to_stall($db, $o_id, $rs_id)
{
    $o_id = intval($o_id);
    $rs_id = intval($rs_id);
    $stmt = $db->prepare('SELECT o_id FROM users_orders WHERE o_id = ? AND rs_id = ? LIMIT 1');
    $stmt->bind_param('ii', $o_id, $rs_id);
    $stmt->execute();
    $ok = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $ok;
}

function hawkergo_hawker_dish_belongs_to_stall($db, $d_id, $rs_id)
{
    $d_id = intval($d_id);
    $rs_id = intval($rs_id);
    $stmt = $db->prepare('SELECT d_id FROM dishes WHERE d_id = ? AND rs_id = ? LIMIT 1');
    $stmt->bind_param('ii', $d_id, $rs_id);
    $stmt->execute();
    $ok = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $ok;
}

function hawkergo_hawker_status_badge($status)
{
    $status = strtolower(trim($status));
    if ($status === 'closed') {
        return '<span class="hg-badge hg-badge--success">Completed</span>';
    }
    if ($status === 'rejected') {
        return '<span class="hg-badge hg-badge--danger">Cancelled</span>';
    }
    return '<span class="hg-badge hg-badge--warn">Preparing</span>';
}

function hawkergo_hawker_issue_status_badge($status)
{
    $status = strtolower(trim($status));
    if ($status === 'resolved') {
        return '<span class="hg-badge hg-badge--success">Resolved</span>';
    }
    if ($status === 'reviewing') {
        return '<span class="hg-badge hg-badge--warn">Reviewing</span>';
    }
    return '<span class="hg-badge hg-badge--danger">Open</span>';
}

function hawkergo_hawker_stall_name_colors()
{
    return array('#f5b800', '#ff8c00', '#e0a600');
}

function hawkergo_render_animated_stall_name($name, $options = array())
{
    $name = trim((string) $name);
    if ($name === '') {
        return '';
    }

    $size = isset($options['size']) ? $options['size'] : 'md';
    $tag = isset($options['tag']) ? $options['tag'] : 'span';
    $extra_class = isset($options['class']) ? $options['class'] : '';
    $colors = hawkergo_hawker_stall_name_colors();

    $allowed_tags = array('span', 'strong', 'div', 'h1', 'h2', 'h3');
    if (!in_array($tag, $allowed_tags, true)) {
        $tag = 'span';
    }

    $class = 'hg-hawker-stall-name-animated hg-hawker-stall-name-animated--' . htmlspecialchars($size, ENT_QUOTES, 'UTF-8');
    if ($extra_class !== '') {
        $class .= ' ' . htmlspecialchars($extra_class, ENT_QUOTES, 'UTF-8');
    }

    $html = '<' . $tag . ' class="' . $class . '" aria-label="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '">';
    $chars = preg_split('//u', $name, -1, PREG_SPLIT_NO_EMPTY);
    $index = 0;

    foreach ($chars as $char) {
        if ($char === ' ') {
            $html .= '<span class="hg-hawker-stall-letter hg-hawker-stall-letter--space">&nbsp;</span>';
            continue;
        }

        $color = $colors[$index % count($colors)];
        $html .= '<span class="hg-hawker-stall-letter" style="--i:' . $index . ';--c:' . $color . ';">'
            . htmlspecialchars($char, ENT_QUOTES, 'UTF-8') . '</span>';
        $index++;
    }

    $html .= '</' . $tag . '>';
    return $html;
}
