<?php
require_once __DIR__ . '/../includes/auth.php';
logoutUser();
header('Location: ' . APP_URL . '/landing_page/index.php');
exit;
