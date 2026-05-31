<?php

include("../config/database.php");

$student_id = $_GET['student_id'];

$query = mysqli_query(
    $conn,
    "SELECT *
     FROM students
     WHERE student_id='$student_id'"
);

$row = mysqli_fetch_assoc($query);

echo json_encode($row);

?>