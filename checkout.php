<?php
include('includes/config.php');
include('includes/alert.php');

// Guest check: send to login, remember to come straight back here after.
if (empty($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = BASE_URL . '/checkout.php';
    set_alert('error', 'Please log in to check out.');
    header('Location: ' . BASE_URL . '/user/login.php');
    exit;
}

$cart = $_SESSION['cart'] ?? [];
if (!$cart) {
    set_alert('error', 'Your cart is empty.');
    header('Location: ' . BASE_URL . '/view_cart.php');
    exit;
}

// Profile check: every customer created through register.php has one,
// but this guards against an account that somehow doesn't.
$customer_result = mysqli_execute_query($conn, "SELECT customer_id, fname, lname, address, contact FROM customer WHERE user_id = ?", [$_SESSION['user_id']]);
$customer = mysqli_fetch_assoc($customer_result);

if (!$customer) {
    set_alert('error', 'Please complete your profile before checking out.');
    header('Location: ' . BASE_URL . '/user/profile.php');
    exit;
}

// Re-check stock for every item in the cart right now, not whatever
// was true when it was added.
$ids = array_keys($cart);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));

$sql = "SELECT i.item_id, i.item_name, i.price, s.quantity AS stock_qty
        FROM item i JOIN stock s ON s.item_id = i.item_id
        WHERE i.item_id IN ($placeholders)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$ids);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$cart_items = [];
$insufficient = [];

while ($row = mysqli_fetch_assoc($result)) {
    $qty = $cart[$row['item_id']];
    if ($qty > $row['stock_qty']) {
        $insufficient[] = $row['item_name'];
    }
    $cart_items[] = array_merge($row, ['qty' => $qty]);
}

if ($insufficient) {
    set_alert('error', 'Not enough stock for: ' . implode(', ', $insufficient) . '. Please adjust your cart.');
    header('Location: ' . BASE_URL . '/view_cart.php');
    exit;
}

// Everything checks out — save the order.
mysqli_begin_transaction($conn);
try {
    mysqli_execute_query(
        $conn,
        "INSERT INTO orderinfo (customer_id, shipping_fee, status) VALUES (?, ?, 'processing')",
        [$customer['customer_id'], SHIPPING_FEE]
    );
    $order_id = mysqli_insert_id($conn);

    foreach ($cart_items as $row) {
        mysqli_execute_query(
            $conn,
            "INSERT INTO orderline (order_id, item_id, quantity, price_each) VALUES (?, ?, ?, ?)",
            [$order_id, $row['item_id'], $row['qty'], $row['price']]
        );
        mysqli_execute_query(
            $conn,
            "UPDATE stock SET quantity = quantity - ? WHERE item_id = ?",
            [$row['qty'], $row['item_id']]
        );
    }

    mysqli_commit($conn);
    $_SESSION['cart'] = [];

    header('Location: ' . BASE_URL . '/user/receipt.php?order_id=' . $order_id);
    exit;
} catch (mysqli_sql_exception $e) {
    mysqli_rollback($conn);
    set_alert('error', 'Something went wrong while placing your order. Please try again.');
    header('Location: ' . BASE_URL . '/view_cart.php');
    exit;
}