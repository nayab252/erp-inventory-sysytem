<?php
require 'includes/header.php';

$totalProducts  = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$lowStock       = $pdo->query("SELECT COUNT(*) FROM products WHERE quantity <= reorder_level")->fetchColumn();
$totalSuppliers = $pdo->query("SELECT COUNT(*) FROM suppliers")->fetchColumn();
$totalSales     = $pdo->query("SELECT COALESCE(SUM(total),0) FROM sales_orders WHERE status='completed'")->fetchColumn();
?>
<h1>Dashboard</h1>
<div class="cards">
  <div class="card blue"><h3><?= $totalProducts ?></h3><p>Total Products</p></div>
  <div class="card red"><h3><?= $lowStock ?></h3><p>Low Stock Items</p></div>
  <div class="card green"><h3><?= $totalSuppliers ?></h3><p>Suppliers</p></div>
  <div class="card purple"><h3>$<?= number_format($totalSales,2) ?></h3><p>Total Sales</p></div>
</div>
<?php require 'includes/footer.php'; ?>