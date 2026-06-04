<?php
define('APP_NAME', 'UdM Application Portal');
define('APP_URL', '/university_application_system');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', APP_URL . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'application/pdf']);
define('APP_FEE', 25.00);
define('SESSION_LIFETIME', 7200); // 2 hours

define('FACULTIES', [
    'FBM'  => 'Faculty of Business & Management',
    'FICT' => 'Faculty of Information & Communication Technology',
    'FSDE' => 'Faculty of Sustainable Development & Engineering',
]);

define('COURSES', [
    'FBM'  => [
        'BSc (Hons) Accounting & Finance',
        'BSc (Hons) Business Management',
        'BSc (Hons) Marketing Management',
        'BSc (Hons) Human Resource Management',
        'BSc (Hons) International Business',
        'MBA (Masters of Business Administration)',
    ],
    'FICT' => [
        'BSc (Hons) Computer Science',
        'BSc (Hons) Information Technology',
        'BSc (Hons) Software Engineering',
        'BSc (Hons) Cyber Security',
        'BSc (Hons) Data Science & Analytics',
        'MSc Information Technology',
    ],
    'FSDE' => [
        'BSc (Hons) Civil Engineering',
        'BSc (Hons) Electrical & Electronic Engineering',
        'BSc (Hons) Mechanical Engineering',
        'BSc (Hons) Environmental Science',
        'BSc (Hons) Sustainable Energy',
        'MEng (Masters of Engineering)',
    ],
]);

define('MONTHS', [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
]);

define('DOCUMENT_TYPES', [
    'passport_photo'  => 'Passport Photo',
    'national_id'     => 'National ID / Passport',
    'sc_certificate'  => 'School Certificate (SC)',
    'hsc_certificate' => 'Higher School Certificate (HSC)',
    'degree_diploma'  => 'Degree / Diploma',
    'other'           => 'Other Certificate',
]);
