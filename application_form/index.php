<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

requireLogin('applicant');

startSession();

$userId = getCurrentUserId();
$app    = getOrCreateApplication($userId);
$appId  = (int) $app['id'];

// Determine current step (URL param wins only if going back or step already reached)
$requestedStep = (int) ($_GET['step'] ?? $app['current_step']);
$step = max(1, min(7, $requestedStep));
// Don't allow jumping ahead of saved progress
if ($step > (int) $app['current_step']) {
    $step = (int) $app['current_step'];
}

// (session errors are read after the POST block, below)

// ── POST: process each step (no validation on Next – validate only on final submit) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedStep = (int) ($_POST['step'] ?? 1);
    $action     = $_POST['action'] ?? 'next';

    switch ($postedStep) {

        // ── Step 1: save everything, no validation ────────────────────────
        case 1:
            $dob = $_POST['date_of_birth'] ?? '';
            $v = [
                'title'              => sanitize($_POST['title'] ?? ''),
                'first_name'         => sanitize($_POST['first_name'] ?? ''),
                'middle_name'        => sanitize($_POST['middle_name'] ?? ''),
                'last_name'          => sanitize($_POST['last_name'] ?? ''),
                'maiden_name'        => sanitize($_POST['maiden_name'] ?? ''),
                'gender'             => sanitize($_POST['gender'] ?? ''),
                'date_of_birth'      => $dob,
                'marital_status'     => sanitize($_POST['marital_status'] ?? ''),
                'national_id'        => sanitize($_POST['national_id'] ?? ''),
                'place_of_birth'     => sanitize($_POST['place_of_birth'] ?? ''),
                'nationality'        => sanitize($_POST['nationality'] ?? ''),
                'email'              => filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL),
                'mobile_number'      => sanitize($_POST['mobile_number'] ?? ''),
                'home_number'        => sanitize($_POST['home_number'] ?? ''),
                'perm_address_line1' => sanitize($_POST['perm_address_line1'] ?? ''),
                'perm_address_line2' => sanitize($_POST['perm_address_line2'] ?? ''),
                'perm_address_line3' => sanitize($_POST['perm_address_line3'] ?? ''),
                'perm_town'          => sanitize($_POST['perm_town'] ?? ''),
                'perm_postcode'      => sanitize($_POST['perm_postcode'] ?? ''),
                'perm_country'       => sanitize($_POST['perm_country'] ?? ''),
                'corr_address_line1' => sanitize($_POST['corr_address_line1'] ?? ''),
                'corr_address_line2' => sanitize($_POST['corr_address_line2'] ?? ''),
                'corr_town'          => sanitize($_POST['corr_town'] ?? ''),
                'guardian_name'      => sanitize($_POST['guardian_name'] ?? ''),
                'guardian_address'   => sanitize($_POST['guardian_address'] ?? ''),
                'guardian_occupation'=> sanitize($_POST['guardian_occupation'] ?? ''),
                'guardian_phone'     => sanitize($_POST['guardian_phone'] ?? ''),
                'guardian_mobile'    => sanitize($_POST['guardian_mobile'] ?? ''),
            ];
            saveStep($appId, 1, $v);
            break;

        // ── Step 2: save courses, no validation ───────────────────────────
        case 2:
            $courseNames     = $_POST['course_name']   ?? [];
            $courseFaculties = $_POST['faculty_code']  ?? [];
            $courses = [];
            foreach ($courseNames as $i => $name) {
                if (trim($name)) {
                    $courses[] = ['name' => sanitize($name), 'faculty' => sanitize($courseFaculties[$i] ?? '')];
                }
            }
            saveCoursePreferences($appId, $courses);
            saveStep($appId, 2, [
                'course_level' => sanitize($_POST['course_level'] ?? ''),
                'entry_level'  => sanitize($_POST['entry_level'] ?? ''),
            ]);
            break;

        // ── Step 3: save education, no validation ─────────────────────────
        case 3:
            $schools = [];
            foreach ($_POST['school_name'] ?? [] as $i => $name) {
                if (trim($name)) {
                    $schools[] = [
                        'name'          => sanitize($name),
                        'entered_month' => (int) ($_POST['entered_month'][$i] ?? 0),
                        'entered_year'  => (int) ($_POST['entered_year'][$i] ?? 0),
                        'left_month'    => (int) ($_POST['left_month'][$i] ?? 0),
                        'left_year'     => (int) ($_POST['left_year'][$i] ?? 0),
                    ];
                }
            }
            saveSecondarySchools($appId, $schools);

            $oResults = [];
            foreach ($_POST['o_subject'] ?? [] as $i => $subj) {
                if (trim($subj)) {
                    $oResults[] = [
                        'subject'  => sanitize($subj),
                        'a1_month' => sanitize($_POST['o_a1_month'][$i] ?? ''),
                        'a1_grade' => sanitize($_POST['o_a1_grade'][$i] ?? ''),
                        'a2_month' => sanitize($_POST['o_a2_month'][$i] ?? ''),
                        'a2_grade' => sanitize($_POST['o_a2_grade'][$i] ?? ''),
                        'a3_month' => sanitize($_POST['o_a3_month'][$i] ?? ''),
                        'a3_grade' => sanitize($_POST['o_a3_grade'][$i] ?? ''),
                    ];
                }
            }
            saveOLevelResults($appId, $oResults);

            $principal = [];
            foreach ($_POST['a_principal_subject'] ?? [] as $i => $subj) {
                if (trim($subj)) {
                    $principal[] = [
                        'subject'  => sanitize($subj),
                        'a1_month' => sanitize($_POST['ap_a1_month'][$i] ?? ''),
                        'a1_grade' => sanitize($_POST['ap_a1_grade'][$i] ?? ''),
                        'a2_month' => sanitize($_POST['ap_a2_month'][$i] ?? ''),
                        'a2_grade' => sanitize($_POST['ap_a2_grade'][$i] ?? ''),
                        'a3_month' => sanitize($_POST['ap_a3_month'][$i] ?? ''),
                        'a3_grade' => sanitize($_POST['ap_a3_grade'][$i] ?? ''),
                    ];
                }
            }
            $subsidiary = [];
            foreach ($_POST['a_subsidiary_subject'] ?? [] as $i => $subj) {
                if (trim($subj)) {
                    $subsidiary[] = [
                        'subject'  => sanitize($subj),
                        'a1_month' => sanitize($_POST['as_a1_month'][$i] ?? ''),
                        'a1_grade' => sanitize($_POST['as_a1_grade'][$i] ?? ''),
                        'a2_month' => sanitize($_POST['as_a2_month'][$i] ?? ''),
                        'a2_grade' => sanitize($_POST['as_a2_grade'][$i] ?? ''),
                        'a3_month' => sanitize($_POST['as_a3_month'][$i] ?? ''),
                        'a3_grade' => sanitize($_POST['as_a3_grade'][$i] ?? ''),
                    ];
                }
            }
            saveALevelResults($appId, $principal, $subsidiary);
            saveStep($appId, 3, [
                'has_english_cert' => isset($_POST['has_english_cert']) ? 1 : 0,
                'has_french_cert'  => isset($_POST['has_french_cert']) ? 1 : 0,
            ]);
            break;

        // ── Step 4: save employment, no validation ────────────────────────
        case 4:
            $records = [];
            foreach ($_POST['employer'] ?? [] as $i => $emp) {
                if (trim($emp)) {
                    $records[] = [
                        'employer'        => sanitize($emp),
                        'title'           => sanitize($_POST['job_title'][$i] ?? ''),
                        'start_date'      => $_POST['start_date'][$i] ?? null,
                        'end_date'        => !empty($_POST['is_current'][$i]) ? null : ($_POST['end_date'][$i] ?? null),
                        'is_current'      => !empty($_POST['is_current'][$i]),
                        'responsibilities'=> sanitize($_POST['responsibilities'][$i] ?? ''),
                    ];
                }
            }
            saveEmploymentRecords($appId, $records);
            saveStep($appId, 4, []);
            break;

        // ── Step 5: upload files if provided, no mandatory check ──────────
        case 5:
            $uploadErrors = [];
            // Standard document types
            foreach (DOCUMENT_TYPES as $type => $label) {
                if (!empty($_FILES[$type]) && $_FILES[$type]['error'] !== UPLOAD_ERR_NO_FILE) {
                    $result = handleDocumentUpload($appId, $type, $_FILES[$type]);
                    if (!$result['success']) {
                        $uploadErrors[] = "$label: " . $result['message'];
                    }
                }
            }
            // Extra "other" documents added via "Add Another Document" button
            if (!empty($_FILES['other_extra'])) {
                $extras = $_FILES['other_extra'];
                // Convert single file to array format if needed
                if (!is_array($extras['name'])) {
                    $extras = array_map(fn($v) => [$v], $extras);
                }
                $count = count($extras['name']);
                for ($ei = 0; $ei < $count; $ei++) {
                    if ($extras['error'][$ei] === UPLOAD_ERR_NO_FILE) continue;
                    $file = [
                        'name'     => $extras['name'][$ei],
                        'tmp_name' => $extras['tmp_name'][$ei],
                        'size'     => $extras['size'][$ei],
                        'error'    => $extras['error'][$ei],
                    ];
                    $result = handleDocumentUpload($appId, 'other', $file);
                    if (!$result['success']) {
                        $uploadErrors[] = "Additional document: " . $result['message'];
                    }
                }
            }
            if ($uploadErrors) {
                $_SESSION['form_errors'] = $uploadErrors;
                header('Location: ' . APP_URL . '/application_form/index.php?step=5');
                exit;
            }
            saveStep($appId, 5, []);
            break;

        // ── Step 6: save payment if provided, no mandatory check ──────────
        case 6:
            $receipt = sanitize($_POST['payment_receipt'] ?? '');
            $fields  = [];
            if (!empty($receipt)) {
                $fields = ['payment_status' => 'paid', 'payment_receipt' => $receipt];
            }
            saveStep($appId, 6, $fields);
            break;

        // ── Step 7: FULL VALIDATION before final submit ───────────────────
        case 7:
            if (empty($_POST['declaration_agreed'])) {
                $_SESSION['submit_errors'] = [
                    7 => ['label' => 'Declaration', 'items' => ['You must check the declaration to submit.']],
                ];
                header('Location: ' . APP_URL . '/application_form/index.php?step=7');
                exit;
            }

            // Validate all required fields from all steps
            $allErrors = validateApplication($appId);
            if (!empty($allErrors)) {
                $_SESSION['submit_errors'] = $allErrors;
                header('Location: ' . APP_URL . '/application_form/index.php?step=7');
                exit;
            }

            // All good – submit
            $db = getDB();
            $db->prepare(
                'UPDATE applications SET status = "submitted", declaration_agreed = 1,
                 declaration_date = CURDATE(), submitted_at = NOW() WHERE id = ?'
            )->execute([$appId]);
            header('Location: ' . APP_URL . '/application_form/index.php?step=7&submitted=1');
            exit;
    }

    // Save & Exit
    if ($action === 'exit') {
        header('Location: ' . APP_URL . '/landing_page/index.php');
        exit;
    }

    // Advance to next step (no validation – always allowed)
    header('Location: ' . APP_URL . '/application_form/index.php?step=' . ($postedStep + 1));
    exit;
}

// Reload fresh app data
$app         = getApplication($appId);
$isMinor     = !empty($app['date_of_birth']) && !isOver18($app['date_of_birth']);
$submitted   = isset($_GET['submitted']);

// Step-level errors (only shown on step 7)
$errors       = $_SESSION['form_errors'] ?? [];
$submitErrors = $_SESSION['submit_errors'] ?? [];   // array keyed by step number
unset($_SESSION['form_errors'], $_SESSION['submit_errors']);

// Data for education step
$schools    = getSecondarySchools($appId);
$oResults   = getOLevelResults($appId);
$aResults   = getALevelResults($appId);
$principal  = array_values(array_filter($aResults, fn($r) => $r['level_type'] === 'principal'));
$subsidiary = array_values(array_filter($aResults, fn($r) => $r['level_type'] === 'subsidiary'));

// Data for other steps
$courses    = getCoursePreferences($appId);
$employment = getEmploymentRecords($appId);
$documents  = getDocuments($appId);
$docMap     = array_column($documents, null, 'document_type');

$currentYear = (int) date('Y');
$pageTitle   = 'Application Form – Step ' . $step;

// Pad arrays so the view always has at least 3 rows
while (count($schools)    < 3) $schools[]    = [];
while (count($oResults)   < 10) $oResults[]  = [];
while (count($principal)  < 3) $principal[]  = [];
while (count($subsidiary) < 4) $subsidiary[] = [];
while (count($employment) < 1) $employment[] = [];
while (count($courses)    < 1) $courses[]    = [];
?>
<!DOCTYPE html>
<html lang="en">
<head><?php require_once __DIR__ . '/../includes/head.php'; ?></head>
<body class="app-form-page">

<?php
// Navbar is now white – use dark text to match screenshot
$navRight = '
    <span class="nav-welcome text-muted small">
        Welcome, <strong class="text-dark">' . e(getCurrentUserName()) . '</strong>
        <span class="text-muted ms-1">&#x25BE;</span>
    </span>
    <a href="' . APP_URL . '/auth/logout.php" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-box-arrow-right me-1"></i> Logout
    </a>';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container py-4">

    <h4 class="udm-portal-title">University Online Application Portal</h4>

    <!-- Step Progress Bar -->
    <div class="udm-stepper mb-4">
        <?php
        $stepLabels = ['Personal','Courses','Education','Employment','Documents','Payment','Declaration'];
        for ($s = 1; $s <= 7; $s++):
            $done    = $s < $step || ($submitted && $s === 7);
            $current = $s === $step;
            $cls     = $done ? 'done' : ($current ? 'current' : 'pending');
        ?>
            <?php if ($s > 1): ?><div class="stepper-line <?= $s <= $step ? 'filled' : '' ?>"></div><?php endif; ?>
            <div class="stepper-item <?= $cls ?>">
                <a href="<?= $s <= (int)$app['current_step'] && !$submitted
                    ? APP_URL . '/application_form/index.php?step=' . $s
                    : '#' ?>"
                   class="stepper-circle <?= $cls ?>" title="<?= $stepLabels[$s-1] ?>">
                    <?php if ($done): ?>
                        <i class="bi bi-check-lg"></i>
                    <?php else: ?>
                        <?= $s ?>
                    <?php endif; ?>
                </a>
                <div class="stepper-label"><?= $stepLabels[$s-1] ?></div>
            </div>
        <?php endfor; ?>
    </div>

    <!-- Alerts: technical upload errors (steps 1-6) -->
    <?php if ($errors): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="bi bi-exclamation-triangle-fill me-1"></i>
                Upload error – please fix the following:</strong>
            <ul class="mb-0 mt-1">
                <?php foreach ($errors as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Alerts: full validation errors shown only on step 7 submit -->
    <?php if (!empty($submitErrors)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                Please complete the required fields before submitting:
            </strong>
            <?php foreach ($submitErrors as $stepNum => $group): ?>
                <div class="mt-2">
                    <span class="fw-semibold">
                        Step <?= $stepNum ?> – <?= e($group['label']) ?>
                        <?php if ($stepNum !== 7): ?>
                            <a href="<?= APP_URL ?>/application_form/index.php?step=<?= $stepNum ?>"
                               class="btn btn-sm btn-outline-danger ms-2 py-0">
                                <i class="bi bi-pencil me-1"></i>Go fix
                            </a>
                        <?php endif; ?>
                    </span>
                    <ul class="mb-0 mt-1">
                        <?php foreach ($group['items'] as $item): ?>
                            <li><?= e($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <?php if ($submitted): ?>
    <!-- SUCCESS PAGE -->
    <div class="card shadow border-0 text-center p-5">
        <div class="mb-3">
            <i class="bi bi-check-circle-fill text-success" style="font-size:4rem"></i>
        </div>
        <h4 class="fw-bold text-success mb-2">Application Submitted!</h4>
        <p class="text-muted mb-1">
            Application Number: <strong><?= e($app['app_number']) ?></strong>
        </p>
        <p class="text-muted">
            Thank you for applying to Université des Mascareignes. We will review your application
            and contact you at <strong><?= e($app['email']) ?></strong>.
        </p>
        <p class="small text-muted">
            Note: Original certificates must be presented on registration day in Mauritius.
        </p>
        <a href="<?= APP_URL ?>/auth/logout.php" class="btn btn-udm-primary mt-2">
            <i class="bi bi-house me-1"></i> Back to Home
        </a>
    </div>

    <?php elseif ($step === 1): ?>
    <!-- ═══════ STEP 1: Personal Information ════════════════════════════════ -->
    <form method="POST" action="<?= APP_URL ?>/application_form/index.php" novalidate
          id="stepForm">
        <input type="hidden" name="step" value="1">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <h6 class="step-heading"><i class="bi bi-person me-1"></i> Step 1: Personal Information</h6>
                <div class="section-block">
                <h6 class="section-label">Personal Details</h6>
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Title</label>
                        <select name="title" class="form-select">
                            <option value="">Select</option>
                            <?php foreach (['Mr','Mrs','Ms','Miss','Dr','Prof'] as $t): ?>
                                <option value="<?= $t ?>" <?= ($app['title'] ?? '') === $t ? 'selected' : '' ?>>
                                    <?= $t ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control"
                               value="<?= e($app['first_name'] ?? '') ?>" maxlength="100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control"
                               value="<?= e($app['middle_name'] ?? '') ?>" maxlength="100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control"
                               value="<?= e($app['last_name'] ?? '') ?>" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Maiden Name <span class="text-muted small">(if married)</span></label>
                        <input type="text" name="maiden_name" class="form-control"
                               value="<?= e($app['maiden_name'] ?? '') ?>" maxlength="100">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Gender <span class="text-danger">*</span></label>
                        <select name="gender" class="form-select">
                            <option value="">Select</option>
                            <?php foreach (['Male','Female','Other'] as $g): ?>
                                <option value="<?= $g ?>" <?= ($app['gender'] ?? '') === $g ? 'selected' : '' ?>>
                                    <?= $g ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_birth" class="form-control"
                               value="<?= e($app['date_of_birth'] ?? '') ?>"
                               max="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Marital Status <span class="text-danger">*</span></label>
                        <select name="marital_status" class="form-select">
                            <option value="">Select</option>
                            <?php foreach (['Single','Married','Divorced','Widowed','Other'] as $m): ?>
                                <option value="<?= $m ?>" <?= ($app['marital_status'] ?? '') === $m ? 'selected' : '' ?>>
                                    <?= $m ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">National ID / Passport No <span class="text-danger">*</span></label>
                        <input type="text" name="national_id" class="form-control"
                               value="<?= e($app['national_id'] ?? '') ?>" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Place of Birth</label>
                        <input type="text" name="place_of_birth" class="form-control"
                               value="<?= e($app['place_of_birth'] ?? '') ?>" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nationality <span class="text-danger">*</span></label>
                        <input type="text" name="nationality" class="form-control"
                               value="<?= e($app['nationality'] ?? '') ?>" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control"
                               value="<?= e($app['email'] ?? '') ?>" maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                        <input type="tel" name="mobile_number" class="form-control"
                               value="<?= e($app['mobile_number'] ?? '') ?>" maxlength="30">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Home Number</label>
                        <input type="tel" name="home_number" class="form-control"
                               value="<?= e($app['home_number'] ?? '') ?>" maxlength="30">
                    </div>
                </div>
                </div><!-- /section-block Personal Details -->

                <div class="section-block">
                <h6 class="section-label">Permanent Address <small style="text-transform:none;letter-spacing:0;font-weight:400">(in country of origin)</small></h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                        <input type="text" name="perm_address_line1" class="form-control"
                               value="<?= e($app['perm_address_line1'] ?? '') ?>" maxlength="255">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Town / City <span class="text-danger">*</span></label>
                        <input type="text" name="perm_town" class="form-control"
                               value="<?= e($app['perm_town'] ?? '') ?>" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Address Line 2</label>
                        <input type="text" name="perm_address_line2" class="form-control"
                               value="<?= e($app['perm_address_line2'] ?? '') ?>" maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Postcode</label>
                        <input type="text" name="perm_postcode" class="form-control"
                               value="<?= e($app['perm_postcode'] ?? '') ?>" maxlength="20">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Country <span class="text-danger">*</span></label>
                        <input type="text" name="perm_country" class="form-control"
                               value="<?= e($app['perm_country'] ?? '') ?>" maxlength="100">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Address Line 3</label>
                        <input type="text" name="perm_address_line3" class="form-control"
                               value="<?= e($app['perm_address_line3'] ?? '') ?>" maxlength="255">
                    </div>
                </div>
                </div><!-- /section-block Permanent Address -->

                <div class="section-block">
                <h6 class="section-label">Address in Mauritius <small style="text-transform:none;letter-spacing:0;font-weight:400">(if applicable)</small></h6>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Address Line 1</label>
                        <input type="text" name="corr_address_line1" class="form-control"
                               value="<?= e($app['corr_address_line1'] ?? '') ?>" maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Address Line 2</label>
                        <input type="text" name="corr_address_line2" class="form-control"
                               value="<?= e($app['corr_address_line2'] ?? '') ?>" maxlength="255">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Town / Village</label>
                        <input type="text" name="corr_town" class="form-control"
                               value="<?= e($app['corr_town'] ?? '') ?>" maxlength="100">
                    </div>
                </div>
                </div><!-- /section-block Address in Mauritius -->

                <?php if ($isMinor): ?>
                <div class="section-block" style="border-left:3px solid #f0a500;">
                <h6 class="section-label" style="color:#9a6200;">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    Parent / Guardian Details <small style="text-transform:none;letter-spacing:0;font-weight:400">(required – applicant is under 18)</small>
                </h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Guardian Name <span class="text-danger">*</span></label>
                        <input type="text" name="guardian_name" class="form-control"
                               value="<?= e($app['guardian_name'] ?? '') ?>" maxlength="200">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Occupation</label>
                        <input type="text" name="guardian_occupation" class="form-control"
                               value="<?= e($app['guardian_occupation'] ?? '') ?>" maxlength="100">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Address</label>
                        <textarea name="guardian_address" class="form-control" rows="2"
                                  maxlength="500"><?= e($app['guardian_address'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="guardian_phone" class="form-control"
                               value="<?= e($app['guardian_phone'] ?? '') ?>" maxlength="30">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mobile Number</label>
                        <input type="tel" name="guardian_mobile" class="form-control"
                               value="<?= e($app['guardian_mobile'] ?? '') ?>" maxlength="30">
                    </div>
                </div>
                </div><!-- /section-block Guardian -->
                <?php endif; ?>
            </div>
        </div>
        <?php include __DIR__ . '/partials/form_buttons.php'; ?>
    </form>

    <?php elseif ($step === 2): ?>
    <!-- ═══════ STEP 2: Course Preferences ══════════════════════════════════ -->
    <form method="POST" action="<?= APP_URL ?>/application_form/index.php" novalidate id="stepForm">
        <input type="hidden" name="step" value="2">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <h6 class="step-heading"><i class="bi bi-book me-1"></i> Step 2: Course Preferences</h6>
                <div class="section-block">
                <h6 class="section-label">Course Level &amp; Year of Entry</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Course Level</label>
                        <select name="course_level" class="form-select">
                            <option value="">Select</option>
                            <?php foreach (['Certificate','Diploma','BSc/BA','MSc/MA','MBA','PhD'] as $cl): ?>
                                <option value="<?= $cl ?>" <?= ($app['course_level'] ?? '') === $cl ? 'selected' : '' ?>>
                                    <?= $cl ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Requested Year of Entry</label>
                        <select name="entry_level" class="form-select">
                            <option value="">Select</option>
                            <?php foreach (['Year 1','Year 2','Year 3'] as $el): ?>
                                <option value="<?= $el ?>" <?= ($app['entry_level'] ?? '') === $el ? 'selected' : '' ?>>
                                    <?= $el ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                </div><!-- /section-block Course Level -->

                <div class="section-block">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="section-label mb-0">Courses Applied For <small style="text-transform:none;letter-spacing:0;font-weight:400">(in order of preference)</small></h6>
                    <button type="button" class="btn btn-sm btn-udm-primary ms-2" id="addCourse">
                        <i class="bi bi-plus-circle me-1"></i> Add Record
                    </button>
                </div>

                <div id="courseRows">
                <?php foreach ($courses as $i => $c):
                    $cName = $c['course_name'] ?? '';
                    $cFac  = $c['faculty_code'] ?? '';
                    $num   = $i + 1;
                ?>
                <div class="course-row row g-2 mb-3 align-items-end">
                    <div class="col-auto">
                        <span class="badge bg-udm fs-6 px-3 py-2 row-num"><?= $num ?></span>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Faculty</label>
                        <select name="faculty_code[]" class="form-select faculty-sel">
                            <option value="">-- Select Faculty --</option>
                            <?php foreach (FACULTIES as $code => $fname): ?>
                                <option value="<?= $code ?>" <?= $cFac === $code ? 'selected' : '' ?>>
                                    <?= $code ?> – <?= $fname ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col">
                        <label class="form-label">Course</label>
                        <select name="course_name[]" class="form-select course-sel">
                            <option value="">-- Select Course --</option>
                            <?php foreach (COURSES as $fac => $courseList): ?>
                                <?php foreach ($courseList as $course): ?>
                                    <option value="<?= e($course) ?>"
                                            data-faculty="<?= $fac ?>"
                                            style="<?= $cFac && $cFac !== $fac ? 'display:none' : '' ?>"
                                            <?= $cName === $course ? 'selected' : '' ?>>
                                        <?= e($course) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($i > 0): ?>
                    <div class="col-auto">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-course-row">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                </div><!-- /courseRows -->
                </div><!-- /section-block Courses Applied For -->
            </div>
        </div>
        <?php include __DIR__ . '/partials/form_buttons.php'; ?>
    </form>

    <?php elseif ($step === 3): ?>
    <!-- ═══════ STEP 3: Education ════════════════════════════════════════════ -->
    <form method="POST" action="<?= APP_URL ?>/application_form/index.php" novalidate id="stepForm">
        <input type="hidden" name="step" value="3">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <h6 class="step-heading"><i class="bi bi-mortarboard me-1"></i> Step 3: Education Details</h6>

                <!-- 3a: Secondary Schools -->
                <div class="section-block">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="section-label mb-0">Secondary Schools / Educational Institutions Attended</h6>
                        <button type="button" class="btn btn-sm btn-udm-primary ms-2" id="addSchool">
                            <i class="bi bi-plus-circle me-1"></i> Add Record
                        </button>
                    </div>
                    <div class="row g-2 mb-1 d-none d-md-flex">
                        <div class="col-md-5"><small class="fw-semibold text-muted">Institution</small></div>
                        <div class="col-md-4 text-center"><small class="fw-semibold text-muted">Entered (Month / Year)</small></div>
                        <div class="col-md-3 text-center"><small class="fw-semibold text-muted">Left (Month / Year)</small></div>
                    </div>
                    <div id="schoolRows">
                    <?php foreach ($schools as $i => $s): ?>
                    <div class="school-row row g-2 mb-2 align-items-center">
                        <div class="col-md-5">
                            <input type="text" name="school_name[]" class="form-control"
                                   value="<?= e($s['institution_name'] ?? '') ?>"
                                   placeholder="Institution name" maxlength="255">
                        </div>
                        <?php foreach ([['entered_month','entered_year','Entered'],['left_month','left_year','Left']] as [$mKey,$yKey,$lbl]): ?>
                        <div class="col-md-<?= ($lbl === 'Entered') ? '4' : '3' ?>">
                            <div class="d-flex gap-1">
                                <select name="<?= $mKey ?>[]" class="form-select">
                                    <option value="0">Month</option>
                                    <?php foreach (MONTHS as $num => $name): ?>
                                        <option value="<?= $num ?>" <?= ($s[$mKey] ?? 0) == $num ? 'selected' : '' ?>>
                                            <?= $name ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="<?= $yKey ?>[]" class="form-select">
                                    <option value="0">Year</option>
                                    <?php for ($y = $currentYear; $y >= 1990; $y--): ?>
                                        <option value="<?= $y ?>" <?= ($s[$yKey] ?? 0) == $y ? 'selected' : '' ?>>
                                            <?= $y ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                    </div>
                </div>

                <!-- 3b: O-Level Results -->
                <div class="section-block">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="section-label mb-0">SC / GCE "O" Level Results</h6>
                        <button type="button" class="btn btn-sm btn-udm-primary ms-2" id="addOLevel">
                            <i class="bi bi-plus-circle me-1"></i> Add Record
                        </button>
                    </div>
                    <?= renderResultsGrid($oResults, 'o_subject', ['o_a1_month','o_a1_grade','o_a2_month','o_a2_grade','o_a3_month','o_a3_grade'], 3, 'oLevelRows') ?>
                </div>

                <!-- 3c: A-Level Results -->
                <div class="section-block">
                    <h6 class="section-label mb-3">HSC / GCE "A" Level Results</h6>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <p class="text-muted small fw-semibold mb-0 text-uppercase">Principal / Advanced Level</p>
                        <button type="button" class="btn btn-sm btn-udm-primary ms-2" id="addPrincipal">
                            <i class="bi bi-plus-circle me-1"></i> Add Record
                        </button>
                    </div>
                    <?= renderResultsGrid($principal, 'a_principal_subject', ['ap_a1_month','ap_a1_grade','ap_a2_month','ap_a2_grade','ap_a3_month','ap_a3_grade'], 3, 'principalRows') ?>

                    <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                        <p class="text-muted small fw-semibold mb-0 text-uppercase">Subsidiary Level</p>
                        <button type="button" class="btn btn-sm btn-udm-primary ms-2" id="addSubsidiary">
                            <i class="bi bi-plus-circle me-1"></i> Add Record
                        </button>
                    </div>
                    <?= renderResultsGrid($subsidiary, 'a_subsidiary_subject', ['as_a1_month','as_a1_grade','as_a2_month','as_a2_grade','as_a3_month','as_a3_grade'], 3, 'subsidiaryRows') ?>
                </div>

                <!-- 3d: Language Certificate -->
                <div class="section-block">
                    <h6 class="section-label">
                        Language Certificate
                        <span class="badge bg-danger ms-1" style="font-size:.6rem;text-transform:none;letter-spacing:0">Mandatory</span>
                    </h6>
                    <p class="text-muted small mb-3">Check as appropriate and attach certificate when uploading documents.</p>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="engCert" name="has_english_cert"
                               value="1" <?= !empty($app['has_english_cert']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="engCert">
                            English – IELTS Certificate (Academic Version) <strong>OR</strong> TOEFL Certificate
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="frCert" name="has_french_cert"
                               value="1" <?= !empty($app['has_french_cert']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="frCert">
                            French – DELF Certificate B2
                        </label>
                    </div>
                </div>

            </div><!-- /card-body step 3 -->
        </div>
        <?php include __DIR__ . '/partials/form_buttons.php'; ?>
    </form>

    <?php elseif ($step === 4): ?>
    <!-- ═══════ STEP 4: Employment History ═══════════════════════════════════ -->
    <form method="POST" action="<?= APP_URL ?>/application_form/index.php" novalidate id="stepForm">
        <input type="hidden" name="step" value="4">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <h6 class="step-heading"><i class="bi bi-briefcase me-1"></i> Step 4: Employment History</h6>
            <div class="section-block">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="section-label mb-0">Employment Records</h6>
                    <button type="button" class="btn btn-sm btn-udm-primary ms-2" id="addEmployer">
                        <i class="bi bi-plus-circle me-1"></i> Add Record
                    </button>
                </div>
                <p class="text-muted small mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    If you have no employment history, leave this section blank and click Next.
                </p>
                <div id="employmentRows">
                <?php foreach ($employment as $i => $emp): ?>
                    <div class="employment-row record-block position-relative">
                        <?php if ($i > 0): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2 remove-row">
                                <i class="bi bi-trash"></i>
                            </button>
                        <?php endif; ?>
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label">Employer / Organisation</label>
                                <input type="text" name="employer[]" class="form-control"
                                       value="<?= e($emp['employer_name'] ?? '') ?>" maxlength="255">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Job Title / Position</label>
                                <input type="text" name="job_title[]" class="form-control"
                                       value="<?= e($emp['job_title'] ?? '') ?>" maxlength="255">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date[]" class="form-control"
                                       value="<?= e($emp['start_date'] ?? '') ?>">
                            </div>
                            <div class="col-md-3 end-date-col">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date[]" class="form-control"
                                       value="<?= e($emp['end_date'] ?? '') ?>"
                                       <?= !empty($emp['is_current']) ? 'disabled' : '' ?>>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input current-check" type="checkbox"
                                           name="is_current[<?= $i ?>]" id="current_<?= $i ?>"
                                           <?= !empty($emp['is_current']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="current_<?= $i ?>">
                                        Currently employed here
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Main Responsibilities</label>
                                <textarea name="responsibilities[]" class="form-control" rows="2"
                                          maxlength="500"><?= e($emp['responsibilities'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div><!-- /section-block step 4 -->
            </div><!-- /card-body step 4 -->
        </div>
        <?php include __DIR__ . '/partials/form_buttons.php'; ?>
    </form>

    <?php elseif ($step === 5): ?>
    <!-- ═══════ STEP 5: Document Upload ══════════════════════════════════════ -->
    <form method="POST" action="<?= APP_URL ?>/application_form/index.php"
          enctype="multipart/form-data" novalidate id="stepForm">
        <input type="hidden" name="step" value="5">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <h6 class="step-heading"><i class="bi bi-cloud-upload me-1"></i> Step 5: Document Upload</h6>
                <!-- Info text directly on blue background -->
                <div class="alert alert-info py-2 small mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Accepted formats: <strong>PDF, JPG, PNG</strong> — Max size: <strong>5 MB</strong> per file.<br>
                    <span class="text-danger fw-semibold">*</span> Passport Photo and National ID are required.
                    Originals must be presented on registration day.
                </div>

                <!-- Each document = individual record-block directly on blue bg -->
                <div id="docRows" class="row g-3">
                <?php foreach (DOCUMENT_TYPES as $type => $label):
                    $existing = $docMap[$type] ?? null;
                    $required = in_array($type, ['passport_photo','national_id']);
                ?>
                    <div class="col-md-6">
                        <div class="doc-upload-card record-block <?= $existing ? 'border-success' : '' ?>">
                            <h6 class="section-label">
                                <?= e($label) ?>
                                <?php if ($required): ?><span class="text-danger ms-1">*</span><?php endif; ?>
                            </h6>
                            <?php if ($existing): ?>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                    <span class="small text-success fw-medium">
                                        <?= e($existing['original_name']) ?> – uploaded
                                    </span>
                                </div>
                                <label class="text-muted small">Replace:</label>
                            <?php endif; ?>
                            <input type="file" name="<?= $type ?>" class="form-control form-control-sm"
                                   accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>

                <!-- Add more documents -->
                <div class="mt-3">
                    <button type="button" class="btn btn-sm btn-udm-primary" id="addDocSlot">
                        <i class="bi bi-plus-circle me-1"></i> Add Another Document
                    </button>
                </div>
            </div><!-- /card-body step 5 -->
        </div>
        <?php include __DIR__ . '/partials/form_buttons.php'; ?>
    </form>

    <?php elseif ($step === 6): ?>
    <!-- ═══════ STEP 6: Payment ═══════════════════════════════════════════════ -->
    <form method="POST" action="<?= APP_URL ?>/application_form/index.php" novalidate id="stepForm">
        <input type="hidden" name="step" value="6">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <h6 class="step-heading"><i class="bi bi-credit-card me-1"></i> Step 6: Application Fee Payment</h6>
            <div class="section-block">
                <div class="row justify-content-center">
                    <div class="col-md-7">
                        <div class="payment-summary p-4 rounded border text-center mb-4">
                            <p class="text-muted small mb-1">Non-Refundable Application Fee</p>
                            <h2 class="fw-bold text-udm mb-0">USD 25.00</h2>
                            <p class="text-muted small mt-1">Application No: <?= e($app['app_number']) ?></p>
                        </div>

                        <?php if (($app['payment_status'] ?? '') === 'paid'): ?>
                            <div class="alert alert-success text-center">
                                <i class="bi bi-check-circle-fill me-1"></i>
                                Payment already recorded. Receipt: <strong><?= e($app['payment_receipt']) ?></strong>
                            </div>
                        <?php else: ?>
                            <div class="mb-3">
                                <label class="form-label fw-medium">Payment Method</label>
                                <select name="payment_method" class="form-select">
                                    <option value="bank_transfer">Bank Transfer / Wire Transfer</option>
                                    <option value="western_union">Western Union</option>
                                    <option value="money_gram">MoneyGram</option>
                                    <option value="online">Online Payment (Card)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-medium">
                                    Payment Receipt / Reference Number <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="payment_receipt" class="form-control"
                                       placeholder="e.g. WU-123456789" maxlength="50">
                                <div class="form-text">
                                    Enter the reference or receipt number from your payment transaction.
                                </div>
                            </div>
                            <div class="alert alert-warning py-2 small">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Payment details will be verified by the admissions office.
                                Keep your payment receipt for your records.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div><!-- /section-block step 6 -->
            </div><!-- /card-body step 6 -->
        </div>
        <?php include __DIR__ . '/partials/form_buttons.php'; ?>
    </form>

    <?php elseif ($step === 7): ?>
    <!-- ═══════ STEP 7: Declaration ═══════════════════════════════════════════ -->
    <form method="POST" action="<?= APP_URL ?>/application_form/index.php" novalidate id="stepForm">
        <input type="hidden" name="step" value="7">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <h6 class="step-heading"><i class="bi bi-pen me-1"></i> Step 7: Declaration and Submission</h6>
                <div class="record-block mb-3">
                    <h6 class="section-label">Applicant Declaration</h6>
                    <p class="mb-3">
                        I, <strong><?= e(trim(($app['first_name'] ?? '') . ' ' . ($app['last_name'] ?? ''))) ?></strong>,
                        solemnly declare that if admitted to the University, I will diligently follow
                        the course of study for which I am selected till its termination, that I will
                        inform the University in writing and without delay if I withdraw from the course
                        and that I will conform to all rules and regulations of the University. I certify
                        that I will pay in advance all fees and dues required and I also declare that all
                        the above given information is true and correct.
                    </p>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="declCheck"
                               name="declaration_agreed" value="1" required>
                        <label class="form-check-label fw-semibold" for="declCheck">
                            I declare that all the above given information is true and correct.
                            <span class="text-danger">*</span>
                        </label>
                    </div>
                </div>

                <div class="record-block">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-triangle-fill text-warning mt-1"></i>
                        <div class="small">
                            <strong>Important:</strong> Original certificates must be presented upon arrival
                            in Mauritius on registration day. Failure to do so will result in your admission
                            being withheld.
                        </div>
                    </div>
                </div>
            </div><!-- /card-body step 7 -->
        </div>

        <div class="d-flex justify-content-between">
            <a href="<?= APP_URL ?>/application_form/index.php?step=6"
               class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Previous
            </a>
            <button type="submit" class="btn btn-success btn-lg px-5"
                    onclick="return confirm('Submit your application? This cannot be undone.')">
                <i class="bi bi-send-fill me-1"></i> Submit Application
            </button>
        </div>
    </form>
    <?php endif; ?>

</div><!-- /container -->

<?php
$jsVer = filemtime(__DIR__ . '/../assets/js/form.js');
$extraScripts = '<script src="' . APP_URL . '/assets/js/form.js?v=' . $jsVer . '"></script>';
require_once __DIR__ . '/../includes/footer.php';
?>

<?php
/**
 * Renders O/A-Level results as a Bootstrap grid (no HTML table).
 * $containerId is used by JS to append new rows dynamically.
 */
function renderResultsGrid(array $rows, string $subjName, array $colNames, int $count = 3, string $containerId = ''): string {
    $open  = $containerId ? '<div id="' . htmlspecialchars($containerId, ENT_QUOTES) . '">' : '';
    $close = $containerId ? '</div>' : '';

    $html  = $open;
    $html .= '<div class="row g-2 mb-1 d-none d-md-flex">';
    $html .= '<div class="col-md-3"><small class="fw-semibold text-muted">Subject</small></div>';
    $html .= '<div class="col"><small class="fw-semibold text-muted">1st Attempt <span class="text-muted">(MM/YYYY + Grade)</span></small></div>';
    $html .= '<div class="col"><small class="fw-semibold text-muted">2nd Attempt</small></div>';
    $html .= '<div class="col"><small class="fw-semibold text-muted">3rd Attempt</small></div>';
    $html .= '</div>';

    for ($i = 0; $i < $count; $i++) {
        $r       = $rows[$i] ?? [];
        $subjVal = htmlspecialchars($r['subject_name'] ?? '', ENT_QUOTES);

        $html .= '<div class="subj-row row g-2 mb-2 align-items-center">';
        $html .= '<div class="col-md-3"><input type="text" name="' . $subjName . '[]"'
               . ' class="form-control form-control-sm" value="' . $subjVal . '"'
               . ' maxlength="255" placeholder="Subject"></div>';

        for ($a = 1; $a <= 3; $a++) {
            $mKey  = "attempt{$a}_month";
            $gKey  = "attempt{$a}_grade";
            $mName = $colNames[($a - 1) * 2];
            $gName = $colNames[($a - 1) * 2 + 1];
            $mVal  = htmlspecialchars($r[$mKey] ?? '', ENT_QUOTES);
            $gVal  = htmlspecialchars($r[$gKey] ?? '', ENT_QUOTES);

            $html .= '<div class="col"><div class="input-group input-group-sm">'
                   . '<input type="text" name="' . $mName . '[]" placeholder="MM/YYYY"'
                   . ' class="form-control form-control-sm" value="' . $mVal . '" maxlength="7">'
                   . '<input type="text" name="' . $gName . '[]" placeholder="A"'
                   . ' class="form-control form-control-sm" value="' . $gVal . '" maxlength="5"'
                   . ' style="max-width:58px">'
                   . '</div></div>';
        }
        $html .= '</div>';
    }
    $html .= $close;
    return $html;
}
?>
</body>
</html>
