<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
        session_set_cookie_params(SESSION_LIFETIME);
        session_start();
    }
}

function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin(string $role = ''): void {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/landing_page/index.php');
        exit;
    }
    if ($role && $_SESSION['user_role'] !== $role && $_SESSION['user_role'] !== 'admin') {
        header('Location: ' . APP_URL . '/landing_page/index.php');
        exit;
    }
}

function loginUser(string $email, string $password): array {
    $db = getDB();
    $stmt = $db->prepare('SELECT id, email, password, role, first_name, last_name, is_active FROM users WHERE email = ?');
    $stmt->execute([strtolower(trim($email))]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }
    if (!$user['is_active']) {
        return ['success' => false, 'message' => 'Your account has been deactivated.'];
    }
    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    startSession();
    session_regenerate_id(true);
    $_SESSION['user_id']        = $user['id'];
    $_SESSION['user_email']     = $user['email'];
    $_SESSION['user_role']      = $user['role'];
    $_SESSION['user_name']      = trim($user['first_name'] . ' ' . $user['last_name']);

    return ['success' => true, 'role' => $user['role']];
}

function registerUser(array $data): array {
    $db = getDB();

    $email = strtolower(trim($data['email']));
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'An account with this email already exists.'];
    }

    $hash = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $db->prepare(
        'INSERT INTO users (email, password, role, first_name, last_name) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $email,
        $hash,
        'applicant',
        ucfirst(strtolower(trim($data['first_name']))),
        ucfirst(strtolower(trim($data['last_name']))),
    ]);

    return ['success' => true, 'user_id' => $db->lastInsertId()];
}

function logoutUser(): void {
    startSession();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

function getCurrentUserId(): ?int {
    startSession();
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUserName(): string {
    startSession();
    return $_SESSION['user_name'] ?? '';
}

function getCurrentUserRole(): string {
    startSession();
    return $_SESSION['user_role'] ?? '';
}

function sanitize(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function old(string $key, string $default = ''): string {
    return e($_SESSION['form_data'][$key] ?? $default);
}
