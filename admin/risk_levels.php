<?php

include("../config/session.php");
include("../config/database.php");

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Risk Level Indicator</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body>

<?php include("includes/sidebar.php"); ?>

<div
class="container-fluid"
style="margin-left:300px;padding:20px;">

<h2 class="mb-4">

Risk Level Indicator

</h2>

<div class="row mb-3">

<div class="col-md-4">

<input
type="text"
id="searchInput"
class="form-control"
placeholder="Search Student ID">

</div>

</div>

<table
class="table table-bordered table-striped"
id="riskTable">

<thead>

<tr>

<th>Student ID</th>
<th>Student Name</th>
<th>Minor Violations</th>
<th>Major Violations</th>
<th>Risk Level</th>

</tr>

</thead>

<tbody>

<?php

$students = mysqli_query(
$conn,
"SELECT * FROM students
ORDER BY full_name ASC"
);

while($student =
mysqli_fetch_assoc($students))
{

$student_id =
$student['student_id'];

$minorCount =
mysqli_num_rows(
mysqli_query(
$conn,
"SELECT *
FROM violation_reports
WHERE student_id='$student_id'
AND category='Minor'"
)
);

$majorCount =
mysqli_num_rows(
mysqli_query(
$conn,
"SELECT *
FROM violation_reports
WHERE student_id='$student_id'
AND category='Major'"
)
);

$risk = "Normal";
$badge = "success";

if($minorCount >= 1 && $minorCount <= 4)
{
$risk = "Low Risk";
$badge = "info";
}

if($minorCount >= 5)
{
$risk = "Moderate Risk";
$badge = "warning";
}

if($majorCount >= 1 && $majorCount <= 2)
{
$risk = "High Risk";
$badge = "danger";
}

if($majorCount >= 3)
{
$risk = "Critical Risk";
$badge = "dark";
}

?>

<tr>

<td>
<?php echo $student_id; ?>
</td>

<td>
<?php echo $student['full_name']; ?>
</td>

<td>
<?php echo $minorCount; ?>
</td>

<td>
<?php echo $majorCount; ?>
</td>

<td>

<span
class="badge bg-<?php echo $badge; ?>">

<?php echo $risk; ?>

</span>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<script>

document
.getElementById("searchInput")
.addEventListener(
"keyup",
function(){

let value =
this.value.toLowerCase();

let rows =
document.querySelectorAll(
"#riskTable tbody tr"
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