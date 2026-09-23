<?php
session_start();
require 'config/db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=?");
    $stmt->execute([$_POST['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role_id']   = $user['role_id'];
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Invalid email or password';
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>ERP Login</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
  <form method="POST" class="login-card">
    <h2>📦 ERP System</h2>
    <p>Sign in to continue</p>
    <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
  </form>
</body>
</html>