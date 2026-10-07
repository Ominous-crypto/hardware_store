<?php
include('../includes/config.php');
include('../includes/alert.php');

if (empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/user/login.php');
    exit;
}

$errors = [];

$result = mysqli_execute_query($conn, "SELECT * FROM customer WHERE user_id = ?", [$_SESSION['user_id']]);
$customer = mysqli_fetch_assoc($result);

$fname = $customer['fname'] ?? '';
$lname = $customer['lname'] ?? '';
$address = $customer['address'] ?? '';
$contact = $customer['contact'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = trim($_POST['fname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $contact = trim($_POST['contact'] ?? '');

    if ($fname === '' || $lname === '' || $address === '' || $contact === '') {
        $errors[] = 'Please fill in all fields.';
    }

    if (!$errors) {
        if ($customer) {
            mysqli_execute_query(
                $conn,
                "UPDATE customer SET fname = ?, lname = ?, address = ?, contact = ? WHERE user_id = ?",
                [$fname, $lname, $address, $contact, $_SESSION['user_id']]
            );
        } else {
            // Covers the edge case of an account with no profile row at all.
            mysqli_execute_query(
                $conn,
                "INSERT INTO customer (user_id, fname, lname, address, contact) VALUES (?, ?, ?, ?, ?)",
                [$_SESSION['user_id'], $fname, $lname, $address, $contact]
            );
        }

        set_alert('success', 'Profile updated.');

        $redirect = $_SESSION['redirect_after_login'] ?? (BASE_URL . '/user/profile.php');
        unset($_SESSION['redirect_after_login']);
        header('Location: ' . $redirect);
        exit;
    }
}

include('../includes/header.php');
?>
<div class="auth-box">
<h1>My Profile</h1>

<?php foreach ($errors as $error): ?>
  <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endforeach; ?>

<form method="post" action="">
  <label>First Name
    <input type="text" name="fname" value="<?= htmlspecialchars($fname) ?>" required>
  </label>
  <label>Last Name
    <input type="text" name="lname" value="<?= htmlspecialchars($lname) ?>" required>
  </label>
  <label>Address
    <input type="text" name="address" value="<?= htmlspecialchars($address) ?>" required>
  </label>
  <label>Contact Number
    <input type="text" name="contact" value="<?= htmlspecialchars($contact) ?>" required maxlength="11">
  </label>
  <button type="submit">Save Changes</button>
</form>
<p><a href="<?= BASE_URL ?>/user/change_password.php">Change Password</a></p>
</div>
<?php include('../includes/footer.php'); ?>