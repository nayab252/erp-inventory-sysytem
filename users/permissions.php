<?php
require '../includes/auth.php';
require_login();
require_permission('users', 'edit');

$modules = [
    'products'  => '📋 Inventory',
    'suppliers' => '🚚 Suppliers',
    'orders'    => '🛒 Sales Orders',
    'users'     => '👥 Users',
];
$actions = ['view', 'add', 'edit', 'delete'];

$roles  = $pdo->query("SELECT * FROM roles ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$roleId = (int)($_GET['role_id'] ?? $_POST['role_id'] ?? 0);
if (!$roleId) {
    $roleId = (int)($roles[1]['id'] ?? $roles[0]['id']);   // default: first non-admin role
}
$isAdminRole = ($roleId === 1);

// Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isAdminRole) {
    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM permissions WHERE role_id=?")->execute([$roleId]);
        $ins = $pdo->prepare("INSERT INTO permissions (role_id,module,can_view,can_add,can_edit,can_delete) VALUES (?,?,?,?,?,?)");

        foreach ($modules as $m => $label) {
            $add    = isset($_POST['perm'][$m]['add'])    ? 1 : 0;
            $edit   = isset($_POST['perm'][$m]['edit'])   ? 1 : 0;
            $delete = isset($_POST['perm'][$m]['delete']) ? 1 : 0;
            $view   = (isset($_POST['perm'][$m]['view']) || $add || $edit || $delete) ? 1 : 0;
            $ins->execute([$roleId, $m, $view, $add, $edit, $delete]);
        }
        $pdo->commit();
        set_flash('Permissions saved.');
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        set_flash('Could not save permissions.', 'error');
    }
    header("Location: permissions.php?role_id=$roleId");
    exit;
}

// Load current permissions for this role
$stmt = $pdo->prepare("SELECT * FROM permissions WHERE role_id=?");
$stmt->execute([$roleId]);
$current = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $current[$row['module']] = $row;
}

require '../includes/header.php';
?>
<h1>🔐 Role Permissions</h1>

<form method="GET" class="role-picker">
  <label><b>Choose role:</b></label>
  <select name="role_id" onchange="this.form.submit()">
    <?php foreach ($roles as $r): ?>
      <option value="<?= $r['id'] ?>" <?= $roleId == $r['id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['role_name']) ?></option>
    <?php endforeach; ?>
  </select>
</form>

<?php if ($isAdminRole): ?>
  <div class="info">🔒 The Admin role always has full access and cannot be changed.</div>
<?php endif; ?>

<form method="POST">
  <input type="hidden" name="role_id" value="<?= $roleId ?>">
  <table class="perm-table">
    <tr><th>Module</th><th>View</th><th>Add</th><th>Edit</th><th>Delete</th></tr>
    <?php foreach ($modules as $m => $label): ?>
    <tr>
      <td><?= $label ?></td>
      <?php foreach ($actions as $a): ?>
      <td>
        <input type="checkbox" name="perm[<?= $m ?>][<?= $a ?>]"
          <?= !empty($current[$m]['can_' . $a]) || $isAdminRole ? 'checked' : '' ?>
          <?= $isAdminRole ? 'disabled' : '' ?>>
      </td>
      <?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
  </table>
  <br>
  <?php if (!$isAdminRole): ?><button class="btn">💾 Save Permissions</button><?php endif; ?>
</form>
<?php require '../includes/footer.php'; ?>