<?php
require '../includes/auth.php';
require_login();
$id = (int)($_GET['id'] ?? 0);
require_permission('suppliers', $id ? 'edit' : 'add');

$s = ['name' => '', 'email' => '', 'phone' => '', 'address' => ''];
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id=?");
    $stmt->execute([$id]);
    $s = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$s) die('Supplier not found.');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $s = [
        'name'    => trim($_POST['name']),
        'email'   => trim($_POST['email']),
        'phone'   => trim($_POST['phone']),
        'address' => trim($_POST['address']),
    ];
    if ($s['name'] === '') {
        $error = 'Supplier name is required.';
    } elseif ($s['email'] !== '' && !filter_var($s['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email.';
    } else {
        if ($id) {
            $pdo->prepare("UPDATE suppliers SET name=?, email=?, phone=?, address=? WHERE id=?")
                ->execute([$s['name'], $s['email'], $s['phone'], $s['address'], $id]);
            set_flash('Supplier updated.');
        } else {
            $pdo->prepare("INSERT INTO suppliers (name,email,phone,address) VALUES (?,?,?,?)")
                ->execute([$s['name'], $s['email'], $s['phone'], $s['address']]);
            set_flash('Supplier added.');
        }
        header('Location: index.php');
        exit;
    }
}

require '../includes/header.php';
?>
<h1><?= $id ? 'Edit' : 'Add' ?> Supplier</h1>
<div class="form-box">
  <?php if ($error): ?><div class="flash error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="POST">
    <label>Name *</label>
    <input type="text" name="name" value="<?= htmlspecialchars($s['name']) ?>" required>
    <label>Email</label>
    <input type="text" name="email" value="<?= htmlspecialchars($s['email']) ?>">
    <label>Phone</label>
    <input type="text" name="phone" value="<?= htmlspecialchars($s['phone']) ?>">
    <label>Address</label>
    <textarea name="address" rows="3"><?= htmlspecialchars($s['address']) ?></textarea>
    <button class="btn">Save</button>
    <a class="btn gray" href="index.php">Cancel</a>
  </form>
</div>
<?php require '../includes/footer.php'; ?>