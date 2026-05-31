<?php

include("../config/session.php");
include("../config/database.php");

$student_id = $_SESSION['student_id'];

$query = mysqli_query(
$conn,
"SELECT
da.*,
vr.violation_name

FROM disciplinary_actions da

LEFT JOIN violation_reports vr
ON da.report_id = vr.report_id

WHERE da.student_id='$student_id'

ORDER BY da.created_at DESC"
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

<div class="container-fluid"
style="margin-left:300px;padding:20px;">

<h2 class="mb-4">

My Disciplinary Actions

</h2>

<table class="table table-bordered">

<thead>

<tr>

<th>Violation</th>
<th>Sanction</th>
<th>Status</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php while($row = mysqli_fetch_assoc($query)) { ?>

<tr>

<td>

<?php echo $row['violation_name']; ?>

</td>

<td>

<?php echo $row['sanction']; ?>

</td>

<td>

<?php echo $row['status']; ?>

</td>

<td>

<button
class="btn btn-primary btn-sm"
data-bs-toggle="modal"
data-bs-target="#appealModal<?php echo $row['action_id']; ?>">

Submit Appeal

</button>

</td>

</tr>

<div
class="modal fade"
id="appealModal<?php echo $row['action_id']; ?>">

<div class="modal-dialog">

<div class="modal-content">

<div class="modal-header">

<h5>

Submit Appeal

</h5>

</div>

<div class="modal-body">

<form
action="../ajax/submit_appeal.php"
method="POST">

<input
type="hidden"
name="report_id"
value="<?php echo $row['report_id']; ?>">

<input
type="hidden"
name="student_id"
value="<?php echo $student_id; ?>">

<textarea
name="appeal_reason"
class="form-control mb-3"
rows="5"
required></textarea>

<button
class="btn btn-success">

Submit Appeal

</button>

</form>

</div>

</div>

</div>

</div>

<?php } ?>

</tbody>

</table>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>