<?php

include("../config/auth.php");

if($_SESSION['role'] != 'student'){
    header("Location: ../auth/login.php");
    exit();
}

?>

<h1>Teacher Dashboard</h1>

<p>
Welcome
<?php echo $_SESSION['fullname']; ?>
</p>

<a href="../auth/logout.php">
Logout
</a>