<?php

include("../config/session.php");
include("../config/database.php");

?>

<!DOCTYPE html>
<html>

<head>

<title>Backup & Export</title>

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

Backup & Export

</h2>

<div class="row">

<div class="col-md-6">

<div class="card shadow mb-4">

<div class="card-header">

Student Reports

</div>

<div class="card-body">

<a
href="../exports/students_export.php"
class="btn btn-success">

Export Students CSV

</a>

</div>

</div>

</div>

<div class="col-md-6">

<div class="card shadow mb-4">

<div class="card-header">

Violation Reports

</div>

<div class="card-body">

<a
href="../exports/violations_export.php"
class="btn btn-danger">

Export Violations CSV

</a>

</div>

</div>

</div>

<div class="col-md-6">

<div class="card shadow mb-4">

<div class="card-header">

Appeals Reports

</div>

<div class="card-body">

<a
href="../exports/appeals_export.php"
class="btn btn-warning">

Export Appeals CSV

</a>

</div>

</div>

</div>

<div class="col-md-6">

<div class="card shadow mb-4">

<div class="card-header">

Database Backup

</div>

<div class="card-body">

<a
href="../exports/database_backup.php"
class="btn btn-primary">

Download SQL Backup

</a>

</div>

</div>

</div>

</div>

</div>

</body>
</html>