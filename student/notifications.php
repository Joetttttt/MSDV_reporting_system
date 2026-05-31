<?php

include("../config/session.php");
include("../config/database.php");

$user_id = $_SESSION['user_id'];

mysqli_query(
    $conn,
    "UPDATE notifications
     SET is_read = 1
     WHERE user_id='$user_id'"
);

$notifications = mysqli_query(
    $conn,
    "SELECT *
     FROM notifications
     WHERE user_id='$user_id'
     ORDER BY created_at DESC"
);

?>

<!DOCTYPE html>
<html>

<head>

<title>Notifications</title>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body>

<?php include("includes/sidebar.php"); ?>

<div class="container-fluid"
style="margin-left:300px;padding:20px;">

<h2 class="mb-4">

My Notifications

</h2>

<?php

if(mysqli_num_rows($notifications) == 0)
{
    echo '
    <div class="alert alert-info">
        No notifications found.
    </div>';
}

while($row = mysqli_fetch_assoc($notifications))
{

?>

<div class="card mb-3 shadow-sm">

<div class="card-body">

<h5>

<?php echo $row['title']; ?>

</h5>

<p>

<?php echo $row['message']; ?>

</p>

<span class="badge bg-primary">

<?php echo $row['notification_type']; ?>

</span>

<br><br>

<small class="text-muted">

<?php echo $row['created_at']; ?>

</small>

</div>

</div>

<?php } ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>