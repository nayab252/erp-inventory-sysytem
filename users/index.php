<?php
require '../includes/auth.php';
require_login();
require_permission('users', 'view');

$rows = $pdo->query("
  SELECT u.*, r.role_name, d.name AS department
  FROM users u
  LEFT JOIN roles r ON u.role_id = r.id
  LEFT JOIN departments d ON u.department_id = d.id
  ORDER BY u.id")->fetchAll(PDO::FETCH_ASSOC);

require '../includes/header.php';
?>
<h1>👥 Users</h1>
<div class="toolbar">
  <span></span>
  <?php if (can('users', 'add')): ?><a class="btn" href="form.php">+ Add User</a><?php endif; ?>
</div>

<table>
  <tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Department</th><th>Created</th><th>Actions</th></tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?= $r['id'] ?></td>
    <td><?= htmlspecialchars($r['full_name']) ?></td>
    <td><?= htmlspecialchars($r['email']) ?></td>
    <td><span class="badge green"><?= htmlspecialchars($r['role_name']) ?></span></td>
    <td><?= htmlspecialchars($r['department'] ?? '-') ?></td>
    <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
    <td class="actions">
      <?php if (can('users', 'edit')): ?><a class="btn small" href="form.php?id=<?= $r['id'] ?>">Edit</a><?php endif; ?>
      <?php if (can('users', 'delete') && $r['id'] != $_SESSION['user_id']): ?>
      <form method="POST" action="delete.php" onsubmit="return confirm('Delete this user?')">
        <input type="hidden" name="id" value="<?= $r['id'] ?>">
        <button class="btn small danger">Delete</button>
      </form>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php require '../includes/footer.php'; ?>