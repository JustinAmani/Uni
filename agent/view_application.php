<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

requireLogin('agent');

$appId = (int) ($_GET['id'] ?? 0);
if (!$appId) {
    header('Location: ' . APP_URL . '/agent/index.php');
    exit;
}

$app = getApplication($appId);
if (!$app) {
    header('Location: ' . APP_URL . '/agent/index.php');
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_status'])) {
    $allowed = ['under_review', 'accepted', 'rejected'];
    $ns = $_POST['new_status'];
    if (in_array($ns, $allowed)) {
        updateApplicationStatus($appId, $ns);
        header('Location: ' . APP_URL . '/agent/view_application.php?id=' . $appId . '&updated=1');
        exit;
    }
}

$courses    = getCoursePreferences($appId);
$schools    = getSecondarySchools($appId);
$oResults   = getOLevelResults($appId);
$aResults   = getALevelResults($appId);
$principal  = array_filter($aResults, fn($r) => $r['level_type'] === 'principal');
$subsidiary = array_filter($aResults, fn($r) => $r['level_type'] === 'subsidiary');
$employment = getEmploymentRecords($appId);
$documents  = getDocuments($appId);

$updated   = isset($_GET['updated']);
$pageTitle = 'Application ' . $app['app_number'];
?>
<!DOCTYPE html>
<html lang="en">
<head><?php require_once __DIR__ . '/../includes/head.php'; ?></head>
<body>

<?php
$navHomeUrl = APP_URL . '/agent/index.php';
$navRight   = '<a href="' . APP_URL . '/auth/logout.php" class="btn btn-sm btn-outline-light">
    <i class="bi bi-box-arrow-right me-1"></i> Logout</a>';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-4">

    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="<?= APP_URL ?>/agent/index.php" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
        <h4 class="mb-0 fw-bold text-udm">
            Application <?= e($app['app_number']) ?>
        </h4>
        <?php
        $badges = ['draft'=>'secondary','submitted'=>'info','under_review'=>'warning','accepted'=>'success','rejected'=>'danger'];
        $bc = $badges[$app['status']] ?? 'secondary';
        ?>
        <span class="badge bg-<?= $bc ?> fs-6">
            <?= ucwords(str_replace('_', ' ', $app['status'])) ?>
        </span>
        <?php if ($updated): ?>
            <div class="alert alert-success py-1 px-3 mb-0 ms-2">
                <i class="bi bi-check-circle me-1"></i> Status updated.
            </div>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <!-- Left: Application Details -->
        <div class="col-lg-8">

            <!-- Personal -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header udm-card-header py-2">
                    <h6 class="mb-0"><i class="bi bi-person me-1"></i> Personal Details</h6>
                </div>
                <div class="card-body">
                    <?php
                    $fields = [
                        ['Full Name',    trim($app['title'].' '.$app['first_name'].' '.($app['middle_name']?$app['middle_name'].' ':'').$app['last_name'])],
                        ['Maiden Name',  $app['maiden_name']],
                        ['Gender',       $app['gender']],
                        ['Date of Birth',$app['date_of_birth'] ? date('d M Y', strtotime($app['date_of_birth'])) : ''],
                        ['Marital Status',$app['marital_status']],
                        ['National ID',  $app['national_id']],
                        ['Place of Birth',$app['place_of_birth']],
                        ['Nationality',  $app['nationality']],
                        ['Email',        $app['email']],
                        ['Mobile',       $app['mobile_number']],
                        ['Home Phone',   $app['home_number']],
                    ];
                    ?>
                    <dl class="row mb-0">
                    <?php foreach ($fields as [$label, $val]): if (empty($val)) continue; ?>
                        <dt class="col-sm-4 text-muted"><?= $label ?></dt>
                        <dd class="col-sm-8"><?= e($val) ?></dd>
                    <?php endforeach; ?>
                    </dl>
                    <hr class="my-2">
                    <strong class="small text-muted">Permanent Address</strong>
                    <p class="mb-0 small mt-1">
                        <?= e(implode(', ', array_filter([
                            $app['perm_address_line1'], $app['perm_address_line2'],
                            $app['perm_address_line3'], $app['perm_town'],
                            $app['perm_postcode'], $app['perm_country']
                        ]))) ?>
                    </p>
                </div>
            </div>

            <!-- Courses -->
            <?php if ($courses): ?>
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header udm-card-header py-2">
                    <h6 class="mb-0"><i class="bi bi-book me-1"></i> Course Preferences</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2 mb-2 small text-muted">
                        <div class="col-4">Level: <strong><?= e($app['course_level'] ?? '—') ?></strong></div>
                        <div class="col-4">Entry: <strong><?= e($app['entry_level'] ?? '—') ?></strong></div>
                    </div>
                    <ol class="mb-0">
                        <?php foreach ($courses as $c): ?>
                            <li>
                                <span class="badge bg-light text-dark border me-1"><?= e($c['faculty_code']) ?></span>
                                <?= e($c['course_name']) ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>
            <?php endif; ?>

            <!-- Education -->
            <?php if ($schools || $oResults || $aResults): ?>
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header udm-card-header py-2">
                    <h6 class="mb-0"><i class="bi bi-mortarboard me-1"></i> Education Details</h6>
                </div>
                <div class="card-body">
                    <?php if ($schools): ?>
                        <strong class="small text-muted">Secondary Schools</strong>
                        <ul class="small mb-2">
                        <?php foreach ($schools as $s): ?>
                            <li>
                                <?= e($s['institution_name']) ?>
                                <?php if ($s['entered_year'] || $s['left_year']): ?>
                                    <span class="text-muted">
                                        (<?= ($s['entered_month'] ?? '?') . '/' . ($s['entered_year'] ?? '?') ?>
                                        – <?= ($s['left_month'] ?? '?') . '/' . ($s['left_year'] ?? '?') ?>)
                                    </span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if ($oResults): ?>
                        <strong class="small text-muted">O-Level Results</strong>
                        <div class="table-responsive mt-1 mb-2">
                            <table class="table table-sm table-bordered small">
                                <thead class="table-light"><tr>
                                    <th>Subject</th><th>1st</th><th>2nd</th><th>3rd</th>
                                </tr></thead>
                                <tbody>
                                <?php foreach ($oResults as $r): ?>
                                    <tr>
                                        <td><?= e($r['subject_name']) ?></td>
                                        <td><?= e($r['attempt1_grade'] ?? '') ?></td>
                                        <td><?= e($r['attempt2_grade'] ?? '') ?></td>
                                        <td><?= e($r['attempt3_grade'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    <?php if ($aResults): ?>
                        <strong class="small text-muted">A-Level Results</strong>
                        <div class="table-responsive mt-1">
                            <table class="table table-sm table-bordered small">
                                <thead class="table-light"><tr>
                                    <th>Type</th><th>Subject</th><th>1st</th><th>2nd</th><th>3rd</th>
                                </tr></thead>
                                <tbody>
                                <?php foreach ($aResults as $r): ?>
                                    <tr>
                                        <td><span class="badge bg-light text-dark border">
                                            <?= ucfirst($r['level_type']) ?>
                                        </span></td>
                                        <td><?= e($r['subject_name']) ?></td>
                                        <td><?= e($r['attempt1_grade'] ?? '') ?></td>
                                        <td><?= e($r['attempt2_grade'] ?? '') ?></td>
                                        <td><?= e($r['attempt3_grade'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    <div class="mt-1 small">
                        <?php if ($app['has_english_cert']): ?>
                            <span class="badge bg-success me-1"><i class="bi bi-check me-1"></i>IELTS/TOEFL</span>
                        <?php endif; ?>
                        <?php if ($app['has_french_cert']): ?>
                            <span class="badge bg-success"><i class="bi bi-check me-1"></i>DELF B2</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Employment -->
            <?php if ($employment): ?>
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header udm-card-header py-2">
                    <h6 class="mb-0"><i class="bi bi-briefcase me-1"></i> Employment History</h6>
                </div>
                <div class="card-body">
                    <?php foreach ($employment as $e2): ?>
                        <div class="border rounded p-2 mb-2 small">
                            <strong><?= e($e2['employer_name']) ?></strong> – <?= e($e2['job_title']) ?><br>
                            <span class="text-muted">
                                <?= $e2['start_date'] ? date('M Y', strtotime($e2['start_date'])) : '?' ?>
                                –
                                <?= $e2['is_current'] ? 'Present' : ($e2['end_date'] ? date('M Y', strtotime($e2['end_date'])) : '?') ?>
                            </span>
                            <?php if ($e2['responsibilities']): ?>
                                <p class="text-muted mb-0 mt-1"><?= e($e2['responsibilities']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Documents -->
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header udm-card-header py-2">
                    <h6 class="mb-0"><i class="bi bi-paperclip me-1"></i> Documents</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($documents)): ?>
                        <p class="text-muted small mb-0">No documents uploaded.</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                        <?php foreach ($documents as $doc): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-1">
                                <span>
                                    <i class="bi bi-file-earmark me-1 text-muted"></i>
                                    <strong><?= e(DOCUMENT_TYPES[$doc['document_type']] ?? $doc['document_type']) ?></strong>
                                    <span class="text-muted small ms-1"><?= e($doc['original_name']) ?></span>
                                </span>
                                <a href="<?= UPLOAD_URL . $appId . '/' . e($doc['stored_name']) ?>"
                                   target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-download"></i>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /col-lg-8 -->

        <!-- Right: Actions -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top:80px">
                <div class="card-header udm-card-header py-2">
                    <h6 class="mb-0"><i class="bi bi-gear me-1"></i> Actions</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-1">
                        Submitted: <strong>
                            <?= $app['submitted_at'] ? date('d M Y H:i', strtotime($app['submitted_at'])) : 'Not submitted' ?>
                        </strong>
                    </p>
                    <p class="text-muted small mb-3">
                        Payment: <strong>
                            <?php if ($app['payment_status'] === 'paid'): ?>
                                <span class="text-success">Paid</span> – <?= e($app['payment_receipt']) ?>
                            <?php else: ?>
                                <span class="text-warning">Pending</span>
                            <?php endif; ?>
                        </strong>
                    </p>

                    <?php if (in_array($app['status'], ['submitted','under_review'])): ?>
                    <form method="POST">
                        <label class="form-label fw-medium">Update Status</label>
                        <select name="new_status" class="form-select mb-2">
                            <option value="under_review" <?= $app['status']==='under_review'?'selected':'' ?>>
                                Under Review
                            </option>
                            <option value="accepted">Accepted</option>
                            <option value="rejected">Rejected</option>
                        </select>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-udm-primary">
                                <i class="bi bi-check2 me-1"></i> Update Status
                            </button>
                        </div>
                    </form>
                    <?php else: ?>
                        <div class="alert alert-<?= $bc ?> py-2 text-center small">
                            Application is <strong><?= ucwords(str_replace('_',' ',$app['status'])) ?></strong>
                        </div>
                    <?php endif; ?>

                    <hr>
                    <a href="<?= APP_URL ?>/agent/index.php" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

    </div><!-- /row -->
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
