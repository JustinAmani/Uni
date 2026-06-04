<?php
require_once __DIR__ . '/../includes/auth.php';

startSession();

$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $email = strtolower(trim($_POST['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            $db   = getDB();
            $stmt = $db->prepare('SELECT id FROM users WHERE email = ? AND role = "applicant"');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Always show success to prevent email enumeration
            if ($user) {
                $token   = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $db->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?')
                   ->execute([$token, $expires, $user['id']]);
                // In production: send email with reset link
                // mail($email, 'Password Reset', APP_URL . '/auth/reset_password.php?token=' . $token);
            }
            $message = 'If that email exists in our system, a reset link has been sent.';
        }
    }
}

$pageTitle = 'Forgot Password';
?>
<!DOCTYPE html>
<html lang="en">
<head><?php require_once __DIR__ . '/../includes/head.php'; ?></head>
<body class="landing-page d-flex flex-column min-vh-100">

<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<main class="container my-5 flex-grow-1">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow border-0 rounded-3">
                <div class="card-header udm-card-header py-3 text-center">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-key me-1"></i> Reset Password
                    </h6>
                </div>
                <div class="card-body p-4">
                    <?php if ($message): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-1"></i> <?= e($message) ?>
                        </div>
                        <div class="text-center">
                            <a href="<?= APP_URL ?>/landing_page/index.php" class="btn btn-udm-primary">
                                Back to Login
                            </a>
                        </div>
                    <?php else: ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger py-2"><?= e($error) ?></div>
                        <?php endif; ?>
                        <p class="text-muted small mb-3">
                            Enter your registered email address and we will send you a password reset link.
                        </p>
                        <form method="POST" novalidate>
                            <input type="hidden" name="csrf_token"
                                   value="<?= e($_SESSION['csrf_token'] ??= bin2hex(random_bytes(32))) ?>">
                            <div class="mb-3">
                                <label for="resetEmail" class="form-label fw-medium">
                                    Email Address
                                </label>
                                <input type="email" id="resetEmail" name="email"
                                       class="form-control" required autocomplete="email">
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-udm-primary">
                                    <i class="bi bi-send me-1"></i> Send Reset Link
                                </button>
                            </div>
                        </form>
                        <div class="text-center mt-3">
                            <a href="<?= APP_URL ?>/landing_page/index.php" class="text-muted small">
                                <i class="bi bi-arrow-left me-1"></i> Back to Login
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="udm-footer mt-auto">
    <div class="container text-center py-3">
        <p class="mb-0 small">
            &copy; <?= date('Y') ?> Université des Mascareignes
        </p>
    </div>
</footer>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
