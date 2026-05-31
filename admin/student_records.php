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
<h3>Add Student</h3>

<form action="save_student.php" method="POST">

    Student ID<br>
    <input type="text" name="student_id" required>
    <br><br>

    First Name<br>
    <input type="text" name="first_name" required>
    <br><br>

    Middle Name<br>
    <input type="text" name="middle_name">
    <br><br>

    Last Name<br>
    <input type="text" name="last_name" required>
    <br><br>

    Course<br>
    <input type="text" name="course" required>
    <br><br>

    Year Level<br>
    <select name="year_level">
        <option>1st Year</option>
        <option>2nd Year</option>
        <option>3rd Year</option>
        <option>4th Year</option>
    </select>

    <br><br>

    Department<br>
    <select name="department">
        <option>School of Technology</option>
        <option>School of Education</option>
        <option>School of Business</option>
    </select>

    <br><br>

    <button type="submit">
        Save Student
    </button>

</form>
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