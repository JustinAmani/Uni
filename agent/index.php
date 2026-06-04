<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

requireLogin('agent');

$applications = getAllApplications();

$statusCounts = ['draft' => 0, 'submitted' => 0, 'under_review' => 0, 'accepted' => 0, 'rejected' => 0];
foreach ($applications as $a) {
    $statusCounts[$a['status']] = ($statusCounts[$a['status']] ?? 0) + 1;
}

$filterStatus = $_GET['status'] ?? '';
$filterSearch = trim($_GET['search'] ?? '');

$filtered = array_filter($applications, function ($a) use ($filterStatus, $filterSearch) {
    if ($filterStatus && $a['status'] !== $filterStatus) return false;
    if ($filterSearch) {
        $haystack = strtolower($a['app_number'] . ' ' . $a['first_name'] . ' ' . $a['last_name'] . ' ' . $a['user_email']);
        if (strpos($haystack, strtolower($filterSearch)) === false) return false;
    }
    return true;
});

$pageTitle = 'Agent Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head><?php require_once __DIR__ . '/../includes/head.php'; ?></head>
<body>

<nav class="navbar udm-navbar">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="#">
            <img src="<?= APP_URL ?>/assets/img/logo.svg" alt="UdM" height="44"
                 onerror="this.style.display='none'">
            <div>
                <div class="udm-brand-name">UNIVERSITÉ DES MASCAREIGNES</div>
                <div class="udm-tagline">SAVOIR, C'EST POUVOIR</div>
            </div>
        </a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="text-white small">
                <i class="bi bi-person-badge me-1"></i>
                <?= e(getCurrentUserName()) ?>
                <span class="badge bg-warning text-dark ms-1">
                    <?= ucfirst(getCurrentUserRole()) ?>
                </span>
            </span>
            <a href="<?= APP_URL ?>/auth/logout.php" class="btn btn-sm btn-outline-light">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 py-4">

    <h4 class="fw-bold text-udm mb-4">
        <i class="bi bi-speedometer2 me-2"></i> Applications Dashboard
    </h4>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <?php
        $statCards = [
            ['label' => 'Total',       'key' => null,          'count' => count($applications), 'icon' => 'bi-files',              'color' => 'primary'],
            ['label' => 'Submitted',   'key' => 'submitted',   'count' => $statusCounts['submitted'],   'icon' => 'bi-send',           'color' => 'info'],
            ['label' => 'Under Review','key' => 'under_review','count' => $statusCounts['under_review'],'icon' => 'bi-hourglass-split','color' => 'warning'],
            ['label' => 'Accepted',    'key' => 'accepted',    'count' => $statusCounts['accepted'],    'icon' => 'bi-check-circle',   'color' => 'success'],
            ['label' => 'Rejected',    'key' => 'rejected',    'count' => $statusCounts['rejected'],    'icon' => 'bi-x-circle',       'color' => 'danger'],
            ['label' => 'Draft',       'key' => 'draft',       'count' => $statusCounts['draft'],       'icon' => 'bi-pencil-square',  'color' => 'secondary'],
        ];
        foreach ($statCards as $card): ?>
        <div class="col-md-2 col-sm-4 col-6">
            <a href="?status=<?= $card['key'] ?? '' ?>"
               class="card border-0 shadow-sm text-decoration-none stat-card <?= $filterStatus === $card['key'] ? 'border-2 border-' . $card['color'] : '' ?>">
                <div class="card-body text-center py-3">
                    <i class="bi <?= $card['icon'] ?> fs-3 text-<?= $card['color'] ?>"></i>
                    <div class="fs-4 fw-bold mt-1"><?= $card['count'] ?></div>
                    <div class="text-muted small"><?= $card['label'] ?></div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Search & Filter -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control"
                               placeholder="Search by name, email or app number…"
                               value="<?= e($filterSearch) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <?php foreach (['submitted','under_review','accepted','rejected','draft'] as $st): ?>
                            <option value="<?= $st ?>" <?= $filterStatus === $st ? 'selected' : '' ?>>
                                <?= ucwords(str_replace('_', ' ', $st)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-udm-primary">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="?" class="btn btn-outline-secondary ms-1">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Applications Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>App No.</th>
                            <th>Applicant Name</th>
                            <th>Email</th>
                            <th>Nationality</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($filtered)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No applications found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($filtered as $a): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace">
                                        <?= e($a['app_number']) ?>
                                    </span>
                                </td>
                                <td class="fw-medium">
                                    <?= e(trim($a['first_name'] . ' ' . $a['last_name'])) ?: '—' ?>
                                </td>
                                <td class="text-muted small"><?= e($a['user_email']) ?></td>
                                <td><?= e($a['nationality'] ?? '—') ?></td>
                                <td class="small text-muted">
                                    <?= $a['submitted_at']
                                        ? date('d M Y', strtotime($a['submitted_at']))
                                        : '—' ?>
                                </td>
                                <td>
                                    <?php
                                    $badges = [
                                        'draft'        => 'secondary',
                                        'submitted'    => 'info',
                                        'under_review' => 'warning',
                                        'accepted'     => 'success',
                                        'rejected'     => 'danger',
                                    ];
                                    $bc = $badges[$a['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $bc ?>">
                                        <?= ucwords(str_replace('_', ' ', $a['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= APP_URL ?>/agent/view_application.php?id=<?= $a['id'] ?>"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer text-muted small">
            Showing <?= count($filtered) ?> of <?= count($applications) ?> applications
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
