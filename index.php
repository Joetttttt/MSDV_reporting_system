<?php

require_once "config/database.php";
require_once "config/session.php";

$error = "";

if(isset($_POST['login']))
{
    $username = mysqli_real_escape_string(
        $conn,
        $_POST['username']
    );

    $password = md5($_POST['password']);

    $query = mysqli_query(
        $conn,
        "SELECT * FROM users
        WHERE username='$username'
        AND password='$password'"
    );

    if(mysqli_num_rows($query) > 0)
    {
        $user = mysqli_fetch_assoc($query);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];

        switch($user['role'])
        {
            case 'admin':
                header("Location: admin/dashboard.php");
                break;

            case 'teacher':
                header("Location: teacher/dashboard.php");
                break;

            case 'csu':
                header("Location: csu/dashboard.php");
                break;

            case 'jassu':
                header("Location: jassu/dashboard.php");
                break;

            case 'student':
                header("Location: student/dashboard.php");
                break;
        }

        exit();
    }
    else
    {
        $error = "Invalid Username or Password";
    }
}
?>
