<?php

include("../config/database.php");

if (isset($_GET['id'])) {

    $id = $_GET['id'];

    $query = "DELETE FROM users WHERE id='$id'";

    mysqli_query($conn, $query);

    header("Location: users.php");
    exit();

}

?>