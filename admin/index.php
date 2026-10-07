<?php
include('../includes/config.php');
include('../includes/alert.php');
include('../includes/guard_admin.php');

$order_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM orderinfo WHERE status = 'processing'"))['c'];
$low_stock_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM stock WHERE quantity <= reorder_level"))['c'];
$item_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM item WHERE is_hidden = 0"))['c'];
$reset_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM users WHERE reset_requested = 1"))['c'];

include('../includes/admin_header.php');
?>
<h1>Admin Dashboard</h1>

<div class="dash-grid">
  <a href="<?= BASE_URL ?>/admin/orders.php" class="dash-card">
    <h2><?= $order_count ?></h2>
    <p>Orders Processing</p>
  </a>
  <a href="<?= BASE_URL ?>/admin/low_stock.php" class="dash-card">
    <h2><?= $low_stock_count ?></h2>
    <p>Low Stock Items</p>
  </a>
  <a href="<?= BASE_URL ?>/item/list.php" class="dash-card">
    <h2><?= $item_count ?></h2>
    <p>Active Items</p>
  </a>
  <a href="<?= BASE_URL ?>/admin/users.php" class="dash-card">
    <h2><?= $reset_count ?></h2>
    <p>Password Reset Requests</p>
  </a>
</div>

<?php include('../includes/footer.php'); ?>