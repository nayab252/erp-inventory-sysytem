<?php
require '../includes/auth.php';
require_login();
$id = (int)($_GET['id'] ?? 0);
require_permission('users', $id ? 'edit' : 'add');

$u = ['full_name' => '', 'email' => '', 'role_id' => '', 'department_id' => ''];
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$id]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$u) die('User not found.');
}
$isSelf = ($id === (int)$_SESSION['user_id']);

$roles = $pdo->query("SELECT * FROM roles ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$depts = $pdo->query("SELECT * FROM departments ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u['full_name']     = trim($_POST['full_name']);
    $u['email']         = trim($_POST['email']);
    $u['role_id']       = $isSelf ? $u['role_id'] : (int)$_POST['role_id'];   // you cannot change your own role
    $u['department_id'] = $_POST['department_id'] !== '' ? (int)$_POST['department_id'] : null;
    $password           = $_POST['password'];

    if ($u['full_name'] === '' || $u['email'] === '') {
        $error = 'Name and email are required.';
    } elseif (!filter_var($u['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email.';
    } elseif (!$id && strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($id && $password !== '' && strlen($password) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif (!$u['role_id']) {
        $error = 'Please choose a role.';
    } else {
        try {
            if ($id) {
                $pdo->prepare("UPDATE users SET full_name=?, email=?, role_id=?, department_id=? WHERE id=?")
                    ->execute([$u['full_name'], $u['email'], $u['role_id'], $u['department_id'], $id]);
                if ($password !== '') {
                    $pdo->prepare("UPDATE users SET password=? WHERE id=?")
                        ->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
                }
                if ($isSelf) $_SESSION['full_name'] = $u['full_name'];
                set_flash('User updated.');
            } else {
                $pdo->prepare("INSERT INTO users (full_name,email,password,role_id,department_id) VALUES (?,?,?,?,?)")
                    ->execute([$u['full_name'], $u['email'], password_hash($password, PASSWORD_DEFAULT), $u['role_id'], $u['department_id']]);
                set_flash('User created.');
            }
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $error = ($e->getCode() == 23000) ? 'This email is already used.' : 'Database error.';
        }
    }
}

require '../includes/header.php';
?>
<h1><?= $id ? 'Edit' : 'Add' ?> User</h1>
<div class="form-box">
  <?php if ($error): ?><div class="flash error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="POST">
    <label>Full name *</label>
    <input type="text" name="full_name" value="<?= htmlspecialchars($u['full_name']) ?>" required>
    <label>Email *</label>
    <input type="text" name="email" value="<?= htmlspecialchars($u['email']) ?>" required>

    <label>Password <?= $id ? '(leave empty to keep the old one)' : '*' ?></label>
    <input type="password" name="password" <?= $id ? '' : 'required' ?>>

    <label>Role *</label>
    <select name="role_id" <?= $isSelf ? 'disabled' : '' ?>>
      <option value="">-- choose --</option>
      <?php foreach ($roles as $r): ?>
        <option value="<?= $r['id'] ?>" <?= $u['role_id'] == $r['id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['role_name']) ?></option>
      <?php endforeach; ?>
    </select>
    <?php if ($isSelf): ?><small>You cannot change your own role.</small><br><?php endif; ?>

    <label>Department</label>
    <select name="department_id">
      <option value="">-- none --</option>
      <?php foreach ($depts as $d): ?>
        <option value="<?= $d['id'] ?>" <?= $u['department_id'] == $d['id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['name']) ?></option>
      <?php endforeach; ?>
    </select>

    <button class="btn">Save</button>
    <a class="btn gray" href="index.php">Cancel</a>
  </form>
</div>
<?php require '../includes/footer.php'; ?>