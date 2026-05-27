<?php

$conn = mysqli_connect(
    getenv("MYSQLHOST"),
    getenv("MYSQLUSER"),
    getenv("GZcElxrUWAbmCqZTJFmyjSBEwpaqmWEz"),
    getenv("railway"),
    getenv("3306")
);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>