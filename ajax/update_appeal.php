<?php

include("../config/database.php");

$appeal_id = $_POST['appeal_id'];

$status = $_POST['status'];

mysqli_query(
$conn,
"UPDATE appeals
SET status='$status'
WHERE appeal_id='$appeal_id'"
);

header(
"Location: ../admin/student_appeals.php"
);

exit();
?>