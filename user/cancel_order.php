<?php
include('../includes/config.php');
include('../includes/alert.php');

if (empty($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/user/login.php');
    exit;
}

$order_id = (int)($_POST['order_id'] ?? 0);

$customer_result = mysqli_execute_query($conn, "SELECT customer_id FROM customer WHERE user_id = ?", [$_SESSION['user_id']]);
$customer = mysqli_fetch_assoc($customer_result);

$order_result = mysqli_execute_query($conn, "SELECT * FROM orderinfo WHERE order_id = ? AND customer_id = ?", [$order_id, $customer['customer_id']]);
$order = mysqli_fetch_assoc($order_result);

if (!$order || $order['status'] !== 'processing') {
    set_alert('error', 'This order can no longer be canceled.');
    header('Location: ' . BASE_URL . '/user/my_orders.php');
    exit;
}

mysqli_begin_transaction($conn);
try {
    $lines_result = mysqli_execute_query($conn, "SELECT item_id, quantity FROM orderline WHERE order_id = ?", [$order_id]);
    while ($line = mysqli_fetch_assoc($lines_result)) {
        mysqli_execute_query($conn, "UPDATE stock SET quantity = quantity + ? WHERE item_id = ?", [$line['quantity'], $line['item_id']]);
    }

    mysqli_execute_query($conn, "UPDATE orderinfo SET status = 'canceled' WHERE order_id = ?", [$order_id]);

    mysqli_commit($conn);
    set_alert('success', 'Order canceled and stock returned.');
} catch (mysqli_sql_exception $e) {
    mysqli_rollback($conn);
    set_alert('error', 'Something went wrong. Please try again.');
}

header('Location: ' . BASE_URL . '/user/my_orders.php');
exit;