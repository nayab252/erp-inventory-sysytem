<?php
require '../includes/auth.php';
require_login();
require_permission('orders', 'edit');

$id = (int)($_POST['id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT status FROM sales_orders WHERE id=? FOR UPDATE");
        $stmt->execute([$id]);
        $status = $stmt->fetchColumn();

        if ($status === 'completed') {
            $items = $pdo->prepare("SELECT product_id, qty FROM order_items WHERE order_id=?");
            $items->execute([$id]);
            $upd = $pdo->prepare("UPDATE products SET quantity = quantity + ? WHERE id=?");
            foreach ($items->fetchAll(PDO::FETCH_ASSOC) as $it) {
                $upd->execute([$it['qty'], $it['product_id']]);
            }
            $pdo->prepare("UPDATE sales_orders SET status='cancelled' WHERE id=?")->execute([$id]);
            set_flash("Order #$id cancelled and stock returned.");
        }
        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        set_flash('Could not cancel the order.', 'error');
    }
}
header("Location: view.php?id=$id");
exit;