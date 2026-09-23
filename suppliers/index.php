<?php
require '../includes/auth.php';
require_login();
require_permission('suppliers', 'view');

$q = trim($_GET['q'] ?? '');
$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE name LIKE ? OR email LIKE ? OR phone LIKE ? ORDER BY id DESC");
$stmt->execute(["%$q%", "%$q%", "%$q%"]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

require '../includes/header.php';
?>
<h1>🚚 Suppliers</h1>
<div class="toolbar">
  <form method="GET">
    <input type="text" name="q" placeholder="Search name, email, phone" value="<?= htmlspecialchars($q) ?>">
    <button class="btn">Search</button>
  </form>
  <?php if (can('suppliers', 'add')): ?><a class="btn" href="form.php">+ Add Supplier</a><?php endif; ?>
</div>

<table>
  <tr><th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Address</th><th>Actions</th></tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?= $r['id'] ?></td>
    <td><?= htmlspecialchars($r['name']) ?></td>
    <td><?= htmlspecialchars($r['email']) ?></td>
    <td><?= htmlspecialchars($r['phone']) ?></td>
    <td><?= htmlspecialchars($r['address']) ?></td>
    <td class="actions">
      <?php if (can('suppliers', 'edit')): ?><a class="btn small" href="form.php?id=<?= $r['id'] ?>">Edit</a><?php endif; ?>
      <?php if (can('suppliers', 'delete')): ?>
      <form method="POST" action="delete.php" onsubmit="return confirm('Delete this supplier?')">
        <input type="hidden" name="id" value="<?= $r['id'] ?>">
        <button class="btn small danger">Delete</button>
      </form>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="6">No suppliers found.</td></tr><?php endif; ?>
</table>
<?php require '../includes/footer.php'; ?>