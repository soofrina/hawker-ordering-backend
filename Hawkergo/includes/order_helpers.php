<?php

function hawkergo_generate_verification_code()
{
    return strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
}

function hawkergo_normalize_phone($phone)
{
    return preg_replace('/\D/', '', trim($phone));
}

function hawkergo_phones_match($phone_a, $phone_b)
{
    $a = hawkergo_normalize_phone($phone_a);
    $b = hawkergo_normalize_phone($phone_b);
    if ($a === '' || $b === '') {
        return false;
    }
    if ($a === $b) {
        return true;
    }
    if (strlen($a) >= 8 && strlen($b) >= 8) {
        return substr($a, -8) === substr($b, -8);
    }
    return false;
}

function hawkergo_find_orders_by_phone_and_code($db, $phone, $code)
{
    $code = strtoupper(trim($code));
    $orders = array();

    if ($code === '' || hawkergo_normalize_phone($phone) === '') {
        return $orders;
    }

    $stmt = $db->prepare(
        "SELECT o.*, u.phone AS user_phone, r.title AS stall_name
         FROM users_orders o
         LEFT JOIN users u ON o.u_id = u.u_id
         LEFT JOIN hawkerstalls r ON o.rs_id = r.rs_id
         WHERE UPPER(o.verification_code) = ?
         ORDER BY o.date DESC"
    );
    $stmt->bind_param('s', $code);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $order_phone = !empty($row['guest_phone']) ? $row['guest_phone'] : $row['user_phone'];
        if (hawkergo_phones_match($phone, $order_phone)) {
            $orders[] = $row;
        }
    }
    $stmt->close();

    return $orders;
}

function hawkergo_get_stall_title($db, $rs_id)
{
    $rs_id = intval($rs_id);
    if ($rs_id <= 0) {
        return '-';
    }
    $stmt = $db->prepare("SELECT title FROM hawkerstalls WHERE rs_id = ? LIMIT 1");
    $stmt->bind_param('i', $rs_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? $row['title'] : '-';
}

function hawkergo_resolve_item_rs_id($db, $item)
{
    if (!empty($item['rs_id'])) {
        return intval($item['rs_id']);
    }
    if (empty($item['d_id'])) {
        return 0;
    }
    $d_id = intval($item['d_id']);
    $stmt = $db->prepare("SELECT rs_id FROM dishes WHERE d_id = ? LIMIT 1");
    $stmt->bind_param('i', $d_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? intval($row['rs_id']) : 0;
}

function hawkergo_cart_stall_names($db, $cart_items)
{
    $names = array();
    foreach ($cart_items as $key => $item) {
        $rs_id = hawkergo_resolve_item_rs_id($db, $item);
        if ($rs_id > 0 && !isset($names[$rs_id])) {
            $names[$rs_id] = hawkergo_get_stall_title($db, $rs_id);
        }
        if ($rs_id > 0 && empty($item['rs_id']) && isset($_SESSION['cart_item'][$key])) {
            $_SESSION['cart_item'][$key]['rs_id'] = $rs_id;
        }
    }
    return $names;
}

function hawkergo_apply_cart_quantities($qty_map)
{
    if (empty($qty_map) || !is_array($qty_map) || empty($_SESSION['cart_item'])) {
        return;
    }
    foreach ($qty_map as $d_id => $qty) {
        $d_id = intval($d_id);
        $qty = max(1, min(99, intval($qty)));
        if ($d_id > 0 && !empty($_SESSION['cart_item'][$d_id])) {
            $_SESSION['cart_item'][$d_id]['quantity'] = $qty;
        }
    }
}

function hawkergo_cart_items_by_stall($db, $cart_items)
{
    $grouped = array();
    foreach ($cart_items as $key => $item) {
        $rs_id = hawkergo_resolve_item_rs_id($db, $item);
        if (!isset($grouped[$rs_id])) {
            $grouped[$rs_id] = array(
                'rs_id' => $rs_id,
                'stall_name' => hawkergo_get_stall_title($db, $rs_id),
                'items' => array()
            );
        }
        $grouped[$rs_id]['items'][$key] = $item;
    }
    return $grouped;
}

function hawkergo_cart_items_for_stall($db, $cart_items, $rs_id)
{
    $rs_id = intval($rs_id);
    $items = array();
    foreach ($cart_items as $key => $item) {
        if (hawkergo_resolve_item_rs_id($db, $item) === $rs_id) {
            $items[$key] = $item;
        }
    }
    return $items;
}

function hawkergo_cart_total($cart_items)
{
    $total = 0;
    foreach ($cart_items as $item) {
        $total += floatval($item['price']) * intval($item['quantity']);
    }
    return $total;
}

function hawkergo_next_queue_number($db, $rs_id)
{
    $rs_id = intval($rs_id);
    $sql = "SELECT COALESCE(MAX(queue_number), 0) + 1 AS next_q
            FROM users_orders
            WHERE rs_id = ?
              AND queue_number IS NOT NULL
              AND DATE(COALESCE(ordered_at, `date`)) = CURDATE()";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('i', $rs_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return intval($row['next_q']);
}

function hawkergo_stall_accepts_orders($db, $rs_id)
{
    $rs_id = intval($rs_id);
    $stmt = $db->prepare("SELECT accepting_orders FROM hawkerstalls WHERE rs_id = ? LIMIT 1");
    $stmt->bind_param('i', $rs_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row && intval($row['accepting_orders']) === 1;
}

function hawkergo_dish_available($db, $d_id)
{
    $d_id = intval($d_id);
    $stmt = $db->prepare(
        "SELECT d.d_id, d.is_sold_out, r.accepting_orders
         FROM dishes d
         JOIN hawkerstalls r ON d.rs_id = r.rs_id
         WHERE d.d_id = ? LIMIT 1"
    );
    $stmt->bind_param('i', $d_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        return false;
    }
    return intval($row['is_sold_out']) === 0 && intval($row['accepting_orders']) === 1;
}

function hawkergo_place_cart_order($db, $cart_items, $options)
{
    if (empty($cart_items)) {
        return array('success' => false, 'message' => 'Your cart is empty.');
    }

    $uid = !empty($options['user_id']) ? intval($options['user_id']) : 0;
    $guest_name = isset($options['guest_name']) ? trim($options['guest_name']) : '';
    $guest_phone = isset($options['guest_phone']) ? trim($options['guest_phone']) : '';
    $special_request = isset($options['special_request']) ? trim($options['special_request']) : '';
    $payment_method = isset($options['payment_method']) ? trim($options['payment_method']) : 'COD';
    $order_type = isset($options['order_type']) ? trim($options['order_type']) : 'takeaway';

    if ($uid === 0) {
        if ($guest_phone === '' || strlen(hawkergo_normalize_phone($guest_phone)) < 8) {
            return array('success' => false, 'message' => 'Please enter a valid contact number.');
        }
        if ($guest_name === '') {
            $guest_name = 'Guest';
        }
        $guest_phone = trim($guest_phone);
    }

    $rs_id = 0;
    foreach ($cart_items as $item) {
        $item_rs_id = hawkergo_resolve_item_rs_id($db, $item);
        if ($item_rs_id === 0) {
            return array('success' => false, 'message' => 'Unable to identify hawker stall for one or more items.');
        }
        if (!hawkergo_stall_accepts_orders($db, $item_rs_id)) {
            $stall_title = hawkergo_get_stall_title($db, $item_rs_id);
            return array('success' => false, 'message' => $stall_title . ' is not accepting orders right now.');
        }
    }

    foreach ($cart_items as $item) {
        if (!hawkergo_dish_available($db, $item['d_id'])) {
            return array('success' => false, 'message' => 'One or more items are sold out or unavailable.');
        }
    }

    $verification_code = hawkergo_generate_verification_code();
    $order_batch_id = uniqid('ord_', true);
    $queues_by_stall = array();
    $primary_queue = 0;
    $primary_rs_id = 0;

    $stmt = $db->prepare(
        "INSERT INTO users_orders
        (u_id, rs_id, d_id, title, quantity, price, queue_number, verification_code, status,
         guest_phone, guest_name, special_request, order_batch_id, payment_method, order_type, ordered_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'in process', ?, ?, ?, ?, ?, ?, NOW())"
    );

    if (!$stmt) {
        return array('success' => false, 'message' => 'Unable to place order: ' . $db->error);
    }

    foreach ($cart_items as $item) {
        $item_rs_id = hawkergo_resolve_item_rs_id($db, $item);
        if (!isset($queues_by_stall[$item_rs_id])) {
            $queues_by_stall[$item_rs_id] = hawkergo_next_queue_number($db, $item_rs_id);
        }
        $queue_number = $queues_by_stall[$item_rs_id];
        if ($primary_queue === 0) {
            $primary_queue = $queue_number;
            $primary_rs_id = $item_rs_id;
        }

        $title = $item['title'];
        $quantity = intval($item['quantity']);
        $price = $item['price'];
        $d_id = intval($item['d_id']);
        $stmt->bind_param(
            'iiisidisssssss',
            $uid,
            $item_rs_id,
            $d_id,
            $title,
            $quantity,
            $price,
            $queue_number,
            $verification_code,
            $guest_phone,
            $guest_name,
            $special_request,
            $order_batch_id,
            $payment_method,
            $order_type
        );
        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            return array('success' => false, 'message' => 'Order failed: ' . $err);
        }
    }
    $stmt->close();

    return array(
        'success' => true,
        'queue_number' => $primary_queue,
        'verification_code' => $verification_code,
        'order_batch_id' => $order_batch_id,
        'rs_id' => $primary_rs_id,
        'queues_by_stall' => $queues_by_stall,
        'is_guest' => $uid === 0
    );
}
