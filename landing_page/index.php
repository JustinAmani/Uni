<?php
require_once __DIR__ . '/../includes/auth.php';

startSession();

if (isLoggedIn()) {
    $role = getCurrentUserRole();
    header('Location: ' . APP_URL . (in_array($role, ['agent','admin']) ? '/agent/index.php' : '/application_form/index.php'));
    exit;
}

$error   = $_SESSION['auth_error'] ?? '';
$success = $_SESSION['auth_success'] ?? '';
unset($_SESSION['auth_error'], $_SESSION['auth_success']);

$tab = $_GET['tab'] ?? 'login';
$pageTitle = 'Welcome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once __DIR__ . '/../includes/head.php'; ?>
</head>
<body class="landing-page d-flex flex-column min-vh-100">

<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<!-- Hero Banner -->
<div class="udm-hero py-4">
    <div class="container text-center">
        <h2 class="fw-bold mb-2">Welcome to the University Application Portal</h2>
        <p class="mb-3 opacity-90">
            Thank you for your interest in the Université des Mascareignes.<br>
            You can apply for the <strong>2025–2026 intake</strong> here.
        </p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <button class="btn btn-outline-light px-4 role-btn <?= $tab !== 'agent' ? 'active' : '' ?>"
                    data-panel="applicant-panel">
                <i class="bi bi-person-fill me-1"></i> I am an Applicant
            </button>
            <button class="btn btn-outline-light px-4 role-btn <?= $tab === 'agent' ? 'active' : '' ?>"
                    data-panel="agent-panel">
                <i class="bi bi-briefcase-fill me-1"></i> I am an Agent
            </button>
        </div>
    </div>
</div>

<!-- Auth Cards -->
<main class="container my-5 flex-grow-1" id="auth-section">
    <div class="row justify-content-center">

        <!-- Applicant panel -->
        <div class="col-md-5" id="applicant-panel"
             style="<?= $tab === 'agent' ? 'display:none' : '' ?>">
            <div class="card shadow border-0 rounded-3">
                <div class="card-header udm-card-header py-0">
                    <ul class="nav nav-tabs card-header-tabs border-0" id="authTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= $tab !== 'register' ? 'active' : '' ?>"
                                    data-bs-toggle="tab" data-bs-target="#loginTab" type="button">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Login
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= $tab === 'register' ? 'active' : '' ?>"
                                    data-bs-toggle="tab" data-bs-target="#registerTab" type="button">
                                <i class="bi bi-person-plus me-1"></i> Create Account
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show py-2 mb-3" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= e($error) ?>
                            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show py-2 mb-3" role="alert">
                            <i class="bi bi-check-circle-fill me-1"></i> <?= e($success) ?>
                            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="tab-content">
                        <!-- Login tab -->
                        <div class="tab-pane fade <?= $tab !== 'register' ? 'show active' : '' ?>"
                             id="loginTab" role="tabpanel">
                            <form method="POST" action="<?= APP_URL ?>/auth/login.php"
                                  novalidate id="loginForm">
                                <input type="hidden" name="csrf_token"
                                       value="<?= e($_SESSION['csrf_token'] ??= bin2hex(random_bytes(32))) ?>">
                                <input type="hidden" name="role" value="applicant">

                                <div class="mb-3">
                                    <label for="loginEmail" class="form-label fw-medium">
                                        Email Address
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-envelope text-muted"></i>
                                        </span>
                                        <input type="email" id="loginEmail" name="email"
                                               class="form-control" placeholder="your@email.com"
                                               required autocomplete="email">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="loginPassword" class="form-label fw-medium">
                                        Password
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-lock text-muted"></i>
                                        </span>
                                        <input type="password" id="loginPassword" name="password"
                                               class="form-control" placeholder="••••••••"
                                               required autocomplete="current-password">
                                        <button type="button" class="btn btn-outline-secondary toggle-pwd"
                                                data-target="loginPassword" aria-label="Toggle password visibility">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-udm-primary">
                                        <i class="bi bi-box-arrow-in-right me-1"></i> Login
                                    </button>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="<?= APP_URL ?>/auth/forgot_password.php"
                                       class="text-muted small">Forgot your password?</a>
                                </div>
                            </form>
                        </div>

                        <!-- Register tab -->
                        <div class="tab-pane fade <?= $tab === 'register' ? 'show active' : '' ?>"
                             id="registerTab" role="tabpanel">
                            <form method="POST" action="<?= APP_URL ?>/auth/register.php"
                                  novalidate id="registerForm">
                                <input type="hidden" name="csrf_token"
                                       value="<?= e($_SESSION['csrf_token'] ??= bin2hex(random_bytes(32))) ?>">

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label for="regFirst" class="form-label fw-medium">
                                            First Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="regFirst" name="first_name"
                                               class="form-control" required
                                               maxlength="100" pattern="[A-Za-z\s\-']+">
                                    </div>
                                    <div class="col-6">
                                        <label for="regLast" class="form-label fw-medium">
                                            Last Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="regLast" name="last_name"
                                               class="form-control" required
                                               maxlength="100" pattern="[A-Za-z\s\-']+">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="regEmail" class="form-label fw-medium">
                                        Email Address <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-envelope text-muted"></i>
                                        </span>
                                        <input type="email" id="regEmail" name="email"
                                               class="form-control" placeholder="your@email.com"
                                               required autocomplete="email">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="regPassword" class="form-label fw-medium">
                                        Password <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-lock text-muted"></i>
                                        </span>
                                        <input type="password" id="regPassword" name="password"
                                               class="form-control" placeholder="Min. 8 characters"
                                               required minlength="8" autocomplete="new-password">
                                        <button type="button" class="btn btn-outline-secondary toggle-pwd"
                                                data-target="regPassword" aria-label="Toggle password visibility">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div id="strengthBar" class="mt-1"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="regConfirm" class="form-label fw-medium">
                                        Confirm Password <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" id="regConfirm" name="password_confirm"
                                           class="form-control" placeholder="Repeat password"
                                           required autocomplete="new-password">
                                    <div class="invalid-feedback">Passwords do not match.</div>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" id="termsCheck" name="terms"
                                           class="form-check-input" required>
                                    <label class="form-check-label small" for="termsCheck">
                                        I agree to the
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">
                                            Terms &amp; Conditions
                                        </a>
                                    </label>
                                    <div class="invalid-feedback">You must accept the terms.</div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-udm-primary">
                                        <i class="bi bi-person-plus me-1"></i> Create Account
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Agent panel -->
        <div class="col-md-4" id="agent-panel"
             style="<?= $tab !== 'agent' ? 'display:none' : '' ?>">
            <div class="card shadow border-0 rounded-3">
                <div class="card-header udm-card-header text-center py-3">
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-shield-lock me-1"></i> Agent / Staff Login
                    </h6>
                </div>
                <div class="card-body p-4">
                    <?php if ($tab === 'agent' && $error): ?>
                        <div class="alert alert-danger py-2"><?= e($error) ?></div>
                    <?php endif; ?>
                    <form method="POST" action="<?= APP_URL ?>/auth/login.php" novalidate>
                        <input type="hidden" name="csrf_token"
                               value="<?= e($_SESSION['csrf_token'] ??= bin2hex(random_bytes(32))) ?>">
                        <input type="hidden" name="role" value="agent">

                        <div class="mb-3">
                            <label for="agentEmail" class="form-label fw-medium">Email</label>
                            <input type="email" id="agentEmail" name="email"
                                   class="form-control" placeholder="agent@udm.ac.mu"
                                   required autocomplete="email">
                        </div>
                        <div class="mb-3">
                            <label for="agentPassword" class="form-label fw-medium">Password</label>
                            <div class="input-group">
                                <input type="password" id="agentPassword" name="password"
                                       class="form-control" placeholder="••••••••"
                                       required autocomplete="current-password">
                                <button type="button" class="btn btn-outline-secondary toggle-pwd"
                                        data-target="agentPassword" aria-label="Toggle password visibility">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-udm-primary">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Login
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <p class="text-center text-muted small mt-3">
                Contact <a href="mailto:applocal@udm.ac.mu">applocal@udm.ac.mu</a>
                if you need access.
            </p>
        </div>

    </div>
</main>

<!-- Terms modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header udm-card-header">
                <h5 class="modal-title" id="termsModalLabel">Terms &amp; Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>By creating an account and submitting an application to Université des Mascareignes
                (UdM), you agree to provide accurate and truthful information. Any misrepresentation
                may result in the cancellation of your application or enrolment.</p>
                <p>The non-refundable application fee of <strong>USD 25</strong> must be paid before
                submission. Applications are processed in the order received.</p>
                <p>UdM will process your personal data in accordance with applicable data protection
                legislation solely for the purpose of evaluating your application.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-udm-primary" data-bs-dismiss="modal">
                    I Understand
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="udm-footer mt-auto">
    <div class="container text-center py-3">
        <p class="mb-0 small">
            &copy; <?= date('Y') ?> Université des Mascareignes &mdash;
            Rose Hill: +230 460-9500 | Pamplemousses: +230 260-4500 |
            <a href="mailto:applocal@udm.ac.mu" class="text-white">applocal@udm.ac.mu</a>
        </p>
    </div>
</footer>

<?php
$extraScripts = <<<HTML
<script src="{$_SERVER['REQUEST_URI']}"></script>
HTML;
// Clear extraScripts — JS is inline below
$extraScripts = '';
require_once __DIR__ . '/../includes/footer.php';
?>
<script>
// Role panel switcher
document.querySelectorAll('.role-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const target = btn.dataset.panel;
        document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        ['applicant-panel', 'agent-panel'].forEach(id => {
            document.getElementById(id).style.display = id === target ? '' : 'none';
        });
        document.getElementById('auth-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});

// Password visibility toggle
document.querySelectorAll('.toggle-pwd').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        const icon  = btn.querySelector('i');
        const show  = input.type === 'password';
        input.type  = show ? 'text' : 'password';
        icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
});

// Password strength meter
const regPwd = document.getElementById('regPassword');
if (regPwd) {
    regPwd.addEventListener('input', () => {
        const v = regPwd.value;
        let score = 0;
        if (v.length >= 8)            score++;
        if (/[A-Z]/.test(v))          score++;
        if (/[0-9]/.test(v))          score++;
        if (/[^A-Za-z0-9]/.test(v))   score++;
        const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
        const colors = ['', '#dc3545', '#fd7e14', '#ffc107', '#198754'];
        const bar = document.getElementById('strengthBar');
        bar.innerHTML = v
            ? `<div style="height:4px;border-radius:2px;background:${colors[score]};width:${score*25}%;transition:width .3s"></div>
               <small style="color:${colors[score]}">${labels[score]}</small>`
            : '';
    });
}

// Confirm password
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', e => {
        const pwd  = document.getElementById('regPassword').value;
        const conf = document.getElementById('regConfirm').value;
        if (pwd !== conf) {
            e.preventDefault();
            document.getElementById('regConfirm').classList.add('is-invalid');
        }
    });
    document.getElementById('regConfirm').addEventListener('input', function() {
        this.classList.toggle('is-invalid',
            this.value !== document.getElementById('regPassword').value);
    });
}
</script>
</body>
</html>
