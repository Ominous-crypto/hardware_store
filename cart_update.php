<?php
include('includes/config.php');
include('includes/alert.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$action  = $_POST['action'] ?? '';
$item_id = (int)($_POST['item_id'] ?? 0);

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($action === 'remove') {
    unset($_SESSION['cart'][$item_id]);
    set_alert('success', 'Item removed from cart.');
    header('Location: ' . BASE_URL . '/view_cart.php');
    exit;
}

if ($action === 'clear') {
    $_SESSION['cart'] = [];
    header('Location: ' . BASE_URL . '/view_cart.php');
    exit;
}

// add or update both need the current stock to enforce the limit
$stock_result = mysqli_execute_query($conn, "SELECT quantity FROM stock WHERE item_id = ?", [$item_id]);
$stock = mysqli_fetch_assoc($stock_result);

if (!$stock) {
    set_alert('error', 'That item no longer exists.');
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$available = (int)$stock['quantity'];
$requested = (int)($_POST['quantity'] ?? 1);

if ($action === 'add') {
    $current = $_SESSION['cart'][$item_id] ?? 0;
    $new_qty = min($current + $requested, $available);
    $_SESSION['cart'][$item_id] = max($new_qty, 0);

    if ($new_qty < $current + $requested) {
        set_alert('error', 'Only ' . $available . ' in stock — cart adjusted.');
    } else {
        set_alert('success', 'Added to cart.');
    }
    $redirect = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/index.php');
    header('Location: ' . $redirect);
    exit;
}

if ($action === 'update') {
    $new_qty = min(max($requested, 1), $available);
    $_SESSION['cart'][$item_id] = $new_qty;
    header('Location: ' . BASE_URL . '/view_cart.php');
    exit;
}

header('Location: ' . BASE_URL . '/index.php');
exit;