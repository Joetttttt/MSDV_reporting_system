<?php
include("../config/auth.php");

if($_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Records</title>
</head>
<body>

<h1>Student Records</h1>

<button>Add Student</button>

<br><br>

<table border="1">

<tr>
    <th>Student ID</th>
    <th>Full Name</th>
    <th>Course</th>
    <th>Year Level</th>
    <th>Department</th>
    <th>Risk Level</th>
    <th>Action</th>
</tr>

</table>

</body>
</html>