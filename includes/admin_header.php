<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Sturdy Supplies</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<header class="site-header">
  <a class="brand" href="<?= BASE_URL ?>/admin/index.php">Sturdy Supplies Admin</a>
  <nav>
    <a href="<?= BASE_URL ?>/admin/index.php">Dashboard</a>
    <a href="<?= BASE_URL ?>/item/list.php">Items</a>
    <a href="<?= BASE_URL ?>/admin/categories.php">Categories</a>
    <a href="<?= BASE_URL ?>/admin/low_stock.php">Low Stock</a>
    <a href="<?= BASE_URL ?>/admin/orders.php">Orders</a>
    <a href="<?= BASE_URL ?>/admin/users.php">Users</a>
    <a href="<?= BASE_URL ?>/index.php">View Shop</a>
    <a href="<?= BASE_URL ?>/user/logout.php">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
  </nav>
</header>
<main class="site-main admin-main">
<?php show_alert(); ?>