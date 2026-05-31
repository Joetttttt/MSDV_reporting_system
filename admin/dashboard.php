<?php

require_once "../config/database.php";
require_once "../config/session.php";

/*
|--------------------------------------------------------------------------
| Authentication Check
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

$totalStudents = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM students"
    )
);

$totalViolations = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM violation_reports"
    )
);

$pendingSanctions = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
        FROM disciplinary_actions
        WHERE status='Pending'"
    )
);

$completedCases = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
        FROM disciplinary_actions
        WHERE status='Completed'"
    )
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Dashboard</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<style>

body{
    background:#f8f9fa;
}

.sidebar{
    width:250px;
    min-height:100vh;
    background:#212529;
}

.sidebar a{
    color:white;
    text-decoration:none;
    display:block;
    padding:12px;
    border-radius:5px;
}

.sidebar a:hover{
    background:#343a40;
}

.card{
    border:none;
    box-shadow:0 2px 10px rgba(0,0,0,0.08);
}

</style>

</head>

<body>

<div class="d-flex">

    <!-- Sidebar -->

    <div class="sidebar p-3">

        <h4 class="text-white">
            MSDV System
        </h4>

        <hr class="text-white">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="student_records.php">
            Student Records
        </a>

        <a href="violation_records.php">
            Violation Records
        </a>

        <a href="disciplinary_actions.php">
            Disciplinary Actions
        </a>

        <a href="risk_levels.php">
            Risk Levels
        </a>

        <a href="student_appeals.php">
            Student Appeals
        </a>

        <a href="backup_export.php">
            Backup & Export
        </a>

        <a href="user_management.php">
            User Management
        </a>

        <a href="change_admin_password.php">
            Change Password
        </a>

        <a href="../logout.php" class="text-danger">
            Logout
        </a>

    </div>

    <!-- Main Content -->

    <div class="flex-grow-1 p-4">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <h2>Admin Dashboard</h2>

                <p class="text-muted">
                    Welcome,
                    <?php echo $_SESSION['fullname']; ?>
                </p>
            </div>

        </div>

        <!-- Cards -->

        <div class="row">

            <div class="col-md-3 mb-3">

                <div class="card">

                    <div class="card-body">

                        <h6>Total Students</h6>

                        <h2>
                            <?php echo $totalStudents['total']; ?>
                        </h2>

                    </div>

                </div>

            </div>

            <div class="col-md-3 mb-3">

                <div class="card">

                    <div class="card-body">

                        <h6>Total Violations</h6>

                        <h2>
                            <?php echo $totalViolations['total']; ?>
                        </h2>

                    </div>

                </div>

            </div>

            <div class="col-md-3 mb-3">

                <div class="card">

                    <div class="card-body">

                        <h6>Pending Sanctions</h6>

                        <h2>
                            <?php echo $pendingSanctions['total']; ?>
                        </h2>

                    </div>

                </div>

            </div>

            <div class="col-md-3 mb-3">

                <div class="card">

                    <div class="card-body">

                        <h6>Completed Cases</h6>

                        <h2>
                            <?php echo $completedCases['total']; ?>
                        </h2>

                    </div>

                </div>

            </div>

        </div>

        <!-- Placeholder -->

        <div class="card mt-4">

            <div class="card-body">

                <h5>
                    Dashboard Charts
                </h5>

                <p>
                    Charts will be added in the next step.
                </p>

            </div>

        </div>

    </div>

</div>

<script
src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>