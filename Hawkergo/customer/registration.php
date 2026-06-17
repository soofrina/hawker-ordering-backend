<?php
session_start();
ini_set('display_errors', 0);
error_reporting(0);
include(__DIR__ . '/../connection/connect.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: create_account.php');
    exit;
}

function hawkergo_register_redirect_error($message, $old = array())
{
    $_SESSION['register_error'] = $message;
    if (!empty($old)) {
        $_SESSION['register_old'] = $old;
    }
    header('Location: create_account.php');
    exit;
}

$old = array(
    'username' => isset($_POST['username']) ? trim($_POST['username']) : '',
    'firstname' => isset($_POST['firstname']) ? trim($_POST['firstname']) : '',
    'lastname' => isset($_POST['lastname']) ? trim($_POST['lastname']) : '',
    'email' => isset($_POST['email']) ? trim($_POST['email']) : '',
    'phone' => isset($_POST['phone']) ? trim($_POST['phone']) : ''
);

$message = '';
$required = array('username', 'firstname', 'lastname', 'email', 'phone', 'password', 'cpassword');
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        hawkergo_register_redirect_error('All fields are required.', $old);
    }
}

if ($_POST['password'] !== $_POST['cpassword']) {
    hawkergo_register_redirect_error('Passwords do not match.', $old);
}

if (strlen($_POST['password']) < 6) {
    hawkergo_register_redirect_error('Password must be at least 6 characters.', $old);
}

if (strlen($_POST['phone']) < 8) {
    hawkergo_register_redirect_error('Please enter a valid phone number.', $old);
}

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    hawkergo_register_redirect_error('Please enter a valid email address.', $old);
}

$username = trim($_POST['username']);
$email = trim($_POST['email']);

$stmt = $db->prepare('SELECT username FROM users WHERE username = ? LIMIT 1');
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    hawkergo_register_redirect_error('Username already exists.', $old);
}
$stmt->close();

$stmt = $db->prepare('SELECT email FROM users WHERE email = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    hawkergo_register_redirect_error('Email already exists.', $old);
}
$stmt->close();

$firstname = trim($_POST['firstname']);
$lastname = trim($_POST['lastname']);
$phone = trim($_POST['phone']);
$hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$stmt = $db->prepare('INSERT INTO users(username,f_name,l_name,email,phone,password) VALUES (?,?,?,?,?,?)');
if (!$stmt) {
    hawkergo_register_redirect_error('Registration failed. Please try again.', $old);
}

$stmt->bind_param('ssssss', $username, $firstname, $lastname, $email, $phone, $hashed_password);
if ($stmt->execute()) {
    $stmt->close();
    $_SESSION['success'] = 'Registration successful! Please login.';
    header('Location: login.php');
    exit;
}

$stmt->close();
hawkergo_register_redirect_error('Registration failed. Please try again.', $old);
