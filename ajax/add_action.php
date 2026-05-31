<?php

include("../config/database.php");

$report_id = $_POST['report_id'];

$student_id = $_POST['student_id'];

$sanction = $_POST['sanction'];

$start_date = $_POST['start_date'];

$end_date = $_POST['end_date'];

mysqli_query(
$conn,
"INSERT INTO disciplinary_actions
(
report_id,
student_id,
sanction,
start_date,
end_date
)
VALUES
(
'$report_id',
'$student_id',
'$sanction',
'$start_date',
'$end_date'
)"
);

echo "success";

?>