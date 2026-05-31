<?php

include("../config/database.php");

$student_id = $_POST['student_id'];
$first_name = $_POST['first_name'];
$middle_name = $_POST['middle_name'];
$last_name = $_POST['last_name'];
$course = $_POST['course'];
$year_level = $_POST['year_level'];
$department = $_POST['department'];

$sql = "INSERT INTO students(
student_id,
first_name,
middle_name,
last_name,
course,
year_level,
department
)
VALUES(
'$student_id',
'$first_name',
'$middle_name',
'$last_name',
'$course',
'$year_level',
'$department'
)";

if(mysqli_query($conn,$sql)){

    $username = "MCC@" . $first_name;

    $last5 = substr(
        str_replace("-","",$student_id),
        -5
    );

    $password = md5($last5);

    mysqli_query(
        $conn,
        "INSERT INTO users(
        fullname,
        username,
        password,
        role,
        first_login
        )
        VALUES(
        '$first_name $last_name',
        '$username',
        '$password',
        'student',
        1
        )"
    );

    header("Location: student_records.php");

}
?>