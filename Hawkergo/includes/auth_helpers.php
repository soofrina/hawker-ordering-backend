<?php

function hawkergo_generate_reset_token()
{
    return bin2hex(random_bytes(32));
}

function hawkergo_get_stalls_for_hawker_registration($db)
{
    $stalls = array();
    $sql = "SELECT hs.rs_id, hs.title, hs.address, hs.image,
                   CASE WHEN hu.h_id IS NULL THEN 0 ELSE 1 END AS taken
            FROM hawkerstalls hs
            LEFT JOIN hawker_users hu ON hu.rs_id = hs.rs_id
            ORDER BY hs.title";
    $res = mysqli_query($db, $sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $stalls[] = $row;
        }
    }
    return $stalls;
}

function hawkergo_hawker_register($db, $rs_id, $username, $email, $password)
{
    $rs_id = intval($rs_id);
    $username = trim($username);
    $email = trim($email);

    if ($rs_id <= 0 || $username === '' || $password === '') {
        return array('success' => false, 'message' => 'Stall, username, and password are required.');
    }
    if (strlen($password) < 6) {
        return array('success' => false, 'message' => 'Password must be at least 6 characters.');
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return array('success' => false, 'message' => 'Invalid email address.');
    }

    $checkStall = $db->prepare('SELECT rs_id FROM hawkerstalls WHERE rs_id = ? LIMIT 1');
    $checkStall->bind_param('i', $rs_id);
    $checkStall->execute();
    if ($checkStall->get_result()->num_rows === 0) {
        $checkStall->close();
        return array('success' => false, 'message' => 'Selected stall was not found.');
    }
    $checkStall->close();

    $checkTaken = $db->prepare('SELECT h_id FROM hawker_users WHERE rs_id = ? LIMIT 1');
    $checkTaken->bind_param('i', $rs_id);
    $checkTaken->execute();
    if ($checkTaken->get_result()->num_rows > 0) {
        $checkTaken->close();
        return array('success' => false, 'message' => 'This stall already has a portal account. Use forgot password or contact admin.');
    }
    $checkTaken->close();

    $checkUser = $db->prepare('SELECT h_id FROM hawker_users WHERE username = ? LIMIT 1');
    $checkUser->bind_param('s', $username);
    $checkUser->execute();
    if ($checkUser->get_result()->num_rows > 0) {
        $checkUser->close();
        return array('success' => false, 'message' => 'Username is already taken.');
    }
    $checkUser->close();

    if ($email !== '') {
        $checkEmail = $db->prepare('SELECT h_id FROM hawker_users WHERE email = ? LIMIT 1');
        $checkEmail->bind_param('s', $email);
        $checkEmail->execute();
        if ($checkEmail->get_result()->num_rows > 0) {
            $checkEmail->close();
            return array('success' => false, 'message' => 'Email is already registered.');
        }
        $checkEmail->close();
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare('INSERT INTO hawker_users (rs_id, username, password, email) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('isss', $rs_id, $username, $hash, $email);
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        return array('success' => false, 'message' => 'Registration failed: ' . $err);
    }
    $stmt->close();

    return array('success' => true, 'message' => 'Hawker account created. You can now sign in.');
}

function hawkergo_admin_request_password_reset($db, $email)
{
    $email = trim($email);
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return array('success' => false, 'message' => 'Please enter a valid email address.');
    }

    $stmt = $db->prepare('SELECT adm_id FROM admin WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        return array('success' => false, 'message' => 'No admin account found with that email.');
    }

    $token = hawkergo_generate_reset_token();
    $expires = date('Y-m-d H:i:s', time() + 3600);

    $upd = $db->prepare('UPDATE admin SET code = ?, reset_expires = ? WHERE adm_id = ?');
    $adm_id = intval($row['adm_id']);
    $upd->bind_param('ssi', $token, $expires, $adm_id);
    $upd->execute();
    $upd->close();

    return array(
        'success' => true,
        'message' => 'Password reset link generated.',
        'email' => $email,
        'token' => $token,
    );
}

function hawkergo_admin_reset_password($db, $email, $token, $password)
{
    $email = trim($email);
    $token = trim($token);

    if ($email === '' || $token === '' || $password === '') {
        return array('success' => false, 'message' => 'All fields are required.');
    }
    if (strlen($password) < 6) {
        return array('success' => false, 'message' => 'Password must be at least 6 characters.');
    }

    $stmt = $db->prepare('SELECT adm_id, code, reset_expires FROM admin WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || $row['code'] === '' || !hash_equals($row['code'], $token)) {
        return array('success' => false, 'message' => 'Invalid or expired reset link.');
    }
    if (!empty($row['reset_expires']) && strtotime($row['reset_expires']) < time()) {
        return array('success' => false, 'message' => 'Reset link has expired. Request a new one.');
    }

    $hash = md5($password);
    $adm_id = intval($row['adm_id']);
    $clear = '';
    $upd = $db->prepare('UPDATE admin SET password = ?, code = ?, reset_expires = NULL WHERE adm_id = ?');
    $upd->bind_param('ssi', $hash, $clear, $adm_id);
    $upd->execute();
    $upd->close();

    return array('success' => true, 'message' => 'Password updated. You can now sign in.');
}

function hawkergo_hawker_request_password_reset($db, $rs_id)
{
    $rs_id = intval($rs_id);
    if ($rs_id <= 0) {
        return array('success' => false, 'message' => 'Please select your hawker stall.');
    }

    $stmt = $db->prepare(
        'SELECT hu.h_id, hu.email, hs.title AS stall_name
         FROM hawker_users hu
         INNER JOIN hawkerstalls hs ON hs.rs_id = hu.rs_id
         WHERE hu.rs_id = ? LIMIT 1'
    );
    $stmt->bind_param('i', $rs_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        return array('success' => false, 'message' => 'No portal account found for that stall.');
    }

    $token = hawkergo_generate_reset_token();
    $expires = date('Y-m-d H:i:s', time() + 3600);
    $h_id = intval($row['h_id']);

    $upd = $db->prepare('UPDATE hawker_users SET reset_token = ?, reset_expires = ? WHERE h_id = ?');
    $upd->bind_param('ssi', $token, $expires, $h_id);
    $upd->execute();
    $upd->close();

    return array(
        'success' => true,
        'message' => 'Password reset link generated for ' . $row['stall_name'] . '.',
        'token' => $token,
        'stall_name' => $row['stall_name'],
    );
}

function hawkergo_hawker_reset_password($db, $token, $password)
{
    $token = trim($token);
    if ($token === '' || $password === '') {
        return array('success' => false, 'message' => 'Password is required.');
    }
    if (strlen($password) < 6) {
        return array('success' => false, 'message' => 'Password must be at least 6 characters.');
    }

    $stmt = $db->prepare('SELECT h_id, reset_expires FROM hawker_users WHERE reset_token = ? LIMIT 1');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        return array('success' => false, 'message' => 'Invalid or expired reset link.');
    }
    if (!empty($row['reset_expires']) && strtotime($row['reset_expires']) < time()) {
        return array('success' => false, 'message' => 'Reset link has expired. Request a new one.');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $h_id = intval($row['h_id']);
    $clear = null;

    $upd = $db->prepare('UPDATE hawker_users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE h_id = ?');
    $upd->bind_param('si', $hash, $h_id);
    $upd->execute();
    $upd->close();

    return array('success' => true, 'message' => 'Password updated. You can now sign in.');
}

function hawkergo_customer_request_password_reset($db, $identifier)
{
    $identifier = trim($identifier);
    if ($identifier === '') {
        return array('success' => false, 'message' => 'Enter your username or email.');
    }

    if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
        $stmt = $db->prepare('SELECT u_id, username, email FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $identifier);
    } else {
        $stmt = $db->prepare('SELECT u_id, username, email FROM users WHERE username = ? LIMIT 1');
        $stmt->bind_param('s', $identifier);
    }
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        return array('success' => false, 'message' => 'No account found with that username or email.');
    }

    $token = hawkergo_generate_reset_token();
    $expires = date('Y-m-d H:i:s', time() + 3600);
    $u_id = intval($row['u_id']);

    $upd = $db->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE u_id = ?');
    $upd->bind_param('ssi', $token, $expires, $u_id);
    $upd->execute();
    $upd->close();

    return array(
        'success' => true,
        'message' => 'Password reset link generated for ' . $row['username'] . '.',
        'token' => $token,
        'username' => $row['username'],
    );
}

function hawkergo_customer_reset_password($db, $token, $password)
{
    $token = trim($token);
    if ($token === '' || $password === '') {
        return array('success' => false, 'message' => 'Password is required.');
    }
    if (strlen($password) < 6) {
        return array('success' => false, 'message' => 'Password must be at least 6 characters.');
    }

    $stmt = $db->prepare('SELECT u_id, reset_expires FROM users WHERE reset_token = ? LIMIT 1');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        return array('success' => false, 'message' => 'Invalid or expired reset link.');
    }
    if (!empty($row['reset_expires']) && strtotime($row['reset_expires']) < time()) {
        return array('success' => false, 'message' => 'Reset link has expired. Request a new one.');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $u_id = intval($row['u_id']);

    $upd = $db->prepare('UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE u_id = ?');
    $upd->bind_param('si', $hash, $u_id);
    $upd->execute();
    $upd->close();

    return array('success' => true, 'message' => 'Password updated. You can now sign in.');
}
