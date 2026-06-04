<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

// Already logged in
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    if ($role === 'admin') $redirect = 'admin/dashboard.php';
    elseif (in_array($role, ['teacher','csu','jassu'])) $redirect = 'reporter/dashboard.php';
    else $redirect = 'student/dashboard.php';

    echo json_encode(['success' => true, 'redirect' => $redirect]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = md5(trim($_POST['password'] ?? ''));

    if (empty($username) || empty($_POST['password'])) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        $_SESSION['user_id']        = $user['id'];
        $_SESSION['username']       = $user['username'];
        $_SESSION['full_name']      = $user['full_name'];
        $_SESSION['role']           = $user['role'];
        $_SESSION['is_first_login'] = $user['is_first_login'];

        if ($user['role'] === 'admin') {
            $redirect = 'admin/dashboard.php';
        } elseif (in_array($user['role'], ['teacher','csu','jassu'])) {
            $redirect = 'reporter/dashboard.php';
        } else {
            $redirect = 'student/dashboard.php';
        }

        echo json_encode(['success' => true, 'redirect' => $redirect]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>