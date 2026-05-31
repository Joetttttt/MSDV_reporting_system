<?php

include("../config/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $year_level = mysqli_real_escape_string($conn, $_POST['year_level']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);

    // Save student record
    $query = "INSERT INTO students
    (student_id, fullname, course, year_level, department)
    VALUES
    ('$student_id','$fullname','$course','$year_level','$department')";

    mysqli_query($conn, $query);

    // ==========================
    // AUTO CREATE STUDENT ACCOUNT
    // ==========================

    $nameParts = explode(' ', trim($fullname));
    $firstname = strtolower($nameParts[0]);

    // username example: MCC@juan345
    $username = "MCC@" . $firstname . substr($student_id, -3);

    // password = last 5 digits of student ID
    $default_password = substr(str_replace("-", "", $student_id), -5);

    $encrypted_password = md5($default_password);

    mysqli_query($conn,"
    INSERT INTO users
    (
        fullname,
        username,
        email,
        password,
        role,
        student_id,
        first_login
    )
    VALUES
    (
        '$fullname',
        '$username',
        '',
        '$encrypted_password',
        'student',
        '$student_id',
        1
    )
");

echo "
<script>
alert(
'Student Added Successfully\n\nUsername: $username\nPassword: $default_password'
);
window.location='students.php';
</script>";
exit();