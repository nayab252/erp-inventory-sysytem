<?php require_once __DIR__ . '/auth.php'; require_login(); ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>ERP System</title>
  <link rel="stylesheet" href="/erp/assets/css/style.css">
</head>
<body>
<aside class="sidebar">
  <h2>📦 ERP</h2>
  <a href="/erp/dashboard.php">🏠 Dashboard</a>
  <?php if (can('products','view')): ?><a href="/erp/products/index.php">📋 Inventory</a><?php endif; ?>
  <?php if (can('suppliers','view')): ?><a href="/erp/suppliers/index.php">🚚 Suppliers</a><?php endif; ?>
  <?php if (can('orders','view')): ?><a href="/erp/orders/index.php">🛒 Sales Orders</a><?php endif; ?>
    <?php if (can('users','view')): ?><a href="/erp/users/index.php">👥 Users</a><?php endif; ?>
  <?php if (can('users','edit')): ?>
    <a href="/erp/users/permissions.php">🔐 Permissions</a>
    <a href="/erp/users/departments.php">🏢 Departments</a>
  <?php endif; ?>
  <a href="/erp/logout.php">🚪 Logout</a>
</aside>
<main class="content">
  <div class="topbar">Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></div>
  <div class="topbar">Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></div>
  <?php show_flash(); ?>
  