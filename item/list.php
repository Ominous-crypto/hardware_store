<?php
include('../includes/config.php');
include('../includes/alert.php');
include('../includes/guard_admin.php');

$search = trim($_GET['search'] ?? '');

$sql = "SELECT i.item_id, i.item_name, i.price, i.image_path, i.is_hidden, i.is_featured, c.category_name, s.quantity
        FROM item i
        JOIN category c ON c.category_id = i.category_id
        JOIN stock s ON s.item_id = i.item_id";

if ($search !== '') {
    $sql .= " WHERE i.item_name LIKE ?";
    $sql .= " ORDER BY i.item_name";
    $result = mysqli_execute_query($conn, $sql, ['%' . $search . '%']);
} else {
    $sql .= " ORDER BY i.item_name";
    $result = mysqli_query($conn, $sql);
}

include('../includes/admin_header.php');
?>
<h1>Items</h1>
<a href="<?= BASE_URL ?>/item/add.php"><button>+ Add Item</button></a>

<form method="get" action="" class="filter-bar">
  <input type="text" name="search" placeholder="Search items..." value="<?= htmlspecialchars($search) ?>">
  <button type="submit">Search</button>
  <?php if ($search !== ''): ?>
    <a href="<?= BASE_URL ?>/item/list.php"><button type="button">Clear</button></a>
  <?php endif; ?>
</form>

<table class="cart-table">
  <tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr>
  <?php while ($item = mysqli_fetch_assoc($result)): ?>
    <tr>
      <td><img src="<?= BASE_URL ?>/item/images/<?= htmlspecialchars($item['image_path']) ?>" alt="" style="width:50px;height:50px;object-fit:contain;"></td>
      <td><?= htmlspecialchars($item['item_name']) ?></td>
      <td><?= htmlspecialchars($item['category_name']) ?></td>
      <td>₱<?= number_format($item['price'], 2) ?></td>
      <td><?= $item['quantity'] ?></td>
      <td><?= $item['is_hidden'] ? 'Archived' : 'Active' ?><?= $item['is_featured'] ? ' ⭐' : '' ?></td>
      <td>
        <a href="<?= BASE_URL ?>/item/edit.php?item_id=<?= $item['item_id'] ?>"><button>Edit</button></a>
  <form method="post" action="<?= BASE_URL ?>/item/hide.php" class="inline-form" onsubmit="return confirm('<?= $item['is_hidden'] ? 'Restore this item to the shop?' : 'Archive this item? It will no longer appear in the shop, but stays in order history.' ?>');">
  <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
  <input type="hidden" name="hide" value="<?= $item['is_hidden'] ? 0 : 1 ?>">
  <button type="submit"><?= $item['is_hidden'] ? 'Restore' : 'Archive' ?></button>
</form>
</td>
    </tr>
  <?php endwhile; ?>
</table>

<?php include('../includes/footer.php'); ?>