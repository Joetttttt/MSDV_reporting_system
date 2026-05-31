<?php

session_start();

include("../config/database.php");

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $username = mysqli_real_escape_string(
        $conn,
        $_POST['username']
    );

    $password = md5($_POST['password']);

    $query =
    "SELECT *
     FROM users
     WHERE username='$username'
     AND password='$password'";

    $result = mysqli_query($conn,$query);

    if(mysqli_num_rows($result) > 0){

        $user = mysqli_fetch_assoc($result);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];

        if(isset($_POST['remember_me'])){

            $token = bin2hex(random_bytes(32));

            mysqli_query(
                $conn,
                "UPDATE users
                 SET remember_token='$token'
                 WHERE id='{$user['id']}'"
            );

            setcookie(
                "remember_token",
                $token,
                time() + (60 * 60 * 24 * 365 * 10),
                "/"
            );

        }

        if(
            $user['role'] != 'admin'
            &&
            $user['first_login'] == 1
        ){

            header("Location: first_login.php");
            exit();

        }

        switch($user['role']){

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

        exit();

    }else{

        echo "Invalid Username or Password";

    }

}
?>