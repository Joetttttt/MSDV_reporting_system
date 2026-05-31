<?php

include("../config/database.php");

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    $student_id = mysqli_real_escape_string(
        $conn,
        $_POST['student_id']
    );

    $full_name = mysqli_real_escape_string(
        $conn,
        $_POST['full_name']
    );

    $course = mysqli_real_escape_string(
        $conn,
        $_POST['course']
    );

    $year_level = mysqli_real_escape_string(
        $conn,
        $_POST['year_level']
    );

    $department = mysqli_real_escape_string(
        $conn,
        $_POST['department']
    );

    $check = mysqli_query(
        $conn,
        "SELECT * FROM students
         WHERE student_id='$student_id'"
    );

    if(mysqli_num_rows($check) > 0)
    {
        echo "Student ID already exists";
        exit();
    }

    $insertStudent = mysqli_query(
        $conn,
        "INSERT INTO students
        (
            student_id,
            full_name,
            course,
            year_level,
            department
        )
        VALUES
        (
            '$student_id',
            '$full_name',
            '$course',
            '$year_level',
            '$department'
        )"
    );

    if(!$insertStudent)
    {
        echo "Failed to save student";
        exit();
    }

    /*
    ===================================
    AUTO CREATE STUDENT ACCOUNT
    ===================================
    */

    $nameParts = explode(" ", trim($full_name));

    $firstName = $nameParts[0];

    $username = "MCC@" . $firstName;

    $password = substr(
        str_replace("-", "", $student_id),
        -5
    );

    $insertUser = mysqli_query(
        $conn,
        "INSERT INTO users
        (
            student_id,
            full_name,
            username,
            password,
            role,
            first_login
        )
        VALUES
        (
            '$student_id',
            '$full_name',
            '$username',
            '$password',
            'student',
            1
        )"
    );

    if($insertUser)
    {
        echo "success";
    }
    else
    {
        echo "Student saved but account creation failed";
    }
}
?>