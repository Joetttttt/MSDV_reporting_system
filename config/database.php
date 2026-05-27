<?php

$host = "localhost";
$user = "root";
$pass = "";
$db = "mcc_discipline_system";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database Connection Failed");
}

?>