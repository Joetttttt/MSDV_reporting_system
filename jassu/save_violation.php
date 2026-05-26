<?php

session_start();

include("../config/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // STUDENT INFO
    $student_id = mysqli_real_escape_string(
        $conn,
        $_POST['student_id']
    );

    $student_name = mysqli_real_escape_string(
        $conn,
        $_POST['student_name']
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

    // VIOLATION
    $violation_category = mysqli_real_escape_string(
        $conn,
        $_POST['violation_category']
    );

    $violation_type = mysqli_real_escape_string(
        $conn,
        $_POST['violation_type']
    );

    $description = mysqli_real_escape_string(
        $conn,
        $_POST['description']
    );

    // CAMERA + SIGNATURE
    $camera_capture = $_POST['camera_capture'];

    $e_signature = $_POST['e_signature'];

    // REPORTER
    $reported_by = $_SESSION['fullname'];

    $reporter_role = $_SESSION['role'];

    // EVIDENCE FILE
    $evidence = "";

    if(
        isset($_FILES['evidence']) &&
        $_FILES['evidence']['name'] != ""
    ){

        $filename =
            time() . "_" .
            $_FILES['evidence']['name'];

        $target =
            "../uploads/" . $filename;

        move_uploaded_file(
            $_FILES['evidence']['tmp_name'],
            $target
        );

        $evidence = $filename;

    }

    // INSERT
    $query = "

    INSERT INTO violations (

        student_id,
        student_name,
        course,
        year_level,
        department,

        violation_category,
        violation_type,
        description,

        evidence,
        camera_capture,
        e_signature,

        reported_by,
        reporter_role

    )

    VALUES (

        '$student_id',
        '$student_name',
        '$course',
        '$year_level',
        '$department',

        '$violation_category',
        '$violation_type',
        '$description',

        '$evidence',
        '$camera_capture',
        '$e_signature',

        '$reported_by',
        '$reporter_role'

    )

    ";

    $result = mysqli_query($conn, $query);

    if($result){

        echo "

        <script>

            alert('Violation Report Submitted Successfully');

            window.location='report_violation.php';

        </script>

        ";

    } else {

        echo mysqli_error($conn);

    }

}

?>