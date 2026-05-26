<?php
/*
|--------------------------------------------------------------------------
| save_violation.php  (use same for teacher/, csu/, jassu/)
| Inserts violation then fires a new_violation notification.
|--------------------------------------------------------------------------
*/

session_start();
include("../config/database.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $student_id         = mysqli_real_escape_string($conn, $_POST['student_id']);
    $student_name       = mysqli_real_escape_string($conn, $_POST['student_name']);
    $course             = mysqli_real_escape_string($conn, $_POST['course']);
    $year_level         = mysqli_real_escape_string($conn, $_POST['year_level']);
    $department         = mysqli_real_escape_string($conn, $_POST['department']);
    $violation_category = mysqli_real_escape_string($conn, $_POST['violation_category']);
    $violation_type     = mysqli_real_escape_string($conn, $_POST['violation_type']);
    $description        = mysqli_real_escape_string($conn, $_POST['description']);
    $camera_capture     = $_POST['camera_capture'];
    $e_signature        = $_POST['e_signature'];
    $reported_by        = $_SESSION['fullname'];
    $reporter_role      = $_SESSION['role'];

    $evidence = "";
    if (isset($_FILES['evidence']) && $_FILES['evidence']['name'] != "") {
        $filename = time() . "_" . $_FILES['evidence']['name'];
        $target   = "../uploads/" . $filename;
        move_uploaded_file($_FILES['evidence']['tmp_name'], $target);
        $evidence = $filename;
    }

    $query = "
    INSERT INTO violations (
        student_id, student_name, course, year_level, department,
        violation_category, violation_type, description,
        evidence, camera_capture, e_signature,
        reported_by, reporter_role
    ) VALUES (
        '$student_id', '$student_name', '$course', '$year_level', '$department',
        '$violation_category', '$violation_type', '$description',
        '$evidence', '$camera_capture', '$e_signature',
        '$reported_by', '$reporter_role'
    )";

    $result = mysqli_query($conn, $query);

    if ($result) {

        /* ── INSERT NEW VIOLATION NOTIFICATION ── */
        $notif_title   = mysqli_real_escape_string($conn, "New Violation: $student_name");
        $notif_message = mysqli_real_escape_string($conn,
            "$reported_by ($reporter_role) submitted a $violation_category violation " .
            "($violation_type) for $student_name (ID: $student_id)."
        );
        $sid_esc = mysqli_real_escape_string($conn, $student_id);

        mysqli_query($conn, "
            INSERT INTO notifications (type, title, message, student_id)
            VALUES ('new_violation', '$notif_title', '$notif_message', '$sid_esc')
        ");
        /* ────────────────────────────────────── */

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