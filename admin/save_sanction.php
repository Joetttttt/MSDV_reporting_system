<?php

session_start();

include("../config/database.php");

if (!isset($_SESSION['user_id'])) {

    header("Location: ../index.html");
    exit();

}

if ($_SESSION['role'] != 'admin') {

    header("Location: ../index.html");
    exit();

}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $report_id =
    $_POST['report_id'];

    $sanction =
    $_POST['sanction'];

    $action_start =
    $_POST['action_start'];

    $action_end =
    $_POST['action_end'];

    $disciplinary_level =
    $_POST['disciplinary_level'];

    $query = "

    UPDATE violations

    SET

    sanction='$sanction',
    action_start='$action_start',
    action_end='$action_end',
    disciplinary_level='$disciplinary_level',
    case_status='Pending'

    WHERE id='$report_id'

    ";

    mysqli_query($conn, $query);

    header("Location: reports.php");
    exit();

}

?>