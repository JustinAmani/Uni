<?php
require_once __DIR__ . '/../includes/auth.php';

startSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/landing_page/index.php');
    exit;
}

// CSRF check
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    $_SESSION['auth_error'] = 'Invalid request. Please try again.';
    header('Location: ' . APP_URL . '/landing_page/index.php');
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role     = $_POST['role'] ?? 'applicant';

if (empty($email) || empty($password)) {
    $_SESSION['auth_error'] = 'Email and password are required.';
    header('Location: ' . APP_URL . '/landing_page/index.php' . ($role === 'agent' ? '?tab=agent' : ''));
    exit;
}

$result = loginUser($email, $password);

if (!$result['success']) {
    $_SESSION['auth_error'] = $result['message'];
    header('Location: ' . APP_URL . '/landing_page/index.php' . ($role === 'agent' ? '?tab=agent' : ''));
    exit;
}

// Validate role access
$userRole = $result['role'];
if ($role === 'agent' && !in_array($userRole, ['agent', 'admin'])) {
    logoutUser();
    $_SESSION['auth_error'] = 'You do not have agent access.';
    header('Location: ' . APP_URL . '/landing_page/index.php?tab=agent');
    exit;
}
if ($role === 'applicant' && !in_array($userRole, ['applicant'])) {
    logoutUser();
    $_SESSION['auth_error'] = 'Please use the agent login.';
    header('Location: ' . APP_URL . '/landing_page/index.php?tab=agent');
    exit;
}

if (in_array($userRole, ['agent', 'admin'])) {
    header('Location: ' . APP_URL . '/agent/index.php');
} else {
    header('Location: ' . APP_URL . '/application_form/index.php');
}
exit;
