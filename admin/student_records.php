<?php

include("../config/auth.php");
include("../config/database.php");

if($_SESSION['role'] != 'admin'){
    header("Location: ../auth/login.php");
    exit();
}

$search = $_GET['search'] ?? '';
$course = $_GET['course'] ?? '';
$year = $_GET['year'] ?? '';
$department = $_GET['department'] ?? '';

$sql = "SELECT * FROM students WHERE 1=1";

if($search != ''){
    $sql .= " AND student_id LIKE '%$search%'";
}

if($course != ''){
    $sql .= " AND course='$course'";
}

if($year != ''){
    $sql .= " AND year_level='$year'";
}

if($department != ''){
    $sql .= " AND department='$department'";
}

$sql .= " ORDER BY last_name ASC";

$result = mysqli_query($conn,$sql);

?>

<!DOCTYPE html>
<html>
<head>
<title>Student Records</title>
</head>
<body>

<h1>Student Records</h1>

<a href="dashboard.php">Dashboard</a> |
<a href="student_records.php">Student Records</a> |
<a href="../auth/logout.php">Logout</a>

<hr>

<a href="add_student.php">
<button>Add Student</button>
</a>

<br><br>

<form method="GET">

Search Student ID

<input
type="text"
name="search"
value="<?php echo $search; ?>">

Course

<input
type="text"
name="course"
value="<?php echo $course; ?>">

Year

<select name="year">

<option value="">All</option>

<option>1st Year</option>
<option>2nd Year</option>
<option>3rd Year</option>
<option>4th Year</option>

</select>

Department

<select name="department">

<option value="">All</option>

<option>School of Technology</option>
<option>School of Education</option>
<option>School of Business</option>

</select>

<button type="submit">
Filter
</button>

<a href="student_records.php">
Reset
</a>

</form>

<br>

<table border="1" width="100%">

<tr>

<th>Student ID</th>
<th>Full Name</th>
<th>Course</th>
<th>Year Level</th>
<th>Department</th>
<th>Risk Level</th>
<th>Action</th>

</tr>

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo $row['student_id']; ?></td>

<td>

<?php

echo
$row['first_name'].' '.
$row['middle_name'].' '.
$row['last_name'];

?>

</td>

<td><?php echo $row['course']; ?></td>

<td><?php echo $row['year_level']; ?></td>

<td><?php echo $row['department']; ?></td>

<td>Low</td>

<td>

<a href="view_student.php?student_id=<?php echo $row['student_id']; ?>">
View
</a>

|

<a href="edit_student.php?student_id=<?php echo $row['student_id']; ?>">
Edit
</a>

|

<a href="delete_student.php?student_id=<?php echo $row['student_id']; ?>">
Delete
</a>

</td>

</tr>

<?php } ?>

</table>

</body>
</html>