<?php

include("../config/session.php");
include("../config/database.php");

$query = mysqli_query(
$conn,
"SELECT
da.*,
vr.violation_name,
s.full_name

FROM disciplinary_actions da

LEFT JOIN violation_reports vr
ON da.report_id = vr.report_id

LEFT JOIN students s
ON da.student_id = s.student_id

ORDER BY da.created_at DESC"
);

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Disciplinary Actions</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body>

<?php include("includes/sidebar.php"); ?>

<div
class="container-fluid"
style="margin-left:300px;padding:20px;">

<h2 class="mb-4">

Disciplinary Actions

</h2>

<table class="table table-bordered">

<thead>

<tr>

<th>Student ID</th>
<th>Student Name</th>
<th>Violation</th>
<th>Sanction</th>
<th>Start Date</th>
<th>End Date</th>
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
<?php echo $row['violation_name']; ?>
</td>

<td>
<?php echo $row['sanction']; ?>
</td>

<td>
<?php echo $row['start_date']; ?>
</td>

<td>
<?php echo $row['end_date']; ?>
</td>

<td>

<span class="badge bg-primary">

<?php echo $row['status']; ?>

</span>

</td>

<td>

<form
action="../ajax/update_action_status.php"
method="POST">

<input
type="hidden"
name="action_id"
value="<?php echo $row['action_id']; ?>">

<select
name="status"
class="form-select mb-2">

<option value="Pending">
Pending
</option>

<option value="Ongoing">
Ongoing
</option>

<option value="Completed">
Completed
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>