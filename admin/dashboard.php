<?php

require_once "../config/auth.php";

echo "<h1>Admin Dashboard</h1>";

echo "Welcome " . $_SESSION['fullname'];

echo "<br><br>";

echo "<a href='../logout.php'>Logout</a>";
?>