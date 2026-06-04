<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

function generateAppNumber(): string {
    return 'UDM-' . date('Y') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

function getOrCreateApplication(int $userId): array {
    $db = getDB();
    $stmt = $db->prepare(
        'SELECT * FROM applications WHERE user_id = ? AND status = "draft" ORDER BY created_at DESC LIMIT 1'
    );
    $stmt->execute([$userId]);
    $app = $stmt->fetch();

    if (!$app) {
        $appNumber = generateAppNumber();
        $stmt = $db->prepare('INSERT INTO applications (user_id, app_number) VALUES (?, ?)');
        $stmt->execute([$userId, $appNumber]);
        $id = $db->lastInsertId();
        return ['id' => $id, 'app_number' => $appNumber, 'current_step' => 1];
    }
    return $app;
}

function getApplication(int $appId): ?array {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM applications WHERE id = ?');
    $stmt->execute([$appId]);
    return $stmt->fetch() ?: null;
}

function saveStep(int $appId, int $step, array $fields): void {
    $db = getDB();
    $sets = array_map(fn($k) => "$k = :$k", array_keys($fields));
    $sets[] = 'current_step = :current_step';
    $fields['current_step'] = max($step + 1, getApplication($appId)['current_step'] ?? $step + 1);
    $fields['id'] = $appId;
    $sql = 'UPDATE applications SET ' . implode(', ', $sets) . ' WHERE id = :id';
    $stmt = $db->prepare($sql);
    $stmt->execute($fields);
}

function getCoursePreferences(int $appId): array {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM course_preferences WHERE application_id = ? ORDER BY preference_order');
    $stmt->execute([$appId]);
    return $stmt->fetchAll();
}

function saveCoursePreferences(int $appId, array $courses): void {
    $db = getDB();
    $db->prepare('DELETE FROM course_preferences WHERE application_id = ?')->execute([$appId]);
    $stmt = $db->prepare('INSERT INTO course_preferences (application_id, preference_order, course_name, faculty_code) VALUES (?, ?, ?, ?)');
    foreach ($courses as $i => $c) {
        if (!empty($c['name'])) {
            $stmt->execute([$appId, $i + 1, $c['name'], $c['faculty'] ?? '']);
        }
    }
}

function getSecondarySchools(int $appId): array {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM secondary_schools WHERE application_id = ? ORDER BY display_order');
    $stmt->execute([$appId]);
    return $stmt->fetchAll();
}

function saveSecondarySchools(int $appId, array $schools): void {
    $db = getDB();
    $db->prepare('DELETE FROM secondary_schools WHERE application_id = ?')->execute([$appId]);
    $stmt = $db->prepare(
        'INSERT INTO secondary_schools (application_id, institution_name, entered_month, entered_year, left_month, left_year, display_order)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    foreach ($schools as $i => $s) {
        if (!empty($s['name'])) {
            $stmt->execute([$appId, $s['name'], $s['entered_month'] ?? null, $s['entered_year'] ?? null,
                $s['left_month'] ?? null, $s['left_year'] ?? null, $i]);
        }
    }
}

function getOLevelResults(int $appId): array {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM o_level_results WHERE application_id = ? ORDER BY display_order');
    $stmt->execute([$appId]);
    return $stmt->fetchAll();
}

function saveOLevelResults(int $appId, array $results): void {
    $db = getDB();
    $db->prepare('DELETE FROM o_level_results WHERE application_id = ?')->execute([$appId]);
    $stmt = $db->prepare(
        'INSERT INTO o_level_results (application_id, subject_name, attempt1_month, attempt1_grade, attempt2_month, attempt2_grade, attempt3_month, attempt3_grade, display_order)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    foreach ($results as $i => $r) {
        if (!empty($r['subject'])) {
            $stmt->execute([$appId, $r['subject'],
                $r['a1_month'] ?? null, $r['a1_grade'] ?? null,
                $r['a2_month'] ?? null, $r['a2_grade'] ?? null,
                $r['a3_month'] ?? null, $r['a3_grade'] ?? null, $i]);
        }
    }
}

function getALevelResults(int $appId): array {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM a_level_results WHERE application_id = ? ORDER BY level_type, display_order');
    $stmt->execute([$appId]);
    return $stmt->fetchAll();
}

function saveALevelResults(int $appId, array $principal, array $subsidiary): void {
    $db = getDB();
    $db->prepare('DELETE FROM a_level_results WHERE application_id = ?')->execute([$appId]);
    $stmt = $db->prepare(
        'INSERT INTO a_level_results (application_id, level_type, subject_name, attempt1_month, attempt1_grade, attempt2_month, attempt2_grade, attempt3_month, attempt3_grade, display_order)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    foreach ($principal as $i => $r) {
        if (!empty($r['subject'])) {
            $stmt->execute([$appId, 'principal', $r['subject'],
                $r['a1_month'] ?? null, $r['a1_grade'] ?? null,
                $r['a2_month'] ?? null, $r['a2_grade'] ?? null,
                $r['a3_month'] ?? null, $r['a3_grade'] ?? null, $i]);
        }
    }
    foreach ($subsidiary as $i => $r) {
        if (!empty($r['subject'])) {
            $stmt->execute([$appId, 'subsidiary', $r['subject'],
                $r['a1_month'] ?? null, $r['a1_grade'] ?? null,
                $r['a2_month'] ?? null, $r['a2_grade'] ?? null,
                $r['a3_month'] ?? null, $r['a3_grade'] ?? null, $i]);
        }
    }
}

function getEmploymentRecords(int $appId): array {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM employment_records WHERE application_id = ? ORDER BY display_order');
    $stmt->execute([$appId]);
    return $stmt->fetchAll();
}

function saveEmploymentRecords(int $appId, array $records): void {
    $db = getDB();
    $db->prepare('DELETE FROM employment_records WHERE application_id = ?')->execute([$appId]);
    $stmt = $db->prepare(
        'INSERT INTO employment_records (application_id, employer_name, job_title, start_date, end_date, is_current, responsibilities, display_order)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    foreach ($records as $i => $r) {
        if (!empty($r['employer'])) {
            $stmt->execute([$appId, $r['employer'], $r['title'] ?? '',
                $r['start_date'] ?? null, $r['end_date'] ?? null,
                isset($r['is_current']) ? 1 : 0,
                $r['responsibilities'] ?? null, $i]);
        }
    }
}

function getDocuments(int $appId): array {
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM documents WHERE application_id = ?');
    $stmt->execute([$appId]);
    return $stmt->fetchAll();
}

function handleDocumentUpload(int $appId, string $docType, array $file): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error.'];
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File too large (max 5MB).'];
    }
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, ALLOWED_TYPES)) {
        return ['success' => false, 'message' => 'Only PDF, JPG, PNG files are allowed.'];
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $storedName = $appId . '_' . $docType . '_' . time() . '.' . $ext;
    $destDir = UPLOAD_DIR . $appId . '/';
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }
    $dest = $destDir . $storedName;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['success' => false, 'message' => 'Failed to save file.'];
    }

    $db = getDB();
    $db->prepare('DELETE FROM documents WHERE application_id = ? AND document_type = ?')->execute([$appId, $docType]);
    $db->prepare(
        'INSERT INTO documents (application_id, document_type, original_name, stored_name, file_path) VALUES (?, ?, ?, ?, ?)'
    )->execute([$appId, $docType, $file['name'], $storedName, $dest]);

    return ['success' => true];
}

function getAllApplications(): array {
    $db = getDB();
    $stmt = $db->query(
        'SELECT a.*, u.email as user_email
         FROM applications a
         JOIN users u ON u.id = a.user_id
         ORDER BY a.updated_at DESC'
    );
    return $stmt->fetchAll();
}

function updateApplicationStatus(int $appId, string $status): void {
    $db = getDB();
    $db->prepare('UPDATE applications SET status = ? WHERE id = ?')->execute([$status, $appId]);
}

function getStepTitle(int $step): string {
    return [
        1 => 'Personal Information',
        2 => 'Course Preferences',
        3 => 'Education Details',
        4 => 'Employment History',
        5 => 'Document Upload',
        6 => 'Payment',
        7 => 'Declaration & Submission',
    ][$step] ?? '';
}

function isOver18(string $dob): bool {
    return (new DateTime($dob))->diff(new DateTime())->y >= 18;
}
