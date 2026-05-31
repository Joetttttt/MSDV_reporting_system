<?php
include("../config/session.php");
include("../config/database.php");
?>

<!DOCTYPE html>
<html>

<head>

<title>Report Violation</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body>

<?php include("includes/sidebar.php"); ?>

<div class="container-fluid"
style="margin-left:300px;padding:20px;">

<h2 class="mb-4">

Report Violation

</h2>

<form
action="../ajax/save_report.php"
method="POST"
enctype="multipart/form-data">

<div class="row">

<div class="col-md-6">

<label>

Student ID

</label>

<input
type="text"
name="student_id"
id="student_id"
class="form-control"
required>

</div>

<div class="col-md-6">

<label>

Student Name

</label>

<input
type="text"
id="student_name"
class="form-control"
readonly>

</div>

</div>

<br>

<div class="row">

<div class="col-md-6">

<label>

Category

</label>

<select
name="category"
class="form-control">

<option>Minor</option>
<option>Major</option>

</select>

</div>

<div class="col-md-6">

<label>

Violation

</label>

<input
type="text"
name="violation_name"
class="form-control"
required>

</div>

</div>

<br>

<label>

Description

</label>

<textarea
name="description"
class="form-control"
rows="4"></textarea>

<br>

<label>

Evidence Image

</label>

<input
type="file"
name="evidence_image"
class="form-control">

<br>

<button
class="btn btn-primary">

Submit Report

</button>

</form>

</div>

<script>

document
.getElementById("student_id")
.addEventListener(
"change",
function(){

fetch(
"../ajax/get_student_info.php?student_id="
+ this.value
)

.then(response => response.json())

.then(data => {

document
.getElementById("student_name")
.value =
data.full_name;

});

});

</script>

</body>
</html>