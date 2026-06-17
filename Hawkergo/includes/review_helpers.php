<?php

function hawkergo_ensure_review_tables($db)
{
    if (!hawkergo_table_exists($db, 'dish_reviews')) {
        mysqli_query(
            $db,
            "CREATE TABLE `dish_reviews` (
                `review_id` INT(11) NOT NULL AUTO_INCREMENT,
                `u_id` INT(11) NOT NULL,
                `d_id` INT(11) NOT NULL,
                `o_id` INT(11) NOT NULL,
                `rs_id` INT(11) NOT NULL,
                `rating` TINYINT(1) NOT NULL,
                `comment` TEXT DEFAULT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`review_id`),
                UNIQUE KEY `uniq_dish_order_review` (`u_id`, `o_id`),
                KEY `idx_dish_reviews_d_id` (`d_id`),
                KEY `idx_dish_reviews_rs_id` (`rs_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    if (!hawkergo_table_exists($db, 'stall_reviews')) {
        mysqli_query(
            $db,
            "CREATE TABLE `stall_reviews` (
                `review_id` INT(11) NOT NULL AUTO_INCREMENT,
                `u_id` INT(11) NOT NULL,
                `rs_id` INT(11) NOT NULL,
                `order_batch_id` VARCHAR(32) NOT NULL,
                `rating` TINYINT(1) NOT NULL,
                `comment` TEXT DEFAULT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`review_id`),
                UNIQUE KEY `uniq_stall_batch_review` (`u_id`, `rs_id`, `order_batch_id`),
                KEY `idx_stall_reviews_rs_id` (`rs_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }
}

function hawkergo_resolve_order_d_id($db, $order_row)
{
    if (!empty($order_row['d_id'])) {
        return intval($order_row['d_id']);
    }
    $rs_id = intval($order_row['rs_id']);
    $title = isset($order_row['title']) ? $order_row['title'] : '';
    if ($rs_id <= 0 || $title === '') {
        return 0;
    }
    $stmt = $db->prepare('SELECT d_id FROM dishes WHERE rs_id = ? AND title = ? LIMIT 1');
    $stmt->bind_param('is', $rs_id, $title);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? intval($row['d_id']) : 0;
}

function hawkergo_order_is_reviewable($status)
{
    return $status === 'closed';
}

function hawkergo_get_user_order($db, $user_id, $o_id)
{
    $stmt = $db->prepare(
        'SELECT o.*, r.title AS stall_name
         FROM users_orders o
         LEFT JOIN hawkerstalls r ON r.rs_id = o.rs_id
         WHERE o.o_id = ? AND o.u_id = ? LIMIT 1'
    );
    $stmt->bind_param('ii', $o_id, $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row;
}

function hawkergo_user_has_dish_review($db, $user_id, $o_id)
{
    $stmt = $db->prepare('SELECT review_id FROM dish_reviews WHERE u_id = ? AND o_id = ? LIMIT 1');
    $stmt->bind_param('ii', $user_id, $o_id);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $exists;
}

function hawkergo_user_has_stall_review($db, $user_id, $rs_id, $order_batch_id)
{
    if ($order_batch_id === '' || $order_batch_id === null) {
        return false;
    }
    $stmt = $db->prepare(
        'SELECT review_id FROM stall_reviews WHERE u_id = ? AND rs_id = ? AND order_batch_id = ? LIMIT 1'
    );
    $stmt->bind_param('iis', $user_id, $rs_id, $order_batch_id);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $exists;
}

function hawkergo_submit_dish_review($db, $user_id, $o_id, $rating, $comment)
{
    $rating = intval($rating);
    if ($rating < 1 || $rating > 5) {
        return array('success' => false, 'message' => 'Please choose a rating from 1 to 5 stars.');
    }

    $order = hawkergo_get_user_order($db, $user_id, $o_id);
    if (!$order) {
        return array('success' => false, 'message' => 'Order not found.');
    }
    if (!hawkergo_order_is_reviewable($order['status'])) {
        return array('success' => false, 'message' => 'You can review after your order is collected.');
    }
    if (hawkergo_user_has_dish_review($db, $user_id, $o_id)) {
        return array('success' => false, 'message' => 'You already reviewed this dish for this order.');
    }

    $d_id = hawkergo_resolve_order_d_id($db, $order);
    if ($d_id <= 0) {
        return array('success' => false, 'message' => 'Unable to match this order to a menu dish.');
    }

    $rs_id = intval($order['rs_id']);
    $comment = trim($comment);

    $stmt = $db->prepare(
        'INSERT INTO dish_reviews (u_id, d_id, o_id, rs_id, rating, comment) VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('iiiiis', $user_id, $d_id, $o_id, $rs_id, $rating, $comment);
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        return array('success' => false, 'message' => 'Could not save review: ' . $err);
    }
    $stmt->close();

    return array('success' => true, 'message' => 'Thank you! Your dish review was saved.');
}

function hawkergo_submit_stall_review($db, $user_id, $o_id, $rating, $comment)
{
    $rating = intval($rating);
    if ($rating < 1 || $rating > 5) {
        return array('success' => false, 'message' => 'Please choose a rating from 1 to 5 stars.');
    }

    $order = hawkergo_get_user_order($db, $user_id, $o_id);
    if (!$order) {
        return array('success' => false, 'message' => 'Order not found.');
    }
    if (!hawkergo_order_is_reviewable($order['status'])) {
        return array('success' => false, 'message' => 'You can review after your order is collected.');
    }

    $rs_id = intval($order['rs_id']);
    $order_batch_id = isset($order['order_batch_id']) ? trim($order['order_batch_id']) : '';
    if ($rs_id <= 0 || $order_batch_id === '') {
        return array('success' => false, 'message' => 'Unable to review this stall for this order.');
    }
    if (hawkergo_user_has_stall_review($db, $user_id, $rs_id, $order_batch_id)) {
        return array('success' => false, 'message' => 'You already reviewed this hawker stall for this order.');
    }

    $comment = trim($comment);
    $stmt = $db->prepare(
        'INSERT INTO stall_reviews (u_id, rs_id, order_batch_id, rating, comment) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->bind_param('iisis', $user_id, $rs_id, $order_batch_id, $rating, $comment);
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        return array('success' => false, 'message' => 'Could not save review: ' . $err);
    }
    $stmt->close();

    return array('success' => true, 'message' => 'Thank you! Your stall review was saved.');
}

function hawkergo_get_dish_rating_summary($db, $d_id)
{
    $d_id = intval($d_id);
    $stmt = $db->prepare(
        'SELECT COUNT(*) AS review_count, COALESCE(AVG(rating), 0) AS avg_rating
         FROM dish_reviews WHERE d_id = ?'
    );
    $stmt->bind_param('i', $d_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return array(
        'count' => intval($row['review_count']),
        'avg' => round(floatval($row['avg_rating']), 1)
    );
}

function hawkergo_get_stall_rating_summary($db, $rs_id)
{
    $rs_id = intval($rs_id);
    $stmt = $db->prepare(
        'SELECT COUNT(*) AS review_count, COALESCE(AVG(rating), 0) AS avg_rating
         FROM stall_reviews WHERE rs_id = ?'
    );
    $stmt->bind_param('i', $rs_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return array(
        'count' => intval($row['review_count']),
        'avg' => round(floatval($row['avg_rating']), 1)
    );
}

function hawkergo_get_dish_reviews($db, $d_id, $limit = 10)
{
    $d_id = intval($d_id);
    $limit = max(1, min(50, intval($limit)));
    $stmt = $db->prepare(
        "SELECT dr.rating, dr.comment, dr.created_at, u.username
         FROM dish_reviews dr
         LEFT JOIN users u ON u.u_id = dr.u_id
         WHERE dr.d_id = ?
         ORDER BY dr.created_at DESC
         LIMIT $limit"
    );
    $stmt->bind_param('i', $d_id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function hawkergo_get_stall_reviews($db, $rs_id, $limit = 10)
{
    $rs_id = intval($rs_id);
    $limit = max(1, min(50, intval($limit)));
    $stmt = $db->prepare(
        "SELECT sr.rating, sr.comment, sr.created_at, u.username
         FROM stall_reviews sr
         LEFT JOIN users u ON u.u_id = sr.u_id
         WHERE sr.rs_id = ?
         ORDER BY sr.created_at DESC
         LIMIT $limit"
    );
    $stmt->bind_param('i', $rs_id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function hawkergo_get_stall_dish_reviews($db, $rs_id, $limit = 10)
{
    $rs_id = intval($rs_id);
    $limit = max(1, min(50, intval($limit)));
    $stmt = $db->prepare(
        "SELECT dr.rating, dr.comment, dr.created_at, u.username, d.title AS dish_title
         FROM dish_reviews dr
         LEFT JOIN users u ON u.u_id = dr.u_id
         LEFT JOIN dishes d ON d.d_id = dr.d_id
         WHERE dr.rs_id = ?
         ORDER BY dr.created_at DESC
         LIMIT $limit"
    );
    $stmt->bind_param('i', $rs_id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function hawkergo_render_star_rating($avg, $count = null, $css_class = 'hg-stars')
{
    $avg = floatval($avg);
    $full = (int) floor($avg);
    $half = ($avg - $full) >= 0.5;
    $html = '<span class="' . htmlspecialchars($css_class) . '" aria-label="' . $avg . ' out of 5 stars">';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $full) {
            $html .= '<i class="fa fa-star"></i>';
        } elseif ($half && $i === $full + 1) {
            $html .= '<i class="fa fa-star-half-o"></i>';
        } else {
            $html .= '<i class="fa fa-star-o"></i>';
        }
    }
    $html .= '</span>';
    if ($count !== null) {
        $html .= ' <span class="hg-stars-count">(' . intval($count) . ')</span>';
    }
    return $html;
}
