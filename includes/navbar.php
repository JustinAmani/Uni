<?php
/**
 * Reusable navbar partial.
 * Variables:
 *   $navHomeUrl  (string) – href on the logo (defaults to landing page)
 *   $navRight    (string) – optional HTML for the right side of the navbar
 */
$navHomeUrl = $navHomeUrl ?? (APP_URL . '/landing_page/index.php');
$navRight   = $navRight   ?? '';
?>
<nav class="navbar udm-navbar">
    <div class="container-fluid px-4">

        <!-- Logo + name -->
        <a class="navbar-brand d-flex align-items-center gap-3 text-decoration-none"
           href="<?= $navHomeUrl ?>">
            <img src="<?= APP_URL ?>/assets/img/udm-logo.jpg"
                 alt="Université des Mascareignes"
                 class="udm-logo-img">
        </a>

        <!-- Right slot -->
        <?php if ($navRight): ?>
            <div class="ms-auto d-flex align-items-center gap-3">
                <?= $navRight ?>
            </div>
        <?php endif; ?>

    </div>
</nav>
