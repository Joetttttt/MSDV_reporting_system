<?php
session_start();
require_once 'db/connection.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . $_SESSION['role'] . '/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember_me']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']        = $user['id'];
        $_SESSION['username']       = $user['username'];
        $_SESSION['full_name']      = $user['full_name'];
        $_SESSION['role']           = $user['role'];
        $_SESSION['is_first_login'] = $user['is_first_login'];

        if ($remember) {
            setcookie('remember_user', $user['username'], time() + (86400 * 30), '/');
        }

        header('Location: ' . $user['role'] . '/dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}

$remembered = isset($_COOKIE['remember_user']) ? $_COOKIE['remember_user'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MDSV - Login</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#0d6efd">
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('service-worker.js');
}
</script>
</head>
<body class="bg-light">
<div class="container">
  <div class="row justify-content-center align-items-center min-vh-100">
    <div class="col-md-4 col-sm-8 col-11">
      <div class="card shadow">
        <div class="card-body p-4">
          <h4 class="text-center mb-1">MDSV Reporting System</h4>
          <p class="text-center text-muted small mb-4">Mindanao Colleges</p>

          <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control"
                     value="<?= htmlspecialchars($remembered) ?>" required autofocus>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3 form-check">
              <input type="checkbox" name="remember_me" class="form-check-input" id="rememberMe"
                     <?= $remembered ? 'checked' : '' ?>>
              <label class="form-check-label" for="rememberMe">Remember Me</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">Log In</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>