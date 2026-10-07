<?php
include('../includes/config.php');
include('../includes/alert.php');
require_once '../includes/mailer.php';

$errors = [];
$username = '';
$submitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');

    if ($username === '') {
        $errors[] = 'Please enter your username.';
    }

    if (!$errors) {
        $result = mysqli_execute_query(
            $conn,
            "SELECT user_id, email FROM users WHERE username = ?",
            [$username]
        );

        $user = mysqli_fetch_assoc($result);

        // Show the same message whether the username exists or not.
        // This prevents users from checking which usernames are registered.
        if ($user && !empty($user['email'])) {

            // Generate a secure random token.
            $reset_token = bin2hex(random_bytes(32));

            // Save the token and expiration time.
            mysqli_execute_query(
                $conn,
                "UPDATE users
                 SET reset_token = ?,
                    reset_expires_at = DATE_ADD(NOW(), INTERVAL 30 MINUTE)
                 WHERE user_id = ?",
                [$reset_token, $user['user_id']]
            );

            // Build the password reset link.
            $reset_link = BASE_URL . '/user/reset_password.php?token=' . urlencode($reset_token);

            // Send the reset email using the existing PHPMailer setup.
            $email_sent = send_mail(
                $user['email'],
                $username,
                'Reset Your Sturdy Supplies Password',
                '<h2>Password Reset Request</h2>
                 <p>We received a request to reset the password for your Sturdy Supplies account.</p>
                 <p>Click the button below to create a new password:</p>
                 <p>
                    <a href="' . htmlspecialchars($reset_link) . '"
                       style="display:inline-block;
                              padding:10px 18px;
                              background:#333;
                              color:#fff;
                              text-decoration:none;
                              border-radius:5px;">
                       Reset Password
                    </a>
                 </p>
                 <p>This link will expire in <strong>30 minutes</strong>.</p>
                 <p>If you did not request a password reset, you can safely ignore this email.</p>'
            );

            // Log the result while testing.
            if (!$email_sent) {
                error_log('Password reset email failed to send for username: ' . $username);
            }
        }

        $submitted = true;
    }
}

include('../includes/header.php');
?>

<div class="auth-box">
<h1>Forgot Password</h1>

<?php if ($submitted): ?>

  <div class="alert alert-success">
    If that username exists, a password reset link has been sent to its registered email address.
    Please check your email.
  </div>

<?php else: ?>

  <?php foreach ($errors as $error): ?>
    <div class="alert alert-error">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endforeach; ?>

  <form method="post" action="">
    <label>Username
      <input
        type="text"
        name="username"
        value="<?= htmlspecialchars($username) ?>"
        required
      >
    </label>

    <button type="submit">Send Reset Link</button>
  </form>

<?php endif; ?>

<p>
  <a href="<?= BASE_URL ?>/user/login.php">Back to login</a>
</p>

</div>

<?php include('../includes/footer.php'); ?>
