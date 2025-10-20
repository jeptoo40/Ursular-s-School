<?php
require_once __DIR__ . '/../config.php';


$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';


$stmt = $pdo->prepare('SELECT id, username, password, fullname FROM admins WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);


if ($admin && password_verify($password, $admin['password'])) {
// login
$_SESSION['admin_id'] = $admin['id'];
$_SESSION['admin_name'] = $admin['fullname'];
header('Location: /public/index.php');
exit;
} else {
$error = 'Invalid username or password';
}
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Login</title>
<link rel="stylesheet" href="/assets/style.css">
</head>
<body class="login-page">
<div class="login-box">
<h2>School Admin Login</h2>
<?php if ($error): ?>
<div class="alert"><?=htmlspecialchars($error)?></div>
<?php endif; ?>
<form method="post">
<label>Username</label>
<input type="text" name="username" required>
<label>Password</label>
<input type="password" name="password" required>
<button type="submit">Login</button>
</form>
</div>
</body>
</html><?php
require_once __DIR__ . '/../config.php';


$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';


$stmt = $pdo->prepare('SELECT id, username, password, fullname FROM admins WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);


if ($admin && password_verify($password, $admin['password'])) {
// login
$_SESSION['admin_id'] = $admin['id'];
$_SESSION['admin_name'] = $admin['fullname'];
header('Location: /public/index.php');
exit;
} else {
$error = 'Invalid username or password';
}
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Login</title>
<link rel="stylesheet" href="/assets/style.css">
</head>
<body class="login-page">
<div class="login-box">
<h2>School Admin Login</h2>
<?php if ($error): ?>
<div class="alert"><?=htmlspecialchars($error)?></div>
<?php endif; ?>
<form method="post">
<label>Username</label>
<input type="text" name="username" required>
<label>Password</label>
<input type="password" name="password" required>
<button type="submit">Login</button>
</form>
</div>
</body>
</html>