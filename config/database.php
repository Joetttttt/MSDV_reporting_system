<?php

$host = "localhost";
$user = "root";
$password = "";
$database = "mcc_discipline_system";

$conn = mysqli_connect(
    $host,
    $user,
    $password,
    $database
);

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

?>