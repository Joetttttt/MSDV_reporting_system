<?php

session_start();

include("database.php");

if(!isset($_SESSION['user_id'])){

    if(isset($_COOKIE['remember_token'])){

        $token =
        $_COOKIE['remember_token'];

        $result =
        mysqli_query(
            $conn,
            "SELECT *
             FROM users
             WHERE remember_token='$token'"
        );

        if(mysqli_num_rows($result) > 0){

            $user =
            mysqli_fetch_assoc($result);

            $_SESSION['user_id']
            = $user['id'];

            $_SESSION['fullname']
            = $user['fullname'];

            $_SESSION['role']
            = $user['role'];

        }

    }

}

if(!isset($_SESSION['user_id'])){

    header(
        "Location: ../auth/login.php"
    );

    exit();
}
?>