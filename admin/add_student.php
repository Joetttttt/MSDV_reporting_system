<?php

include("../config/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $year_level = mysqli_real_escape_string($conn, $_POST['year_level']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);

    $query = "INSERT INTO students
            (student_id, fullname, course, year_level, department)

            VALUES

            ('$student_id','$fullname','$course','$year_level','$department')";

    mysqli_query($conn, $query);

    header("Location: students.php");
    exit();

}

?>