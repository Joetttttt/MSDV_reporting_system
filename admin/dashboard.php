<?php
include("../config/session.php");
include("../config/database.php");

$totalStudents =
mysqli_num_rows(
mysqli_query($conn,"SELECT * FROM students")
);

$totalViolations =
mysqli_num_rows(
mysqli_query($conn,"SELECT * FROM violation_reports")
);

$pendingSanctions =
mysqli_num_rows(
mysqli_query(
$conn,
"SELECT * FROM disciplinary_actions
 WHERE status='Pending'"
)
);

$completedCases =
mysqli_num_rows(
mysqli_query(
$conn,
"SELECT * FROM disciplinary_actions
 WHERE status='Completed'"
)
);
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Admin Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

<?php include("includes/sidebar.php"); ?>

<div style="margin-left:300px;"
class="p-4">

<h2 class="mb-4">
Dashboard
</h2>

<div class="row">

<div class="col-md-3">

<div class="card text-bg-primary mb-3">

<div class="card-body">

<h5>Total Students</h5>

<h2>
<?php echo $totalStudents; ?>
</h2>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card text-bg-danger mb-3">

<div class="card-body">

<h5>Total Violations</h5>

<h2>
<?php echo $totalViolations; ?>
</h2>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card text-bg-warning mb-3">

<div class="card-body">

<h5>Pending Sanctions</h5>

<h2>
<?php echo $pendingSanctions; ?>
</h2>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card text-bg-success mb-3">

<div class="card-body">

<h5>Completed Cases</h5>

<h2>
<?php echo $completedCases; ?>
</h2>

</div>

</div>

</div>

</div>

<div class="row">

<div class="col-md-6">

<div class="card">

<div class="card-header">

Violation Per Month

</div>

<div class="card-body">

<canvas id="monthlyChart"></canvas>

</div>

</div>

</div>

<div class="col-md-6">

<div class="card">

<div class="card-header">

Minor vs Major

</div>

<div class="card-body">

<canvas id="minorMajorChart"></canvas>

</div>

</div>

</div>

</div>

<br>

<div class="row">

<div class="col-md-6">

<div class="card">

<div class="card-header">

Violation By Department

</div>

<div class="card-body">

<canvas id="departmentChart"></canvas>

</div>

</div>

</div>

<div class="col-md-6">

<div class="card">

<div class="card-header">

Violation By Course

</div>

<div class="card-body">

<canvas id="courseChart"></canvas>

</div>

</div>

</div>

</div>

<br>

<div class="card">

<div class="card-header">

Specific Violation By Department

</div>

<div class="card-body">

<canvas id="specificChart"></canvas>

</div>

</div>

</div>

<script>

new Chart(
document.getElementById('monthlyChart'),
{
type:'bar',
data:{
labels:['Jan','Feb','Mar','Apr'],
datasets:[
{
label:'Violations',
data:[0,0,0,0]
}
]
}
}
);

new Chart(
document.getElementById('minorMajorChart'),
{
type:'bar',
data:{
labels:['Jan','Feb','Mar','Apr'],
datasets:[
{
label:'Minor',
data:[0,0,0,0]
},
{
label:'Major',
data:[0,0,0,0]
}
]
}
}
);

new Chart(
document.getElementById('departmentChart'),
{
type:'pie',
data:{
labels:[
'SOT',
'SOE',
'SOB'
],
datasets:[
{
data:[0,0,0]
}
]
}
}
);

new Chart(
document.getElementById('courseChart'),
{
type:'bar',
data:{
labels:['Course'],
datasets:[
{
label:'Violations',
data:[0]
}
]
}
}
);

new Chart(
document.getElementById('specificChart'),
{
type:'line',
data:{
labels:['Jan','Feb','Mar'],
datasets:[
{
label:'SOT',
data:[0,0,0]
},
{
label:'SOE',
data:[0,0,0]
},
{
label:'SOB',
data:[0,0,0]
}
]
}
}
);

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>