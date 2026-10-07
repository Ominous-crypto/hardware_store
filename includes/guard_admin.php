<?php
// Include this at the very top of every admin page, right after config.php.
if (empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/user/login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}