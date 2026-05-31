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

$userQuery =
mysqli_query(
$conn,
"SELECT *
FROM users
WHERE student_id='$student_id'
LIMIT 1"
);

$user =
mysqli_fetch_assoc(
$userQuery
);

mysqli_query(
$conn,
"INSERT INTO notifications
(
user_id,
title,
message,
notification_type
)
VALUES
(
'".$user['user_id']."',
'New Disciplinary Action',
'You have been assigned a disciplinary action.',
'Sanction'
)"
);

echo "success";

?>