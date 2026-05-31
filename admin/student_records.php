<?php
include("../config/session.php");
include("../config/database.php");

$students = mysqli_query(
    $conn,
    "SELECT * FROM students ORDER BY full_name ASC"
);
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Student Records</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<?php include("includes/sidebar.php"); ?>

<div class="container-fluid" style="margin-left:300px; padding:20px;">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h2>Student Records</h2>

        <button
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#addStudentModal">

            Add Student

        </button>

    </div>

    <div class="row mb-3">

        <div class="col-md-4">

            <input
                type="text"
                id="searchStudent"
                class="form-control"
                placeholder="Search Student ID">

        </div>

    </div>

    <table class="table table-bordered table-striped">

        <thead>

        <tr>

            <th>Student ID</th>
            <th>Full Name</th>
            <th>Course</th>
            <th>Year Level</th>
            <th>Department</th>
            <th>Risk Level</th>
            <th>Action</th>

        </tr>

        </thead>

        <tbody id="studentTable">

        <?php while($row = mysqli_fetch_assoc($students)) { ?>

        <tr>

            <td><?php echo $row['student_id']; ?></td>
            <td><?php echo $row['full_name']; ?></td>
            <td><?php echo $row['course']; ?></td>
            <td><?php echo $row['year_level']; ?></td>
            <td><?php echo $row['department']; ?></td>

            <td>
                <span class="badge bg-success">
                    Normal
                </span>
            </td>

            <td>

                <button
                    class="btn btn-info btn-sm viewBtn"
                    data-id="<?php echo $row['student_id']; ?>">

                    View

                </button>

                <button
                    class="btn btn-warning btn-sm editBtn"
                    data-id="<?php echo $row['student_id']; ?>">

                    Edit

                </button>

                <button
                    class="btn btn-danger btn-sm deleteBtn"
                    data-id="<?php echo $row['student_id']; ?>">

                    Delete

                </button>

            </td>

        </tr>

        <?php } ?>

        </tbody>

    </table>

</div>

<!-- ADD STUDENT MODAL -->

<div class="modal fade" id="addStudentModal">

<div class="modal-dialog">

<div class="modal-content">

<div class="modal-header">

<h5>Add Student</h5>

</div>

<div class="modal-body">

<form id="addStudentForm">

<input
type="text"
name="student_id"
class="form-control mb-2"
placeholder="Student ID"
maxlength="9"
oninput="formatStudentID(this)"
required>

<input
type="text"
name="full_name"
class="form-control mb-2"
placeholder="Full Name"
required>

<input
type="text"
name="course"
class="form-control mb-2"
placeholder="Course"
required>

<select
name="year_level"
class="form-control mb-2">

<option value="1">1</option>
<option value="2">2</option>
<option value="3">3</option>
<option value="4">4</option>

</select>

<select
name="department"
class="form-control mb-2">

<option>School of Technology</option>
<option>School of Education</option>
<option>School of Business</option>

</select>

<button
type="submit"
class="btn btn-success">

Save Student

</button>

</form>

</div>

</div>

</div>

</div>

<!-- VIEW MODAL -->

<div class="modal fade" id="viewModal">

<div class="modal-dialog">

<div class="modal-content">

<div class="modal-header">

<h5>Student Information</h5>

</div>

<div class="modal-body" id="viewContent">

</div>

</div>

</div>

</div>

<!-- EDIT MODAL -->

<div class="modal fade" id="editModal">

<div class="modal-dialog">

<div class="modal-content">

<div class="modal-header">

<h5>Edit Student</h5>

</div>

<div class="modal-body">

<form id="editStudentForm">

<input type="hidden" name="student_id" id="edit_student_id">

<input
type="text"
name="full_name"
id="edit_full_name"
class="form-control mb-2">

<input
type="text"
name="course"
id="edit_course"
class="form-control mb-2">

<select
name="year_level"
id="edit_year_level"
class="form-control mb-2">

<option value="1">1</option>
<option value="2">2</option>
<option value="3">3</option>
<option value="4">4</option>

</select>

<select
name="department"
id="edit_department"
class="form-control mb-2">

<option>School of Technology</option>
<option>School of Education</option>
<option>School of Business</option>

</select>

<button
type="submit"
class="btn btn-primary">

Update Student

</button>

</form>

</div>

</div>

</div>

</div>

<!-- DELETE MODAL -->

<div class="modal fade" id="deleteModal">

<div class="modal-dialog">

<div class="modal-content">

<div class="modal-header">

<h5>Delete Student</h5>

</div>

<div class="modal-body">

<form id="deleteForm">

<input
type="hidden"
name="student_id"
id="delete_student_id">

<input
type="password"
name="admin_password"
class="form-control mb-3"
placeholder="Enter Admin Password">

<button
type="submit"
class="btn btn-danger">

Delete

</button>

</form>

</div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="../assets/js/student.js"></script>

</body>
</html>