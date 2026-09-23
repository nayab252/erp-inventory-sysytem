<?php
session_start();
require_once __DIR__ . '/../config/db.php';

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /erp/login.php');
        exit;
    }
}

// Example: can('products', 'add')
function can($module, $action) {
    global $pdo;
    $col = 'can_' . $action;
    if (!in_array($col, ['can_view','can_add','can_edit','can_delete'])) return false;
    $stmt = $pdo->prepare("SELECT $col FROM permissions WHERE role_id=? AND module=?");
    $stmt->execute([$_SESSION['role_id'], $module]);
    return (bool) $stmt->fetchColumn();
}

function require_permission($module, $action) {
    if (!can($module, $action)) {
        http_response_code(403);
        die("Access denied. You don't have permission for this.");
    }
}
function set_flash($msg, $type = 'success') {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

function show_flash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        echo '<div class="flash ' . $f['type'] . '">' . htmlspecialchars($f['msg']) . '</div>';
        unset($_SESSION['flash']);
    }
}