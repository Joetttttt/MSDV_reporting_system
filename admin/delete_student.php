<?php

session_start();

include("../config/database.php");

if(!isset($_SESSION['user_id'])){

    header("Location: ../index.html");
    exit();

}

if($_SESSION['role'] != 'admin'){

    header("Location: ../index.php");
    exit();

}

/* ADMIN DELETE PASSWORD */
$adminPassword = "admin123";

/* FORM DATA */
$student_id =
mysqli_real_escape_string(
    $conn,
    $_POST['student_id']
);

$password =
$_POST['delete_password'];

/* VERIFY PASSWORD */
if($password != $adminPassword){

    echo "

    <script>

        alert('Incorrect deletion password.');

        window.location='students.php';

    </script>

    ";

    exit();

}

/* DELETE VIOLATIONS FIRST */
mysqli_query(
    $conn,
    "DELETE FROM violations
     WHERE student_id='$student_id'"
);

/* DELETE STUDENT */
mysqli_query(
    $conn,
    "DELETE FROM students
     WHERE student_id='$student_id'"
);

echo "

<script>

    alert('Student deleted successfully.');

    window.location='students.php';

</script>

";

?>