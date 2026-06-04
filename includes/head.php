<?php
/**
 * Common <head> partial – CDN links with SRI hashes.
 * Usage: require_once __DIR__ . '/../includes/head.php';
 * Variables: $pageTitle (string), $extraHead (string, optional)
 */
$pageTitle = $pageTitle ?? 'UdM Application Portal';
$extraHead = $extraHead ?? '';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> – UdM</title>

<!-- Bootstrap 5.3.8 CSS -->
<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
    crossorigin="anonymous">

<!-- Bootstrap Icons 1.11.3 -->
<link
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    rel="stylesheet"
    integrity="sha384-XGjxtQfXaH2tnPFa9x+ruJTuLE3Aa6LhHSWRr1XeTyhezb4abCG4ccI5AkVDxqC+"
    crossorigin="anonymous">

<!-- UdM custom styles -->
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
<?= $extraHead ?>
