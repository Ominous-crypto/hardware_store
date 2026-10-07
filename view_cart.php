<?php
include('includes/config.php');
include('includes/alert.php');

$cart = $_SESSION['cart'] ?? [];
$cart_items = [];
$subtotal = 0;

if ($cart) {
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

    while ($row = mysqli_fetch_assoc($result)) {
        $qty = $cart[$row['item_id']];
        $line_total = $qty * $row['price'];
        $subtotal += $line_total;
        $cart_items[] = array_merge($row, ['qty' => $qty, 'line_total' => $line_total]);
    }
}

include('includes/header.php');
?>
<h1>Your Cart</h1>

<?php if (!$cart_items): ?>
  <p>Your cart is empty. <a href="<?= BASE_URL ?>/index.php">Continue shopping</a>.</p>
<?php else: ?>
  <table class="cart-table">
    <tr><th>Item</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr>
    <?php foreach ($cart_items as $row): ?>
      <tr>
        <td><?= htmlspecialchars($row['item_name']) ?></td>
        <td>₱<?= number_format($row['price'], 2) ?></td>
        <td>
          <form method="post" action="<?= BASE_URL ?>/cart_update.php" class="inline-form">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="item_id" value="<?= $row['item_id'] ?>">
            <input type="number" name="quantity" value="<?= $row['qty'] ?>" min="1" max="<?= $row['stock_qty'] ?>">
            <button type="submit">Update</button>
          </form>
        </td>
        <td>₱<?= number_format($row['line_total'], 2) ?></td>
        <td>
          <form method="post" action="<?= BASE_URL ?>/cart_update.php" class="inline-form">
            <input type="hidden" name="action" value="remove">
            <input type="hidden" name="item_id" value="<?= $row['item_id'] ?>">
            <button type="submit">Remove</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>

  <p>Subtotal: ₱<?= number_format($subtotal, 2) ?></p>
  <p>Shipping: ₱<?= number_format(SHIPPING_FEE, 2) ?></p>
  <p><strong>Total: ₱<?= number_format($subtotal + SHIPPING_FEE, 2) ?></strong></p>

  <form method="post" action="<?= BASE_URL ?>/cart_update.php">
    <input type="hidden" name="action" value="clear">
    <button type="submit">Empty Cart</button>
  </form>
  <a href="<?= BASE_URL ?>/checkout.php"><button>Checkout</button></a>
<?php endif; ?>

<?php include('includes/footer.php'); ?>