<?php

include("../config/database.php");

$student_id = $_GET['student_id'];

$query = mysqli_query(
    $conn,
    "SELECT * FROM students
     WHERE student_id='$student_id'"
);

if(mysqli_num_rows($query) > 0)
{
    echo json_encode(
        mysqli_fetch_assoc($query)
    );
}
else
{
    echo "not_found";
}
?>