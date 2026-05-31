<?php

include("../config/database.php");

$student_id = $_POST['student_id'];

$admin_password = $_POST['admin_password'];

$admin = mysqli_query(
    $conn,
    "SELECT * FROM users
    WHERE role='admin'
    LIMIT 1"
);

$adminData = mysqli_fetch_assoc($admin);

if($admin_password != $adminData['password'])
{
    echo "Invalid Password";
    exit();
}

mysqli_query(
    $conn,
    "DELETE FROM users
     WHERE student_id='$student_id'"
);

mysqli_query(
    $conn,
    "DELETE FROM students
     WHERE student_id='$student_id'"
);

echo "success";

?>