<?php

session_start();

include("../config/database.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SESSION['role'] != 'teacher') {
    header("Location: ../index.php");
    exit();
}

$fullname = $_SESSION['fullname'];

$query = "SELECT * FROM violations
          WHERE reported_by='$fullname'
          ORDER BY id DESC";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html>
<head>

<title>My Reports</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

.sidebar{
    height:100vh;
    background:#212529;
}

.sidebar a{
    color:white;
    text-decoration:none;
    display:block;
    padding:15px;
}

.sidebar a:hover{
    background:#343a40;
}

.badge-pending{
    background:orange;
}

.badge-resolved{
    background:green;
}

</style>

</head>

<body>

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-2 sidebar p-0">

            <h4 class="text-white text-center py-3">
                TEACHER
            </h4>

            <a href="report_violation.php">
                Report Violation
            </a>

            <a href="my_reports.php">
                My Reports
            </a>

            <a href="../auth/logout.php">
                Logout
            </a>

        </div>

        <!-- CONTENT -->
        <div class="col-md-10 p-4">

            <h2 class="mb-4">
                My Submitted Reports
            </h2>

            <table class="table table-bordered table-striped">

                <thead class="table-dark">

                    <tr>

                        <th>ID</th>
                        <th>Student</th>
                        <th>Violation</th>
                        <th>Description</th>
                        <th>Evidence</th>
                        <th>Status</th>
                        <th>Date</th>

                    </tr>

                </thead>

                <tbody>

                <?php while($row = mysqli_fetch_assoc($result)) { ?>

                    <tr>

                        <td><?= $row['id']; ?></td>

                        <td>

                            <?= $row['student_name']; ?>

                            <br>

                            <small>
                                <?= $row['student_id']; ?>
                            </small>

                        </td>

                        <td>
                            <?= $row['violation_type']; ?>
                        </td>

                        <td>
                            <?= $row['description']; ?>
                        </td>

                        <td>

                            <?php if($row['evidence'] != "") { ?>

                                <a
                                    href="../uploads/<?= $row['evidence']; ?>"
                                    target="_blank"
                                    class="btn btn-sm btn-primary">

                                    View File

                                </a>

                            <?php } else { ?>

                                No File

                            <?php } ?>

                        </td>

                        <td>

                            <?php if($row['status'] == 'Pending') { ?>

                                <span class="badge badge-pending">
                                    Pending
                                </span>

                            <?php } else { ?>

                                <span class="badge badge-resolved">
                                    Resolved
                                </span>

                            <?php } ?>

                        </td>

                        <td>
                            <?= $row['created_at']; ?>
                        </td>

                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</body>
</html>