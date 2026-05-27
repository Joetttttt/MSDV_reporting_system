<?php

$host = "localhost";
$user = "root";
$pass = "";
$db = "mcc_discipline_system";<?php

$conn = mysqli_connect(
    getenv("MYSQLHOST"),
    getenv("MYSQLUSER"),
    getenv("MYSQLPASSWORD"),
    getenv("MYSQLDATABASE"),
    getenv("MYSQLPORT")
);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Database Connection Failed");
}

?>