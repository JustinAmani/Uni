<?php
/**
 * Reusable white navbar – matches the screenshot design.
 * Variables:
 *   $navHomeUrl (string) – logo link href
 *   $navRight   (string) – HTML for the right side
 */
$navHomeUrl = $navHomeUrl ?? (APP_URL . '/landing_page/index.php');
$navRight   = $navRight   ?? '';
?>
<nav class="navbar udm-navbar d-flex align-items-center justify-content-between px-3 px-md-4">

    <!-- Logo -->
    <a href="<?= $navHomeUrl ?>" class="d-flex align-items-center text-decoration-none">
        <img src="<?= APP_URL ?>/assets/img/udm-logo.png"
             alt="Université des Mascareignes"
             class="udm-logo-img">
    </a>

    <!-- Right slot (welcome text, logout, etc.) -->
    <?php if ($navRight): ?>
        <div class="d-flex align-items-center gap-3">
            <?= $navRight ?>
        </div>
    <?php endif; ?>

</nav>
