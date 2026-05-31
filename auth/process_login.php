<?php

session_start();

include("../config/database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = mysqli_real_escape_string(
        $conn,
        $_POST['username']
    );

    $password = md5($_POST['password']);

    $sql = "SELECT * FROM users
            WHERE username='$username'
            AND password='$password'";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {

        $user = mysqli_fetch_assoc($result);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];

        if ($user['first_login'] == 1) {

            header("Location: first_login.php");
            exit();
        }

        switch ($user['role']) {

            case 'admin':
                header("Location: ../admin/dashboard.php");
                break;

            case 'teacher':
                header("Location: ../teacher/dashboard.php");
                break;

            case 'jassu':
                header("Location: ../jassu/dashboard.php");
                break;

            case 'csu':
                header("Location: ../csu/dashboard.php");
                break;

            case 'student':
                header("Location: ../student/dashboard.php");
                break;
        }

    } else {

        echo "Invalid username or password.";

    }

}
?>