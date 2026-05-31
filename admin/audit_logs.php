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

<title>Audit Logs</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body>

<?php include("includes/sidebar.php"); ?>

<div
class="container-fluid"
style="margin-left:300px;padding:20px;">

<h2 class="mb-4">

Audit Logs

</h2>

<div class="row mb-3">

<div class="col-md-4">

<input
type="text"
id="searchInput"
class="form-control"
placeholder="Search User">

</div>

<div class="col-md-4">

<input
type="date"
id="dateInput"
class="form-control">

</div>

</div>

<table
class="table table-bordered table-striped"
id="auditTable">

<thead>

<tr>

<th>ID</th>
<th>User</th>
<th>Role</th>
<th>Action</th>
<th>IP Address</th>
<th>Date</th>

</tr>

</thead>

<tbody>

<?php

$logs = mysqli_query(
$conn,
"SELECT *
FROM audit_logs
ORDER BY created_at DESC"
);

while($row = mysqli_fetch_assoc($logs))
{

?>

<tr>

<td>

<?php echo $row['log_id']; ?>

</td>

<td>

<?php echo $row['full_name']; ?>

</td>

<td>

<?php echo strtoupper($row['role']); ?>

</td>

<td>

<?php echo $row['action']; ?>

</td>

<td>

<?php echo $row['ip_address']; ?>

</td>

<td>

<?php echo $row['created_at']; ?>

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
"#auditTable tbody tr"
);

rows.forEach(row => {

row.style.display =
row.innerText
.toLowerCase()
.includes(value)
? ""
: "none";

});

});

document
.getElementById("dateInput")
.addEventListener(
"change",
function(){

let value =
this.value;

let rows =
document.querySelectorAll(
"#auditTable tbody tr"
);

rows.forEach(row => {

row.style.display =
row.innerText.includes(value)
? ""
: "none";

});

});

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>