<?php
include('../includes/config.php');
include('../includes/alert.php');
include('../includes/guard_admin.php');

$result = mysqli_query($conn, "
    SELECT i.item_id, i.item_name, s.quantity, s.reorder_level
    FROM item i JOIN stock s ON s.item_id = i.item_id
    WHERE s.quantity <= s.reorder_level AND i.is_hidden = 0
    ORDER BY s.quantity ASC
");

include('../includes/admin_header.php');
?>
<h1>Low Stock Items</h1>

<table class="cart-table">
  <tr><th>Item</th><th>Current Stock</th><th>Reorder Level</th><th></th></tr>
  <?php while ($item = mysqli_fetch_assoc($result)): ?>
    <tr>
      <td><?= htmlspecialchars($item['item_name']) ?></td>
      <td><?= $item['quantity'] ?></td>
      <td><?= $item['reorder_level'] ?></td>
      <td><a href="<?= BASE_URL ?>/admin/stock_in.php"><button>Restock</button></a></td>
    </tr>
  <?php endwhile; ?>
</table>

<?php if (mysqli_num_rows($result) === 0): ?>
  <p><em>No items are currently low on stock.</em></p>
<?php endif; ?>

<?php include('../includes/footer.php'); ?>