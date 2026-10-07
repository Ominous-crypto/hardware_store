<?php
include('../includes/config.php');
include('../includes/alert.php');
include('../includes/guard_admin.php');

$status_filter = $_GET['status'] ?? '';

$sql = "SELECT o.order_id, o.order_date, o.status, o.shipping_fee, c.fname, c.lname
        FROM orderinfo o
        JOIN customer c ON c.customer_id = o.customer_id";
$params = [];

if ($status_filter !== '') {
    $sql .= " WHERE o.status = ?";
    $params[] = $status_filter;
}
$sql .= " ORDER BY o.order_date DESC";

if ($params) {
    $result = mysqli_execute_query($conn, $sql, $params);
} else {
    $result = mysqli_query($conn, $sql);
}

include('../includes/admin_header.php');
?>
<h1>Orders</h1>

<form method="get" action="" class="filter-bar">
  <select name="status" onchange="this.form.submit()">
    <option value="">All Statuses</option>
    <option value="processing" <?= $status_filter === 'processing' ? 'selected' : '' ?>>Processing</option>
    <option value="delivered" <?= $status_filter === 'delivered' ? 'selected' : '' ?>>Delivered</option>
    <option value="canceled" <?= $status_filter === 'canceled' ? 'selected' : '' ?>>Canceled</option>
  </select>
</form>

<table class="cart-table">
  <tr><th>Order #</th><th>Customer</th><th>Date</th><th>Status</th><th></th></tr>
  <?php while ($order = mysqli_fetch_assoc($result)): ?>
    <tr>
      <td>#<?= $order['order_id'] ?></td>
      <td><?= htmlspecialchars($order['fname'] . ' ' . $order['lname']) ?></td>
      <td><?= htmlspecialchars($order['order_date']) ?></td>
      <td><?= htmlspecialchars(ucfirst($order['status'])) ?></td>
      <td><a href="<?= BASE_URL ?>/admin/order_view.php?order_id=<?= $order['order_id'] ?>"><button>View</button></a></td>
    </tr>
  <?php endwhile; ?>
</table>

<?php include('../includes/footer.php'); ?>