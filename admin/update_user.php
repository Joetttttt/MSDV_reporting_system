<?php

include("../config/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id = $_POST['id'];

    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    $query = "UPDATE users SET
                fullname='$fullname',
                username='$username',
                email='$email',
                role='$role'
              WHERE id='$id'";

    mysqli_query($conn, $query);

    header("Location: users.php");
    exit();

}

?>