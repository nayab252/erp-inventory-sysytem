<?php
require '../includes/auth.php';
require_login();
require_permission('users', 'edit');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add'])) {
        $name = trim($_POST['name']);
        if ($name !== '') {
            $pdo->prepare("INSERT INTO departments (name) VALUES (?)")->execute([$name]);
            set_flash('Department added.');
        }
    } elseif (isset($_POST['delete_id'])) {
        try {
            $pdo->prepare("DELETE FROM departments WHERE id=?")->execute([(int)$_POST['delete_id']]);
            set_flash('Department deleted.');
        } catch (PDOException $e) {
            set_flash('Cannot delete: users belong to this department.', 'error');
        }
    }
    header('Location: departments.php');
    exit;
}

$rows = $pdo->query("
  SELECT d.*, COUNT(u.id) AS staff
  FROM departments d LEFT JOIN users u ON u.department_id = d.id
  GROUP BY d.id ORDER BY d.name")->fetchAll(PDO::FETCH_ASSOC);

require '../includes/header.php';
?>
<h1>🏢 Departments</h1>
<div class="toolbar">
  <form method="POST">
    <input type="text" name="name" placeholder="New department name" required>
    <button class="btn" name="add">+ Add</button>
  </form>
</div>

<table>
  <tr><th>#</th><th>Department</th><th>Staff</th><th>Actions</th></tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><?= $r['id'] ?></td>
    <td><?= htmlspecialchars($r['name']) ?></td>
    <td><?= $r['staff'] ?></td>
    <td class="actions">
      <form method="POST" onsubmit="return confirm('Delete this department?')">
        <input type="hidden" name="delete_id" value="<?= $r['id'] ?>">
        <button class="btn small danger">Delete</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php require '../includes/footer.php'; ?>