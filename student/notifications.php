<?php

include("../config/session.php");
include("../config/database.php");

$user_id = $_SESSION['user_id'];

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

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body>

<?php include("includes/sidebar.php"); ?>

<div
class="container-fluid"
style="margin-left:300px;padding:20px;">

<h2 class="mb-4">

Notifications

</h2>

<?php while($row = mysqli_fetch_assoc($notifications)) { ?>

<div class="card mb-3">

<div class="card-body">

<h5>

<?php echo $row['title']; ?>

</h5>

<p>

<?php echo $row['message']; ?>

</p>

<small>

<?php echo $row['created_at']; ?>

</small>

</div>

</div>

<?php } ?>

</div>

</body>
</html>