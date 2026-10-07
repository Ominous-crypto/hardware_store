<?php
include('../includes/config.php');
include('../includes/alert.php');
include('../includes/guard_admin.php');

$result = mysqli_query($conn, "
    SELECT u.user_id, u.username, u.reset_requested, c.fname, c.lname, c.address, c.contact
    FROM users u
    JOIN customer c ON c.user_id = u.user_id
    WHERE u.role = 'customer'
    ORDER BY u.reset_requested DESC, c.lname
");

include('../includes/admin_header.php');
?>
<h1>Customers</h1>

<table class="cart-table">
  <tr><th>Name</th><th>Username</th><th>Contact</th><th>Status</th><th></th></tr>
  <?php while ($user = mysqli_fetch_assoc($result)): ?>
    <tr>
      <td><?= htmlspecialchars($user['fname'] . ' ' . $user['lname']) ?></td>
      <td><?= htmlspecialchars($user['username']) ?></td>
      <td><?= htmlspecialchars($user['contact']) ?></td>
      <td><?= $user['reset_requested'] ? '🔑 Reset Requested' : '—' ?></td>
      <td>
        <?php if ($user['reset_requested']): ?>
          <a href="<?= BASE_URL ?>/admin/reset_password.php?user_id=<?= $user['user_id'] ?>"><button>Reset Password</button></a>
        <?php endif; ?>
      </td>
    </tr>
  <?php endwhile; ?>
</table>

<?php include('../includes/footer.php'); ?>