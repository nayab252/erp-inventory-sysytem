<?php
require '../includes/auth.php';
require_login();
require_permission('orders', 'view');

$rows = $pdo->query("
  SELECT o.*, c.name AS customer, u.full_name AS staff
  FROM sales_orders o
  LEFT JOIN customers c ON o.customer_id = c.id
  LEFT JOIN users u ON o.user_id = u.id
  ORDER BY o.id DESC")->fetchAll(PDO::FETCH_ASSOC);

require '../includes/header.php';
?>
<h1>🛒 Sales Orders</h1>
<div class="toolbar">
  <span></span>
  <?php if (can('orders', 'add')): ?><a class="btn" href="create.php">+ New Order</a><?php endif; ?>
</div>

<table>
  <tr><th>Order #</th><th>Date</th><th>Customer</th><th>Created by</th><th>Total</th><th>Status</th><th>Actions</th></tr>
  <?php foreach ($rows as $r):
      $color = ['completed' => 'green', 'cancelled' => 'red', 'pending' => 'orange'][$r['status']];
  ?>
  <tr>
    <td>#<?= $r['id'] ?></td>
    <td><?= date('d M Y, H:i', strtotime($r['order_date'])) ?></td>
    <td><?= htmlspecialchars($r['customer'] ?? 'N/A') ?></td>
    <td><?= htmlspecialchars($r['staff'] ?? '-') ?></td>
    <td>$<?= number_format($r['total'], 2) ?></td>
    <td><span class="badge <?= $color ?>"><?= ucfirst($r['status']) ?></span></td>
    <td class="actions"><a class="btn small" href="view.php?id=<?= $r['id'] ?>">View</a></td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="7">No orders yet.</td></tr><?php endif; ?>
</table>
<?php require '../includes/footer.php'; ?>