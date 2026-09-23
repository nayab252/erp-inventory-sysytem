<?php
require '../includes/auth.php';
require_login();
$id = (int)($_GET['id'] ?? 0);
require_permission('products', $id ? 'edit' : 'add');

$p = ['sku' => '', 'name' => '', 'category_id' => '', 'supplier_id' => '', 'price' => '', 'quantity' => 0, 'reorder_level' => 10];
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
    $stmt->execute([$id]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$p) die('Product not found.');
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$suppliers  = $pdo->query("SELECT * FROM suppliers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p = [
        'sku'           => trim($_POST['sku']),
        'name'          => trim($_POST['name']),
        'category_id'   => $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null,
        'supplier_id'   => $_POST['supplier_id'] !== '' ? (int)$_POST['supplier_id'] : null,
        'price'         => $_POST['price'],
        'quantity'      => (int)$_POST['quantity'],
        'reorder_level' => (int)$_POST['reorder_level'],
    ];
    if ($p['sku'] === '' || $p['name'] === '') {
        $error = 'SKU and name are required.';
    } elseif (!is_numeric($p['price']) || $p['price'] < 0) {
        $error = 'Price must be a valid number.';
    } elseif ($p['quantity'] < 0 || $p['reorder_level'] < 0) {
        $error = 'Quantity cannot be negative.';
    } else {
        try {
            if ($id) {
                $pdo->prepare("UPDATE products SET sku=?, name=?, category_id=?, supplier_id=?, price=?, quantity=?, reorder_level=? WHERE id=?")
                    ->execute([$p['sku'], $p['name'], $p['category_id'], $p['supplier_id'], $p['price'], $p['quantity'], $p['reorder_level'], $id]);
                set_flash('Product updated.');
            } else {
                $pdo->prepare("INSERT INTO products (sku,name,category_id,supplier_id,price,quantity,reorder_level) VALUES (?,?,?,?,?,?,?)")
                    ->execute([$p['sku'], $p['name'], $p['category_id'], $p['supplier_id'], $p['price'], $p['quantity'], $p['reorder_level']]);
                set_flash('Product added.');
            }
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $error = ($e->getCode() == 23000) ? 'This SKU already exists. Use a different one.' : 'Database error.';
        }
    }
}

require '../includes/header.php';
?>
<h1><?= $id ? 'Edit' : 'Add' ?> Product</h1>
<div class="form-box">
  <?php if ($error): ?><div class="flash error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="POST">
    <label>SKU (unique code) *</label>
    <input type="text" name="sku" value="<?= htmlspecialchars($p['sku']) ?>" required>
    <label>Product name *</label>
    <input type="text" name="name" value="<?= htmlspecialchars($p['name']) ?>" required>

    <label>Category</label>
    <select name="category_id">
      <option value="">-- none --</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= $c['id'] ?>" <?= $p['category_id'] == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
      <?php endforeach; ?>
    </select>

    <label>Supplier</label>
    <select name="supplier_id">
      <option value="">-- none --</option>
      <?php foreach ($suppliers as $s): ?>
        <option value="<?= $s['id'] ?>" <?= $p['supplier_id'] == $s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?></option>
      <?php endforeach; ?>
    </select>

    <label>Price *</label>
    <input type="number" step="0.01" min="0" name="price" value="<?= htmlspecialchars($p['price']) ?>" required>
    <label>Quantity in stock</label>
    <input type="number" min="0" name="quantity" value="<?= (int)$p['quantity'] ?>">
    <label>Reorder level (low-stock warning)</label>
    <input type="number" min="0" name="reorder_level" value="<?= (int)$p['reorder_level'] ?>">

    <button class="btn">Save</button>
    <a class="btn gray" href="index.php">Cancel</a>
  </form>
</div>
<?php require '../includes/footer.php'; ?>