<?php

include("../config/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id = $_POST['id'];

    // HASH NEW PASSWORD
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $query = "UPDATE users
              SET password='$password'
              WHERE id='$id'";

    mysqli_query($conn, $query);

    header("Location: users.php");
    exit();

}

?>