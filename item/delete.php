<?php
include('../includes/config.php');
include('../includes/guard_admin.php');
include('../includes/alert.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/item/list.php');
    exit;
}

$item_id = (int)($_POST['item_id'] ?? 0);

$check = mysqli_execute_query($conn, "SELECT COUNT(*) AS c FROM orderline WHERE item_id = ?", [$item_id]);
$in_use = mysqli_fetch_assoc($check)['c'];

if ($in_use > 0) {
    set_alert('error', 'Cannot delete an item that has order history.');
} else {
    // Remove the image file from disk too, so it doesn't pile up unused.
    $item_result = mysqli_execute_query($conn, "SELECT image_path FROM item WHERE item_id = ?", [$item_id]);
    $item = mysqli_fetch_assoc($item_result);

    mysqli_execute_query($conn, "DELETE FROM stock WHERE item_id = ?", [$item_id]);
    mysqli_execute_query($conn, "DELETE FROM item WHERE item_id = ?", [$item_id]);

    if ($item && $item['image_path'] !== 'default.png') {
        $file_path = __DIR__ . '/images/' . $item['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    set_alert('success', 'Item deleted.');
}

header('Location: ' . BASE_URL . '/item/list.php');
exit;