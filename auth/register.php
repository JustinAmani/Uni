<?php
require_once __DIR__ . '/../includes/auth.php';

startSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/landing_page/index.php?tab=register');
    exit;
}

// CSRF check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    $_SESSION['auth_error'] = 'Invalid request. Please try again.';
    header('Location: ' . APP_URL . '/landing_page/index.php?tab=register');
    exit;
}

$errors = [];

$firstName = trim($_POST['first_name'] ?? '');
$lastName  = trim($_POST['last_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$password  = $_POST['password'] ?? '';
$confirm   = $_POST['password_confirm'] ?? '';
$terms     = $_POST['terms'] ?? '';

if (empty($firstName) || !preg_match("/^[A-Za-z\s\-']{1,100}$/", $firstName)) {
    $errors[] = 'Please enter a valid first name (letters only).';
}
if (empty($lastName) || !preg_match("/^[A-Za-z\s\-']{1,100}$/", $lastName)) {
    $errors[] = 'Please enter a valid last name (letters only).';
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}
if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
}
if ($password !== $confirm) {
    $errors[] = 'Passwords do not match.';
}
if (empty($terms)) {
    $errors[] = 'You must accept the Terms & Conditions.';
}

if ($errors) {
    $_SESSION['auth_error'] = implode(' ', $errors);
    header('Location: ' . APP_URL . '/landing_page/index.php?tab=register');
    exit;
}

$result = registerUser([
    'email'      => $email,
    'password'   => $password,
    'first_name' => $firstName,
    'last_name'  => $lastName,
]);

if (!$result['success']) {
    $_SESSION['auth_error'] = $result['message'];
    header('Location: ' . APP_URL . '/landing_page/index.php?tab=register');
    exit;
}

// Auto-login after registration
$login = loginUser($email, $password);
if ($login['success']) {
    header('Location: ' . APP_URL . '/application_form/index.php');
} else {
    $_SESSION['auth_success'] = 'Account created successfully. Please log in.';
    header('Location: ' . APP_URL . '/landing_page/index.php');
}
exit;
