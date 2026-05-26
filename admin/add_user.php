<?php

session_start();

include("../config/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // HASH PASSWORD
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // CHECK EMAIL
    $checkEmail = "SELECT * FROM users WHERE email='$email'";
    $emailResult = mysqli_query($conn, $checkEmail);

    if(mysqli_num_rows($emailResult) > 0){

        echo "
        <script>
            alert('Email already exists.');
            window.location='users.php';
        </script>
        ";

        exit();
    }

    // CHECK USERNAME
    $checkUsername = "SELECT * FROM users WHERE username='$username'";
    $usernameResult = mysqli_query($conn, $checkUsername);

    if(mysqli_num_rows($usernameResult) > 0){

        echo "
        <script>
            alert('Username already exists.');
            window.location='users.php';
        </script>
        ";

        exit();
    }

    // INSERT USER
    $query = "INSERT INTO users(fullname, username, email, password, role)
              VALUES('$fullname','$username','$email','$password','$role')";

    mysqli_query($conn, $query);

    echo "
    <script>
        alert('User added successfully.');
        window.location='users.php';
    </script>
    ";

}

?>