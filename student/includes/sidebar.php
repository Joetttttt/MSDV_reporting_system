<?php

$countQuery = mysqli_query(
$conn,
"SELECT *
FROM notifications
WHERE user_id='".$_SESSION['user_id']."'
AND is_read=0"
);

$notificationCount =
mysqli_num_rows($countQuery);

?>