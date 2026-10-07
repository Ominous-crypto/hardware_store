<?php
include('../includes/config.php');
include('../includes/alert.php');
include('../includes/guard_admin.php');

$result = mysqli_query($conn, "
    SELECT c.category_id, c.category_name, c.description, COUNT(i.item_id) AS item_count
    FROM category c
    LEFT JOIN item i ON i.category_id = c.category_id
    GROUP BY c.category_id, c.category_name, c.description
    ORDER BY c.category_name
");

include('../includes/admin_header.php');
?>
<h1>Categories</h1>
<a href="<?= BASE_URL ?>/admin/category_form.php"><button>+ Add Category</button></a>

<table class="cart-table">
  <tr><th>Name</th><th>Description</th><th>Items</th><th></th></tr>
  <?php while ($cat = mysqli_fetch_assoc($result)): ?>
    <tr>
      <td><?= htmlspecialchars($cat['category_name']) ?></td>
      <td><?= htmlspecialchars($cat['description']) ?></td>
      <td><?= $cat['item_count'] ?></td>
      <td>
        <a href="<?= BASE_URL ?>/admin/category_form.php?category_id=<?= $cat['category_id'] ?>"><button>Edit</button></a>
        <?php if ($cat['item_count'] == 0): ?>
          <form method="post" action="<?= BASE_URL ?>/admin/category_delete.php" class="inline-form" onsubmit="return confirm('Delete this category?');">
            <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
            <button type="submit">Delete</button>
          </form>
        <?php else: ?>
          <button disabled title="Category is in use">Delete</button>
        <?php endif; ?>
      </td>
    </tr>
  <?php endwhile; ?>
</table>

<?php include('../includes/footer.php'); ?>