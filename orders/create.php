<?php
require '../includes/auth.php';
require_login();
require_permission('orders', 'add');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids  = $_POST['product_id'] ?? [];
    $qtys = $_POST['qty'] ?? [];
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $newName  = trim($_POST['new_customer'] ?? '');
    $newPhone = trim($_POST['new_phone'] ?? '');

    try {
        // Merge same product picked twice
        $wanted = [];
        foreach ($ids as $i => $pid) {
            $pid = (int)$pid;
            $q   = (int)($qtys[$i] ?? 0);
            if ($pid > 0 && $q > 0) $wanted[$pid] = ($wanted[$pid] ?? 0) + $q;
        }
        if (!$wanted) throw new Exception('Add at least one product with quantity 1 or more.');

        $pdo->beginTransaction();

        if ($newName !== '') {
            $pdo->prepare("INSERT INTO customers (name, phone) VALUES (?,?)")->execute([$newName, $newPhone]);
            $customer_id = $pdo->lastInsertId();
        } elseif ($customer_id === 0) {
            $customer_id = null;
        }

        $pdo->prepare("INSERT INTO sales_orders (customer_id, user_id, status) VALUES (?,?,'completed')")
            ->execute([$customer_id, $_SESSION['user_id']]);
        $orderId = $pdo->lastInsertId();

        $sel = $pdo->prepare("SELECT name, price, quantity FROM products WHERE id=? FOR UPDATE");
        $ins = $pdo->prepare("INSERT INTO order_items (order_id, product_id, qty, unit_price) VALUES (?,?,?,?)");
        $upd = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id=?");

        $total = 0;
        foreach ($wanted as $pid => $q) {
            $sel->execute([$pid]);
            $p = $sel->fetch(PDO::FETCH_ASSOC);
            if (!$p) throw new Exception('A selected product no longer exists.');
            if ($p['quantity'] < $q) {
                throw new Exception("Not enough stock for {$p['name']} (available: {$p['quantity']}).");
            }
            $ins->execute([$orderId, $pid, $q, $p['price']]);   // price comes from DB, not from the form
            $upd->execute([$q, $pid]);
            $total += $p['price'] * $q;
        }

        $pdo->prepare("UPDATE sales_orders SET total=? WHERE id=?")->execute([$total, $orderId]);
        $pdo->commit();

        set_flash("Order #$orderId created and stock updated.");
        header("Location: view.php?id=$orderId");
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $e->getMessage();
    }
}

$customers = $pdo->query("SELECT * FROM customers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$products  = $pdo->query("SELECT * FROM products WHERE quantity > 0 ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$opts = '';
foreach ($products as $p) {
    $opts .= '<option value="' . $p['id'] . '">' . htmlspecialchars($p['name'])
           . ' - $' . number_format($p['price'], 2) . ' (stock: ' . $p['quantity'] . ')</option>';
}

require '../includes/header.php';
?>
<h1>New Sales Order</h1>
<div class="form-box" style="max-width:800px">
  <?php if ($error): ?><div class="flash error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="POST">
    <label>Customer</label>
    <select name="customer_id">
      <option value="0">-- select existing --</option>
      <?php foreach ($customers as $c): ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <label>...or add a new customer (optional)</label>
    <input type="text" name="new_customer" placeholder="New customer name">
    <input type="text" name="new_phone" placeholder="Phone">

    <h3 style="margin:15px 0 5px">Products</h3>
    <table id="items">
      <thead><tr><th>Product</th><th width="120">Qty</th><th width="60"></th></tr></thead>
      <tbody></tbody>
    </table>
    <br>
    <button type="button" class="btn gray" onclick="addRow()">+ Add another product</button>
    <hr style="margin:20px 0;border:none;border-top:1px solid #eee">
    <button class="btn">Save Order</button>
    <a class="btn gray" href="index.php">Cancel</a>
  </form>
</div>

<script>
const options = <?= json_encode($opts) ?>;
function addRow() {
  const tr = document.createElement('tr');
  tr.innerHTML = '<td><select name="product_id[]">' + options + '</select></td>' +
                 '<td><input type="number" name="qty[]" min="1" value="1"></td>' +
                 '<td><button type="button" class="btn danger small" onclick="this.closest(\'tr\').remove()">✕</button></td>';
  document.querySelector('#items tbody').appendChild(tr);
}
addRow();
</script>
<?php require '../includes/footer.php'; ?>