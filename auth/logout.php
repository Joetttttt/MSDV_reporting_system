<?php

session_start();

include("../config/database.php");

if(isset($_COOKIE['remember_token'])){

    $token = $_COOKIE['remember_token'];

    mysqli_query(
        $conn,
        "UPDATE users
         SET remember_token=NULL
         WHERE remember_token='$token'"
    );

    setcookie(
        "remember_token",
        "",
        time() - 3600,
        "/"
    );
}

session_destroy();

header("Location: login.php");
exit();
?>