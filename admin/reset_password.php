<?php
include('../includes/config.php');
include('../includes/guard_admin.php');
include('../includes/alert.php');

$user_id = (int)($_GET['user_id'] ?? $_POST['user_id'] ?? 0);

$user_result = mysqli_execute_query($conn, "
    SELECT u.user_id, u.username, c.fname, c.lname
    FROM users u JOIN customer c ON c.user_id = u.user_id
    WHERE u.user_id = ?", [$user_id]);
$user = mysqli_fetch_assoc($user_result);

if (!$user) {
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

$temp_password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $temp_password = substr(bin2hex(random_bytes(4)), 0, 8);
    $hash = password_hash($temp_password, PASSWORD_DEFAULT);

    mysqli_execute_query(
        $conn,
        "UPDATE users SET password = ?, reset_requested = 0 WHERE user_id = ?",
        [$hash, $user_id]
    );

    set_alert('success', 'Password reset. Give the customer their new temporary password below.');
}

include('../includes/admin_header.php');
?>
<div class="auth-box">
<h1>Reset Password</h1>

<p>Customer: <?= htmlspecialchars($user['fname'] . ' ' . $user['lname']) ?> (<?= htmlspecialchars($user['username']) ?>)</p>

<?php if ($temp_password): ?>
  <div class="alert alert-success">
    New temporary password: <strong><?= htmlspecialchars($temp_password) ?></strong><br>
    Give this to the customer directly. They should log in and it's recommended they change it afterward.
  </div>
<?php else: ?>
  <form method="post" action="" onsubmit="return confirm('Generate a new temporary password for this customer?');">
    <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
    <button type="submit">Generate Temporary Password</button>
  </form>
<?php endif; ?>

<a href="<?= BASE_URL ?>/admin/users.php"><button>Back to Customers</button></a>
</div>
<?php include('../includes/footer.php'); ?>