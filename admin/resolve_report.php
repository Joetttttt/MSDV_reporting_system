<?php

include("../config/database.php");

if(isset($_GET['id'])){

    $id = $_GET['id'];

    $query = "UPDATE violations
              SET status='Resolved'
              WHERE id='$id'";

    mysqli_query($conn, $query);

    header("Location: reports.php");
    exit();

}
?>