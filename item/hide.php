<?php
include('../includes/config.php');
include('../includes/guard_admin.php');
include('../includes/alert.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/item/list.php');
    exit;
}

$item_id = (int)($_POST['item_id'] ?? 0);
$hide = (int)($_POST['hide'] ?? 1);

mysqli_execute_query($conn, "UPDATE item SET is_hidden = ? WHERE item_id = ?", [$hide, $item_id]);

set_alert('success', $hide ? 'Item hidden from the shop.' : 'Item is visible again.');
header('Location: ' . BASE_URL . '/item/list.php');
exit;