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
    <title>Admin Dashboard</title>

    <style>

    body{
        margin:0;
        font-family:Arial;
    }

    .container{
        display:flex;
    }

    .sidebar{
        width:250px;
        min-height:100vh;
        border-right:1px solid #ccc;
        padding:20px;
    }

    .sidebar a{
        display:block;
        text-decoration:none;
        margin-bottom:15px;
    }

    .content{
        flex:1;
        padding:20px;
    }

    .cards{
        display:flex;
        gap:20px;
        flex-wrap:wrap;
    }

    .card{
        border:1px solid #ccc;
        padding:20px;
        width:220px;
    }

    .chart-box{
        border:1px solid #ccc;
        min-height:250px;
        margin-top:20px;
        padding:20px;
    }

    </style>

</head>
<body>

<div class="container">

    <!-- SIDEBAR -->

    <div class="sidebar">

        <h2>MSDV</h2>

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
            Risk Level Indicator
        </a>

        <a href="appeals.php">
            Student Appeals
        </a>

        <a href="backup_export.php">
            Backup & Export
        </a>

        <a href="user_management.php">
            User Management
        </a>

        <a href="notifications.php">
            Notifications
        </a>

        <a href="../auth/logout.php">
            Logout
        </a>

    </div>

    <!-- CONTENT -->

    <div class="content">

        <h1>Admin Dashboard</h1>

        <p>
            Welcome,
            <?php echo $_SESSION['fullname']; ?>
        </p>

        <hr>

        <div class="cards">

            <div class="card">
                <h3>Total Students</h3>
                <h1>0</h1>
            </div>

            <div class="card">
                <h3>Total Violations</h3>
                <h1>0</h1>
            </div>

            <div class="card">
                <h3>Pending Sanctions</h3>
                <h1>0</h1>
            </div>

            <div class="card">
                <h3>Completed Cases</h3>
                <h1>0</h1>
            </div>

        </div>

        <div class="chart-box">
            Violation Per Month Chart
        </div>

        <div class="chart-box">
            Monthly Minor vs Major Violations
        </div>

        <div class="chart-box">
            Violation By Department
        </div>

        <div class="chart-box">
            Violation By Course
        </div>

        <div class="chart-box">
            Specific Violation By Department
        </div>

    </div>

</div>

</body>
</html>