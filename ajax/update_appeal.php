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

$getAppeal =
mysqli_query(
$conn,
"SELECT *
FROM appeals
WHERE appeal_id='$appeal_id'"
);

$appeal =
mysqli_fetch_assoc(
$getAppeal
);

$getUser =
mysqli_query(
$conn,
"SELECT *
FROM users
WHERE student_id='".$appeal['student_id']."'
LIMIT 1"
);

$user =
mysqli_fetch_assoc(
$getUser
);

$title = "Appeal Update";

$message =
"Your appeal request has been "
. $status;

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
'$title',
'$message',
'Appeal'
)"
);

header(
"Location: ../admin/student_appeals.php"
);

exit();
?>