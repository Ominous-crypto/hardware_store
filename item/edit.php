<?php
include('../includes/config.php');
include('../includes/guard_admin.php');
include('../includes/alert.php');

$item_id = (int)($_GET['item_id'] ?? $_POST['item_id'] ?? 0);

$item_result = mysqli_execute_query(
    $conn,
    "SELECT i.*, s.quantity, s.reorder_level FROM item i JOIN stock s ON s.item_id = i.item_id WHERE i.item_id = ?",
    [$item_id]
);
$item = mysqli_fetch_assoc($item_result);

if (!$item) {
    header('Location: ' . BASE_URL . '/item/list.php');
    exit;
}

$categories = mysqli_query($conn, "SELECT category_id, category_name FROM category ORDER BY category_name");
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name     = trim($_POST['item_name'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $category_id   = $_POST['category_id'] ?? '';
    $price         = $_POST['price'] ?? '';
    $reorder_level = $_POST['reorder_level'] ?? '5';

    if ($item_name === '' || $category_id === '' || $price === '') {
        $errors[] = 'Please fill in all required fields.';
    }

    // Keep the existing photo unless a new one was uploaded.
    $image_path = $item['image_path'];
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Image must be a jpg, jpeg, png, or webp file.';
        } else {
            $new_image = 'item_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
            $dest = __DIR__ . '/images/' . $new_image;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $image_path = $new_image;
            } else {
                $errors[] = 'Failed to upload image. Please try again.';
            }
        }
    }

    if (!$errors) {
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        mysqli_execute_query(
            $conn,
            "UPDATE item SET category_id = ?, item_name = ?, description = ?, price = ?, image_path = ?, is_featured = ? WHERE item_id = ?",
            [$category_id, $item_name, $description, $price, $image_path, $is_featured, $item_id]
        );
        mysqli_execute_query(
            $conn,
            "UPDATE stock SET reorder_level = ? WHERE item_id = ?",
            [$reorder_level, $item_id]
        );

        set_alert('success', 'Item updated.');
        header('Location: ' . BASE_URL . '/item/list.php');
        exit;
    }

    // Keep what the admin typed even if there was an error.
    $item = array_merge($item, compact('item_name', 'description', 'category_id', 'price', 'reorder_level'));
}

include('../includes/admin_header.php');
?>
<h1>Edit Item</h1>

<?php foreach ($errors as $error): ?>
  <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endforeach; ?>

<p><img src="<?= BASE_URL ?>/item/images/<?= htmlspecialchars($item['image_path']) ?>" alt="" style="width:100px;height:100px;object-fit:contain;"></p>

<form method="post" action="" enctype="multipart/form-data">
  <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
  <label>Item Name
    <input type="text" name="item_name" value="<?= htmlspecialchars($item['item_name']) ?>" required>
  </label>
  <label>Category
    <select name="category_id" required>
      <?php mysqli_data_seek($categories, 0); while ($cat = mysqli_fetch_assoc($categories)): ?>
        <option value="<?= $cat['category_id'] ?>" <?= ($item['category_id'] == $cat['category_id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($cat['category_name']) ?>
        </option>
      <?php endwhile; ?>
    </select>
  </label>
  <label>Description
    <input type="text" name="description" value="<?= htmlspecialchars($item['description']) ?>">
  </label>
  <label>Price
    <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($item['price']) ?>" required>
  </label>
  <label>Reorder Level
    <input type="number" name="reorder_level" value="<?= htmlspecialchars($item['reorder_level']) ?>">
  </label>
  <label>Replace Image (leave blank to keep current)
    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
  </label>
  <label>
    <input type="checkbox" name="is_featured" value="1" style="width:auto;display:inline-block;" <?= $item['is_featured'] ? 'checked' : '' ?>> Feature this item in the shop carousel
  </label>
  <button type="submit">Save Changes</button>
</form>

<p><em>Stock quantity is managed separately on the Stock page.</em></p>

<?php include('../includes/footer.php'); ?>