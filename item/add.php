<?php
include('../includes/config.php');
include('../includes/guard_admin.php');
include('../includes/alert.php');

$categories = mysqli_query($conn, "SELECT category_id, category_name FROM category ORDER BY category_name");
$errors = [];
$item_name = $description = $price = $quantity = $reorder_level = '';
$category_id = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name     = trim($_POST['item_name'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $category_id   = $_POST['category_id'] ?? '';
    $price         = $_POST['price'] ?? '';
    $quantity      = $_POST['quantity'] ?? '';
    $reorder_level = $_POST['reorder_level'] ?? '5';

    if ($item_name === '' || $category_id === '' || $price === '' || $quantity === '') {
        $errors[] = 'Please fill in all required fields.';
    }

    $image_path = 'default.png';
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Image must be a jpg, jpeg, png, or webp file.';
        } else {
            $image_path = 'item_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
            $dest = __DIR__ . '/images/' . $image_path;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $errors[] = 'Failed to upload image. Please try again.';
            }
        }
    }

    if (!$errors) {
        mysqli_begin_transaction($conn);
        try {
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            mysqli_execute_query(
                $conn,
                "INSERT INTO item (category_id, item_name, description, price, image_path, is_featured) VALUES (?, ?, ?, ?, ?, ?)",
                [$category_id, $item_name, $description, $price, $image_path, $is_featured]
            );
            $item_id = mysqli_insert_id($conn);
            mysqli_execute_query(
                $conn,
                "INSERT INTO stock (item_id, quantity, reorder_level) VALUES (?, ?, ?)",
                [$item_id, $quantity, $reorder_level]
            );
            mysqli_commit($conn);

            set_alert('success', 'Item added.');
            header('Location: ' . BASE_URL . '/item/list.php');
            exit;
        } catch (mysqli_sql_exception $e) {
            mysqli_rollback($conn);
            $errors[] = 'Something went wrong. Please try again.';
        }
    }
}

include('../includes/admin_header.php');
?>
<h1>Add Item</h1>

<?php foreach ($errors as $error): ?>
  <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endforeach; ?>

<form method="post" action="" enctype="multipart/form-data">
  <label>Item Name
    <input type="text" name="item_name" value="<?= htmlspecialchars($item_name) ?>" required>
  </label>
  <label>Category
    <select name="category_id" required>
      <option value="">-- Select --</option>
      <?php mysqli_data_seek($categories, 0); while ($cat = mysqli_fetch_assoc($categories)): ?>
        <option value="<?= $cat['category_id'] ?>" <?= ($category_id == $cat['category_id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($cat['category_name']) ?>
        </option>
      <?php endwhile; ?>
    </select>
  </label>
  <label>Description
    <input type="text" name="description" value="<?= htmlspecialchars($description) ?>">
  </label>
  <label>Price
    <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($price) ?>" required>
  </label>
  <label>Starting Quantity
    <input type="number" name="quantity" value="<?= htmlspecialchars($quantity) ?>" required>
  </label>
  <label>Reorder Level
    <input type="number" name="reorder_level" value="<?= htmlspecialchars($reorder_level ?: 5) ?>">
  </label>
  <label>Image
    <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
  </label>
  <label>
    <input type="checkbox" name="is_featured" value="1" style="width:auto;display:inline-block;"> Feature this item in the shop carousel
  </label>
  <button type="submit">Add Item</button>
</form>

<?php include('../includes/footer.php'); ?>