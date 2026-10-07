<?php
include('../includes/config.php');

$_SESSION = [];
session_destroy();

session_start();
include('../includes/alert.php');
set_alert('success', 'You have been logged out.');

header('Location: ' . BASE_URL . '/user/login.php');
exit;