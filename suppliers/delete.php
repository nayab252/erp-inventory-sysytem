<?php
require '../includes/auth.php';
require_login();
require_permission('suppliers', 'delete');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->prepare("DELETE FROM suppliers WHERE id=?")->execute([(int)$_POST['id']]);
        set_flash('Supplier deleted.');
    } catch (PDOException $e) {
        set_flash('Cannot delete: this supplier is linked to products.', 'error');
    }
}
header('Location: index.php');
exit;