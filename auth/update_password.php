<?php

session_start();

include("../config/database.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$new_password =
    $_POST['new_password'];

$confirm_password =
    $_POST['confirm_password'];

if($new_password != $confirm_password){

    die("Passwords do not match.");

}

$user_id =
    $_SESSION['user_id'];

$password =
    md5($new_password);

$sql =
"UPDATE users
 SET password='$password',
 first_login=0
 WHERE id='$user_id'";

if(mysqli_query($conn,$sql)){

    session_destroy();

    echo "
    <script>
        alert('Password changed successfully. Login again.');
        window.location='login.php';
    </script>
    ";

}
?>