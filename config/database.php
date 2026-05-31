<?php

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "msdv_reporting_system";

$conn = mysqli_connect(
    $host,
    $user,
    $pass,
    $dbname
);

if(!$conn){
    die("Connection Failed");
}
?>