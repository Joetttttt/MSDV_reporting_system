<?php

include("../config/database.php");

$action_id = $_POST['action_id'];

$status = $_POST['status'];

mysqli_query(
$conn,
"UPDATE disciplinary_actions
SET status='$status'
WHERE action_id='$action_id'"
);

header(
"Location: ../admin/disciplinary_actions.php"
);

exit();
?>