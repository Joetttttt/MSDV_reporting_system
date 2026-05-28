<?php
session_start();
include("../config/database.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

if ($_SESSION['role'] != 'student') {
    header("Location: ../index.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $violation_id = mysqli_real_escape_string($conn, $_POST['violation_id']);
    $appeal_reason = mysqli_real_escape_string($conn, $_POST['appeal_reason']);
    $student_id = mysqli_real_escape_string($conn, $_SESSION['username']);
    $student_name = mysqli_real_escape_string($conn, $_SESSION['fullname']);

    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS appeals (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        violation_id INT(11) NOT NULL,
        student_id VARCHAR(50) NOT NULL,
        student_name VARCHAR(100) NOT NULL,
        appeal_reason TEXT NOT NULL,
        appeal_status VARCHAR(50) NOT NULL DEFAULT 'Pending',
        created_at TIMESTAMP NOT NULL DEFAULT current_timestamp()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $insert = "INSERT INTO appeals (violation_id, student_id, student_name, appeal_reason)
               VALUES ('$violation_id', '$student_id', '$student_name', '$appeal_reason')";

    if (mysqli_query($conn, $insert)) {
        echo "<script>alert('Appeal submitted successfully.'); window.location='dashboard.php#appeals';</script>";
    } else {
        echo "<script>alert('Unable to submit appeal. Please try again.'); window.location='dashboard.php#appeals';</script>";
    }
    exit();
}

header("Location: dashboard.php");
exit();
