<?php

session_start();

include("../config/database.php");

/* CHECK LOGIN */

if (!isset($_SESSION['user_id'])) {

    header("Location: ../index.html");
    exit();

}

if ($_SESSION['role'] != 'admin') {

    header("Location: ../index.html");
    exit();

}

/* GET FORM DATA */

$id =
$_POST['id'];

$student_id =
$_POST['student_id'];

$fullname =
$_POST['fullname'];

$course =
$_POST['course'];

$year_level =
$_POST['year_level'];

$department =
$_POST['department'];

/* UPDATE QUERY */

$query = "

UPDATE students

SET

student_id='$student_id',
fullname='$fullname',
course='$course',
year_level='$year_level',
department='$department'

WHERE id='$id'

";

/* EXECUTE */

if(mysqli_query($conn, $query)){

    echo "

    <script>

        alert('Student updated successfully.');

        window.location='students.php';

    </script>

    ";

}else{

    echo mysqli_error($conn);

}

?>