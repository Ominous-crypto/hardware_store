<?php
include('../includes/config.php');
include('../includes/alert.php');

if (empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/user/login.php');
    exit;
}

$order_id = (int)($_GET['order_id'] ?? 0);

$order_result = mysqli_execute_query($conn, "SELECT * FROM orderinfo WHERE order_id = ?", [$order_id]);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Ownership check: only the customer who placed it, or an admin, can view it.
if ($_SESSION['role'] !== 'admin') {
    $owner_check = mysqli_execute_query($conn, "SELECT customer_id FROM customer WHERE user_id = ?", [$_SESSION['user_id']]);
    $owner = mysqli_fetch_assoc($owner_check);
    if (!$owner || $owner['customer_id'] != $order['customer_id']) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

$lines_result = mysqli_execute_query($conn, "SELECT * FROM orderdetails WHERE order_id = ?", [$order_id]);
$lines = [];
$customer_info = null;
$subtotal = 0;

while ($row = mysqli_fetch_assoc($lines_result)) {
    $customer_info = $row;
    $subtotal += $row['line_total'];
    $lines[] = $row;
}

include('../includes/header.php');
?>
<h1>Order Receipt</h1>

<div class="receipt">
  <p><strong>Order #<?= $order_id ?></strong> — <?= htmlspecialchars($order['order_date']) ?></p>
  <p>Status: <?= htmlspecialchars(ucfirst($order['status'])) ?></p>

  <p>
    <?= htmlspecialchars($customer_info['fname'] . ' ' . $customer_info['lname']) ?><br>
    <?= htmlspecialchars($customer_info['address']) ?><br>
    <?= htmlspecialchars($customer_info['contact']) ?>
  </p>

  <table class="cart-table">
    <tr><th>Item</th><th>Qty</th><th>Price</th><th>Line Total</th></tr>
    <?php foreach ($lines as $line): ?>
      <tr>
        <td><?= htmlspecialchars($line['item_name']) ?></td>
        <td><?= $line['quantity'] ?></td>
        <td>₱<?= number_format($line['price_each'], 2) ?></td>
        <td>₱<?= number_format($line['line_total'], 2) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <p>Subtotal: ₱<?= number_format($subtotal, 2) ?></p>
  <p>Shipping: ₱<?= number_format($order['shipping_fee'], 2) ?></p>
  <p><strong>Total: ₱<?= number_format($subtotal + $order['shipping_fee'], 2) ?></strong></p>
</div>

<button onclick="window.print()">Print Receipt</button>
<a href="<?= BASE_URL ?>/index.php"><button>Back to Shop</button></a>

<?php include('../includes/footer.php'); ?>