<?php
require '../includes/auth.php';
require_login();
require_permission('products', 'delete');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->prepare("DELETE FROM products WHERE id=?")->execute([(int)$_POST['id']]);
        set_flash('Product deleted.');
    } catch (PDOException $e) {
        set_flash('Cannot delete: this product exists in sales orders.', 'error');
    }
}
header('Location: index.php');
exit;