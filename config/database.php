<?php

$conn = mysqli_connect(
    getenv("MYSQLHOST"),
    getenv("root"),
    getenv("GZcElxrUWAbmCqZTJFmyjSBEwpaqmWEz"),
    getenv("railway"),
    getenv("3306")
);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>