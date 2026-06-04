<?php

session_start();
require_once 'db/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

$username = trim($_POST['username']);
$password = $_POST['password'];
$remember = isset($_POST['remember_me']);

$stmt = $pdo->prepare(
    "SELECT * FROM users WHERE username = ?"
);

$stmt->execute([$username]);

$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {

    $_SESSION['user_id']        = $user['id'];
    $_SESSION['username']       = $user['username'];
    $_SESSION['full_name']      = $user['full_name'];
    $_SESSION['role']           = $user['role'];
    $_SESSION['is_first_login'] = $user['is_first_login'];

    if ($remember) {
        setcookie(
            'remember_user',
            $user['username'],
            time() + (86400 * 30),
            '/'
        );
    }

    header(
        'Location: ' .
        $user['role'] .
        '/dashboard.php'
    );
    exit;
}

header('Location: index.html?error=1');
exit;
?>