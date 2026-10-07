<?php
include('../includes/config.php');
include('../includes/guard_admin.php');
include('../includes/alert.php');

$items = mysqli_query($conn, "
    SELECT i.item_id, i.item_name, s.quantity
    FROM item i JOIN stock s ON s.item_id = i.item_id
    WHERE i.is_hidden = 0
    ORDER BY i.item_name
");

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = (int)($_POST['item_id'] ?? 0);
    $amount = (int)($_POST['amount'] ?? 0);

    if ($item_id <= 0 || $amount <= 0) {
        $errors[] = 'Please select an item and enter a quantity greater than zero.';
    }

    if (!$errors) {
        mysqli_execute_query($conn, "UPDATE stock SET quantity = quantity + ? WHERE item_id = ?", [$amount, $item_id]);
        set_alert('success', 'Stock updated.');
        header('Location: ' . BASE_URL . '/admin/stock_in.php');
        exit;
    }
}

include('../includes/admin_header.php');
?>
<h1>Stock In</h1>

<?php foreach ($errors as $error): ?>
  <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endforeach; ?>

<form method="post" action="">
  <label>Item
    <select name="item_id" required>
      <option value="">-- Select --</option>
      <?php while ($item = mysqli_fetch_assoc($items)): ?>
        <option value="<?= $item['item_id'] ?>">
          <?= htmlspecialchars($item['item_name']) ?> (current: <?= $item['quantity'] ?>)
        </option>
      <?php endwhile; ?>
    </select>
  </label>
  <label>Quantity Received
    <input type="number" name="amount" min="1" required>
  </label>
  <button type="submit">Add to Stock</button>
</form>

<?php include('../includes/footer.php'); ?>