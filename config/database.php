<?php

$conn = mysqli_connect(
    getenv("MYSQLHOST"),
    getenv("MYSQLUSER"),
    getenv("MYSQLPASSWORD"),
    getenv("railway"),
    getenv("MYSQLPORT")
);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>