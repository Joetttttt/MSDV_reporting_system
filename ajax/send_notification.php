<?php

include("../config/database.php");

$user_id = $_POST['user_id'];

$title = mysqli_real_escape_string(
$conn,
$_POST['title']
);

$message = mysqli_real_escape_string(
$conn,
$_POST['message']
);

$redirect_link = $_POST['redirect_link'];

mysqli_query(
$conn,
"INSERT INTO notifications
(
user_id,
title,
message,
redirect_link
)
VALUES
(
'$user_id',
'$title',
'$message',
'$redirect_link'
)"
);

echo "success";

?>