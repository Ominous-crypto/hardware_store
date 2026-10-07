<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sturdy Supplies</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<header class="site-header">
  <a class="brand" href="<?= BASE_URL ?>/index.php">Sturdy Supplies</a>
  <nav>
    <a href="<?= BASE_URL ?>/index.php">Shop</a>
    <a href="<?= BASE_URL ?>/view_cart.php">Cart</a>
    <?php if (!empty($_SESSION['user_id'])): ?>
      <a href="<?= BASE_URL ?>/user/my_orders.php">My Orders</a>
      <a href="<?= BASE_URL ?>/user/profile.php">My Profile</a>
      <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="<?= BASE_URL ?>/admin/index.php">Admin</a>
      <?php endif; ?>
      <a href="<?= BASE_URL ?>/user/logout.php">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/user/login.php">Login</a>
      <a href="<?= BASE_URL ?>/user/register.php">Register</a>
    <?php endif; ?>
  </nav>
</header>
<main class="site-main">
<?php show_alert(); ?>