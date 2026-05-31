<?php

include("../config/session.php");
include("../config/database.php");

$query = mysqli_query(
$conn,
"SELECT
a.*,
s.full_name

FROM appeals a

LEFT JOIN students s
ON a.student_id=s.student_id

ORDER BY a.submitted_at DESC"
);

?>

<!DOCTYPE html>
<html>

<head>

<title>Student Appeals</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body>

<?php include("includes/sidebar.php"); ?>

<div
class="container-fluid"
style="margin-left:300px;padding:20px;">

<h2 class="mb-4">

Student Appeals

</h2>

<table class="table table-bordered">

<thead>

<tr>

<th>Student ID</th>
<th>Student Name</th>
<th>Reason</th>
<th>Status</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php while($row = mysqli_fetch_assoc($query)) { ?>

<tr>

<td>

<?php echo $row['student_id']; ?>

</td>

<td>

<?php echo $row['full_name']; ?>

</td>

<td>

<?php echo $row['appeal_reason']; ?>

</td>

<td>

<?php echo $row['status']; ?>

</td>

<td>

<form
action="../ajax/update_appeal.php"
method="POST">

<input
type="hidden"
name="appeal_id"
value="<?php echo $row['appeal_id']; ?>">

<select
name="status"
class="form-select mb-2">

<option value="Approved">

Approved

</option>

<option value="Rejected">

Rejected

</option>

</select>

<button
class="btn btn-success btn-sm">

Update

</button>

</form>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</body>
</html>