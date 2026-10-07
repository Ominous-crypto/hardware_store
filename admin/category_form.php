<?php
include('../includes/config.php');
include('../includes/guard_admin.php');
include('../includes/alert.php');

$category_id = $_GET['category_id'] ?? $_POST['category_id'] ?? '';
$category_name = $description = '';
$errors = [];
$is_edit = false;

if ($category_id !== '') {
    $is_edit = true;
    $result = mysqli_execute_query($conn, "SELECT * FROM category WHERE category_id = ?", [$category_id]);
    $category = mysqli_fetch_assoc($result);
    if (!$category) {
        header('Location: ' . BASE_URL . '/admin/categories.php');
        exit;
    }
    $category_name = $category['category_name'];
    $description = $category['description'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = trim($_POST['category_name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($category_name === '') {
        $errors[] = 'Category name is required.';
    }

    if (!$errors) {
        if ($is_edit) {
            mysqli_execute_query(
                $conn,
                "UPDATE category SET category_name = ?, description = ? WHERE category_id = ?",
                [$category_name, $description, $category_id]
            );
            set_alert('success', 'Category updated.');
        } else {
            mysqli_execute_query(
                $conn,
                "INSERT INTO category (category_name, description) VALUES (?, ?)",
                [$category_name, $description]
            );
            set_alert('success', 'Category added.');
        }
        header('Location: ' . BASE_URL . '/admin/categories.php');
        exit;
    }
}

include('../includes/admin_header.php');
?>
<div class="auth-box">
<h1><?= $is_edit ? 'Edit' : 'Add' ?> Category</h1>

<?php foreach ($errors as $error): ?>
  <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endforeach; ?>

<form method="post" action="">
  <?php if ($is_edit): ?>
    <input type="hidden" name="category_id" value="<?= htmlspecialchars($category_id) ?>">
  <?php endif; ?>
  <label>Category Name
    <input type="text" name="category_name" value="<?= htmlspecialchars($category_name) ?>" required>
  </label>
  <label>Description
    <input type="text" name="description" value="<?= htmlspecialchars($description) ?>">
  </label>
  <button type="submit"><?= $is_edit ? 'Save Changes' : 'Add Category' ?></button>
</form>
</div>

<?php include('../includes/footer.php'); ?>