<?php

session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
</head>
<body>

<h2>First Login - Change Password</h2>

<form action="update_password.php" method="POST">

    <label>New Password</label><br>
    <input type="password"
           name="new_password"
           required>

    <br><br>

    <label>Confirm Password</label><br>
    <input type="password"
           name="confirm_password"
           required>

    <br><br>

    <button type="submit">
        Save Password
    </button>

</form>

</body>
</html>