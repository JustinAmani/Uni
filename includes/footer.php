<?php
/**
 * Common footer partial – Bootstrap JS bundle with SRI hash.
 * Usage: require_once __DIR__ . '/../includes/footer.php';
 * Variables: $extraScripts (string, optional)
 */
$extraScripts = $extraScripts ?? '';
?>
<!-- Bootstrap 5.3.8 JS Bundle (includes Popper) -->
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
<?= $extraScripts ?>
