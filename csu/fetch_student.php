<?php

include("../config/database.php");

if(isset($_POST['student_id'])){

    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);

    $query = "SELECT * FROM students WHERE student_id='$student_id'";

    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) > 0){

        $student = mysqli_fetch_assoc($result);

        echo json_encode($student);

    }

}
?>