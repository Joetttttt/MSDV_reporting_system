<?php

include("../config/database.php");

if($_SERVER["REQUEST_METHOD"] == "POST")
{
    $student_id = $_POST['student_id'];

    $full_name = $_POST['full_name'];

    $course = $_POST['course'];

    $year_level = $_POST['year_level'];

    $department = $_POST['department'];

    $update = mysqli_query(
        $conn,
        "UPDATE students
        SET
        full_name='$full_name',
        course='$course',
        year_level='$year_level',
        department='$department'
        WHERE student_id='$student_id'"
    );

    mysqli_query(
        $conn,
        "UPDATE users
        SET
        full_name='$full_name'
        WHERE student_id='$student_id'"
    );

    if($update)
    {
        echo "success";
    }
    else
    {
        echo "error";
    }
}
?>