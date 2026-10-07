<?php
include('../includes/config.php');
include('../includes/guard_admin.php');
include('../includes/alert.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/categories.php');
    exit;
}

$category_id = (int)($_POST['category_id'] ?? 0);

$check = mysqli_execute_query($conn, "SELECT COUNT(*) AS c FROM item WHERE category_id = ?", [$category_id]);
$in_use = mysqli_fetch_assoc($check)['c'];

if ($in_use > 0) {
    set_alert('error', 'Cannot delete a category that still has items.');
} else {
    mysqli_execute_query($conn, "DELETE FROM category WHERE category_id = ?", [$category_id]);
    set_alert('success', 'Category deleted.');
}

header('Location: ' . BASE_URL . '/admin/categories.php');
exit;