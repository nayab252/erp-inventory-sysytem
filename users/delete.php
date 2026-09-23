<?php
require '../includes/auth.php';
require_login();
require_permission('users', 'delete');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    if ($id === (int)$_SESSION['user_id']) {
        set_flash('You cannot delete your own account.', 'error');
    } else {
        try {
            $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
            set_flash('User deleted.');
        } catch (PDOException $e) {
            set_flash('Cannot delete: this user created sales orders.', 'error');
        }
    }
}
header('Location: index.php');
exit;