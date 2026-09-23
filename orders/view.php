<?php
require '../includes/auth.php';
require_login();
require_permission('orders', 'view');

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("
  SELECT o.*, c.name AS customer, c.phone, u.full_name AS staff
  FROM sales_orders o
  LEFT JOIN customers c ON o.customer_id = c.id
  LEFT JOIN users u ON o.user_id = u.id
  WHERE o.id=?");
$stmt->execute([$id]);
$o = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$o) die('Order not found.');

$stmt = $pdo->prepare("
  SELECT i.*, p.name, p.sku FROM order_items i
  JOIN products p ON i.product_id = p.id WHERE i.order_id=?");
$stmt->execute([$id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$color = ['completed' => 'green', 'cancelled' => 'red', 'pending' => 'orange'][$o['status']];
require '../includes/header.php';
?>
<h1>Order #<?= $o['id'] ?> <span class="badge <?= $color ?>"><?= ucfirst($o['status']) ?></span></h1>

<div class="form-box" style="max-width:100%;margin-bottom:20px">
  <p><b>Date:</b> <?= date('d M Y, H:i', strtotime($o['order_date'])) ?></p>
  <p><b>Customer:</b> <?= htmlspecialchars($o['customer'] ?? 'N/A') ?> <?= htmlspecialchars($o['phone'] ?? '') ?></p>
  <p><b>Created by:</b> <?= htmlspecialchars($o['staff'] ?? '-') ?></p>
</div>

<table>
  <tr><th>SKU</th><th>Product</th><th>Qty</th><th>Unit price</th><th>Subtotal</th></tr>
  <?php foreach ($items as $i): ?>
  <tr>
    <td><?= htmlspecialchars($i['sku']) ?></td>
    <td><?= htmlspecialchars($i['name']) ?></td>
    <td><?= $i['qty'] ?></td>
    <td>$<?= number_format($i['unit_price'], 2) ?></td>
    <td>$<?= number_format($i['qty'] * $i['unit_price'], 2) ?></td>
  </tr>
  <?php endforeach; ?>
  <tr><td colspan="4" align="right"><b>Total</b></td><td><b>$<?= number_format($o['total'], 2) ?></b></td></tr>
</table>
<br>
<div class="actions">
  <a class="btn gray" href="index.php">← Back</a>
  <button class="btn" onclick="window.print()">🖨 Print</button>
  <?php if ($o['status'] === 'completed' && can('orders', 'edit')): ?>
  <form method="POST" action="cancel.php" onsubmit="return confirm('Cancel this order and return stock?')">
    <input type="hidden" name="id" value="<?= $o['id'] ?>">
    <button class="btn danger">Cancel Order</button>
  </form>
  <?php endif; ?>
</div>
<?php require '../includes/footer.php'; ?>