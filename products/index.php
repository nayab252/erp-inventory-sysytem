<?php
require '../includes/auth.php';
require_login();
require_permission('products', 'view');

$q = trim($_GET['q'] ?? '');
$stmt = $pdo->prepare("
  SELECT p.*, c.name AS category, s.name AS supplier
  FROM products p
  LEFT JOIN categories c ON p.category_id = c.id
  LEFT JOIN suppliers s ON p.supplier_id = s.id
  WHERE p.name LIKE ? OR p.sku LIKE ?
  ORDER BY p.id DESC");
$stmt->execute(["%$q%", "%$q%"]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

require '../includes/header.php';
?>
<h1>📋 Inventory</h1>
<div class="toolbar">
  <form method="GET">
    <input type="text" name="q" placeholder="Search name or SKU" value="<?= htmlspecialchars($q) ?>">
    <button class="btn">Search</button>
  </form>
  <?php if (can('products', 'add')): ?><a class="btn" href="form.php">+ Add Product</a><?php endif; ?>
</div>

<table>
  <tr><th>SKU</th><th>Name</th><th>Category</th><th>Supplier</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr>
  <?php foreach ($rows as $r):
      if ($r['quantity'] == 0)                        { $badge = ['Out of stock', 'red']; }
      elseif ($r['quantity'] <= $r['reorder_level'])  { $badge = ['Low stock', 'orange']; }
      else                                            { $badge = ['In stock', 'green']; }
  ?>
  <tr>
    <td><?= htmlspecialchars($r['sku']) ?></td>
    <td><?= htmlspecialchars($r['name']) ?></td>
    <td><?= htmlspecialchars($r['category'] ?? '-') ?></td>
    <td><?= htmlspecialchars($r['supplier'] ?? '-') ?></td>
    <td>$<?= number_format($r['price'], 2) ?></td>
    <td><?= $r['quantity'] ?></td>
    <td><span class="badge <?= $badge[1] ?>"><?= $badge[0] ?></span></td>
    <td class="actions">
      <?php if (can('products', 'edit')): ?><a class="btn small" href="form.php?id=<?= $r['id'] ?>">Edit</a><?php endif; ?>
      <?php if (can('products', 'delete')): ?>
      <form method="POST" action="delete.php" onsubmit="return confirm('Delete this product?')">
        <input type="hidden" name="id" value="<?= $r['id'] ?>">
        <button class="btn small danger">Delete</button>
      </form>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="8">No products found.</td></tr><?php endif; ?>
</table>
<?php require '../includes/footer.php'; ?>