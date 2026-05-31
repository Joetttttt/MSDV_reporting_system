<?php

include("../config/database.php");
include("../config/session.php");

$student_id = $_POST['student_id'];

$category = $_POST['category'];

$violation_name = $_POST['violation_name'];

$description = $_POST['description'];

$reporter_id = $_SESSION['user_id'];

$report_number =
"VR-" . date("YmdHis");

$evidence_image = "";

if(isset($_FILES['evidence_image']))
{
    $filename =
    time() .
    "_" .
    $_FILES['evidence_image']['name'];

    move_uploaded_file(
        $_FILES['evidence_image']['tmp_name'],
        "../assets/uploads/evidence/" . $filename
    );

    $evidence_image = $filename;
}

$insert = mysqli_query(
    $conn,
    "INSERT INTO violation_reports
    (
        report_number,
        student_id,
        reporter_id,
        category,
        violation_name,
        description,
        evidence_image
    )
    VALUES
    (
        '$report_number',
        '$student_id',
        '$reporter_id',
        '$category',
        '$violation_name',
        '$description',
        '$evidence_image'
    )"
);

if($insert)
{
    echo "success";
}
else
{
    echo mysqli_error($conn);
}
?>