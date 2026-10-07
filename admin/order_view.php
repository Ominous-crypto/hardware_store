<?php
include('../includes/config.php');
include('../includes/guard_admin.php');
include('../includes/alert.php');

$order_id = (int)($_GET['order_id'] ?? $_POST['order_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = $_POST['status'] ?? '';
    $allowed = ['processing', 'delivered', 'canceled'];

    if (in_array($new_status, $allowed)) {

        $current = mysqli_execute_query($conn, "SELECT status FROM orderinfo WHERE order_id = ?", [$order_id]);
        $current_status = mysqli_fetch_assoc($current)['status'];

        if ($new_status === 'canceled' && $current_status !== 'canceled') {
            mysqli_begin_transaction($conn);
            try {
                $lines = mysqli_execute_query($conn, "SELECT item_id, quantity FROM orderline WHERE order_id = ?", [$order_id]);
                while ($line = mysqli_fetch_assoc($lines)) {
                    mysqli_execute_query($conn, "UPDATE stock SET quantity = quantity + ? WHERE item_id = ?", [$line['quantity'], $line['item_id']]);
                }
                mysqli_execute_query($conn, "UPDATE orderinfo SET status = ? WHERE order_id = ?", [$new_status, $order_id]);
                mysqli_commit($conn);
                set_alert('success', 'Order canceled and stock returned.');
            } catch (mysqli_sql_exception $e) {
                mysqli_rollback($conn);
                set_alert('error', 'Something went wrong. Please try again.');
            }
        } else {
            mysqli_execute_query($conn, "UPDATE orderinfo SET status = ? WHERE order_id = ?", [$new_status, $order_id]);
            set_alert('success', 'Order status updated.');
        }
    }

    header('Location: ' . BASE_URL . '/admin/order_view.php?order_id=' . $order_id);
    exit;
}

$order_result = mysqli_execute_query($conn, "
    SELECT o.*, c.fname, c.lname, c.address, c.contact
    FROM orderinfo o JOIN customer c ON c.customer_id = o.customer_id
    WHERE o.order_id = ?", [$order_id]);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    header('Location: ' . BASE_URL . '/admin/orders.php');
    exit;
}

$lines_result = mysqli_execute_query($conn, "
    SELECT ol.*, i.item_name
    FROM orderline ol JOIN item i ON i.item_id = ol.item_id
    WHERE ol.order_id = ?", [$order_id]);

$subtotal = 0;
$lines = [];
while ($line = mysqli_fetch_assoc($lines_result)) {
    $subtotal += $line['quantity'] * $line['price_each'];
    $lines[] = $line;
}

include('../includes/admin_header.php');
?>
<h1>Order #<?= $order['order_id'] ?></h1>

<p>
  <?= htmlspecialchars($order['fname'] . ' ' . $order['lname']) ?><br>
  <?= htmlspecialchars($order['address']) ?><br>
  <?= htmlspecialchars($order['contact']) ?>
</p>

<form method="post" action="" class="filter-bar">
  <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
  <select name="status">
    <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
    <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
    <option value="canceled" <?= $order['status'] === 'canceled' ? 'selected' : '' ?>>Canceled</option>
  </select>
  <button type="submit">Update Status</button>
</form>

<table class="cart-table">
  <tr><th>Item</th><th>Qty</th><th>Price</th><th>Line Total</th></tr>
  <?php foreach ($lines as $line): ?>
    <tr>
      <td><?= htmlspecialchars($line['item_name']) ?></td>
      <td><?= $line['quantity'] ?></td>
      <td>₱<?= number_format($line['price_each'], 2) ?></td>
      <td>₱<?= number_format($line['quantity'] * $line['price_each'], 2) ?></td>
    </tr>
  <?php endforeach; ?>
</table>

<p>Subtotal: ₱<?= number_format($subtotal, 2) ?></p>
<p>Shipping: ₱<?= number_format($order['shipping_fee'], 2) ?></p>
<p><strong>Total: ₱<?= number_format($subtotal + $order['shipping_fee'], 2) ?></strong></p>

<a href="<?= BASE_URL ?>/user/receipt.php?order_id=<?= $order['order_id'] ?>"><button>View/Print Receipt</button></a>

<?php include('../includes/footer.php'); ?>