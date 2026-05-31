<?php

include("../config/session.php");
include("../config/database.php");

$query = mysqli_query(
    $conn,
    "SELECT vr.*,
            s.full_name
     FROM violation_reports vr
     LEFT JOIN students s
     ON vr.student_id = s.student_id
     ORDER BY vr.submitted_at DESC"
);

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Violation Records</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body>

<?php include("includes/sidebar.php"); ?>

<div class="container-fluid"
style="margin-left:300px;padding:20px;">

<h2 class="mb-4">

Violation Records

</h2>

<div class="row mb-3">

<div class="col-md-4">

<input
type="text"
id="searchInput"
class="form-control"
placeholder="Search Student ID">

</div>

<div class="col-md-3">

<select
id="categoryFilter"
class="form-control">

<option value="">
All Categories
</option>

<option value="Minor">
Minor
</option>

<option value="Major">
Major
</option>

</select>

</div>

<div class="col-md-3">

<select
id="statusFilter"
class="form-control">

<option value="">
All Status
</option>

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

</div>

</div>

<table
class="table table-bordered table-striped"
id="violationTable">

<thead>

<tr>

<th>Report No.</th>
<th>Student ID</th>
<th>Student Name</th>
<th>Category</th>
<th>Violation</th>
<th>Status</th>
<th>Date</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php while($row = mysqli_fetch_assoc($query)) { ?>

<tr>

<td>
<?php echo $row['report_number']; ?>
</td>

<td>
<?php echo $row['student_id']; ?>
</td>

<td>
<?php echo $row['full_name']; ?>
</td>

<td>
<?php echo $row['category']; ?>
</td>

<td>
<?php echo $row['violation_name']; ?>
</td>

<td>

<span class="badge bg-warning">

<?php echo $row['status']; ?>

</span>

</td>

<td>

<?php echo date(
"Y-m-d",
strtotime($row['submitted_at'])
); ?>

</td>

<td>

<button
class="btn btn-success btn-sm"
data-bs-toggle="modal"
data-bs-target="#actionModal<?php echo $row['report_id']; ?>">
Create Action
</button>


</td>

</tr>

<!-- VIEW MODAL -->

<div
class="modal fade"
id="viewModal<?php echo $row['report_id']; ?>">

<div class="modal-dialog modal-lg">

<div class="modal-content">

<div class="modal-header">

<h5>

Violation Details

</h5>

</div>

<div class="modal-body">

<p>

<strong>Report Number:</strong>

<?php echo $row['report_number']; ?>

</p>

<p>

<strong>Student ID:</strong>

<?php echo $row['student_id']; ?>

</p>

<p>

<strong>Category:</strong>

<?php echo $row['category']; ?>

</p>

<p>

<strong>Violation:</strong>

<?php echo $row['violation_name']; ?>

</p>

<p>

<strong>Description:</strong>

<br>

<?php echo $row['description']; ?>

</p>

<?php if(!empty($row['evidence_image'])) { ?>

<img
src="../assets/uploads/evidence/<?php echo $row['evidence_image']; ?>"
class="img-fluid border">

<?php } ?>

</div>

</div>

</div>

</div>

<?php } ?>

</tbody>

</table>

</div>

<script>

document
.getElementById("searchInput")
.addEventListener("keyup", function() {

let value =
this.value.toLowerCase();

let rows =
document.querySelectorAll(
"#violationTable tbody tr"
);

rows.forEach(row => {

let text =
row.innerText.toLowerCase();

row.style.display =
text.includes(value)
? ""
: "none";

});

});

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>