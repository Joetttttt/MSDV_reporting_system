<?php

require_once "../config/database.php";
require_once "../config/session.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

$totalStudents = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM students")
);

$totalViolations = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM violation_reports")
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

$notificationCount = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
        FROM notifications
        WHERE user_id='{$_SESSION['user_id']}'
        AND is_read=0"
    )
);

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>MSDV Admin Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    background:#f4f7fc;
    font-family:'Segoe UI',sans-serif;
}

/* SIDEBAR */

.sidebar{
    position:fixed;
    left:0;
    top:0;
    width:260px;
    height:100vh;
    background:#0f172a;
    z-index:1000;
    overflow-y:auto;
}

.logo{
    color:#fff;
    text-align:center;
    padding:25px;
    font-size:22px;
    font-weight:bold;
    border-bottom:1px solid rgba(255,255,255,.1);
}

.sidebar a{
    display:block;
    padding:15px 20px;
    color:white;
    text-decoration:none;
    transition:.3s;
}

.sidebar a:hover{
    background:#1e293b;
}

.sidebar a.active{
    background:#2563eb;
}

.logout{
    color:#ff6b6b !important;
}

/* NAVBAR */

.topbar{
    position:fixed;
    top:0;
    left:260px;
    right:0;
    height:70px;
    background:white;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:0 25px;
    box-shadow:0 2px 10px rgba(0,0,0,.08);
    z-index:999;
}

.topbar-title{
    font-size:24px;
    font-weight:600;
}

.topbar-right{
    display:flex;
    align-items:center;
    gap:20px;
}

.notification{
    position:relative;
    cursor:pointer;
    font-size:24px;
}

.badge-notif{
    position:absolute;
    top:-5px;
    right:-8px;
    background:red;
    color:white;
    border-radius:50%;
    width:18px;
    height:18px;
    font-size:10px;
    display:flex;
    justify-content:center;
    align-items:center;
}

/* MAIN */

.main-content{
    margin-left:260px;
    margin-top:70px;
    padding:25px;
}

/* CARDS */

.stats-card{
    background:white;
    border-radius:12px;
    padding:20px;
    box-shadow:0 2px 10px rgba(0,0,0,.08);
}

.stats-card h6{
    color:#6c757d;
    margin-bottom:10px;
}

.stats-card h2{
    margin:0;
    font-weight:bold;
}

/* CHARTS */

.chart-card{
    background:white;
    border-radius:12px;
    padding:20px;
    margin-top:20px;
    box-shadow:0 2px 10px rgba(0,0,0,.08);
}

.chart-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:15px;
}

.chart-header h5{
    margin:0;
}

canvas{
    max-height:400px;
}

</style>

</head>

<body>

<!-- SIDEBAR -->

<div class="sidebar">

    <div class="logo">
        MSDV System
    </div>

    <a href="dashboard.php" class="active">
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

    <a href="../logout.php" class="logout">
        Logout
    </a>

</div>

<!-- TOP BAR -->

<div class="topbar">

    <div class="topbar-title">
        Dashboard
    </div>

    <div class="topbar-right">

        <div class="notification">

            🔔

            <?php if($notificationCount['total'] > 0){ ?>

            <span class="badge-notif">
                <?= $notificationCount['total']; ?>
            </span>

            <?php } ?>

        </div>

        <strong>
            <?= $_SESSION['fullname']; ?>
        </strong>

    </div>

</div>

<!-- MAIN CONTENT -->

<div class="main-content">

    <!-- STAT CARDS -->

    <div class="row">

        <div class="col-md-3">

            <div class="stats-card">

                <h6>Total Students</h6>

                <h2>
                    <?= $totalStudents['total']; ?>
                </h2>

            </div>

        </div>

        <div class="col-md-3">

            <div class="stats-card">

                <h6>Total Violations</h6>

                <h2>
                    <?= $totalViolations['total']; ?>
                </h2>

            </div>

        </div>

        <div class="col-md-3">

            <div class="stats-card">

                <h6>Pending Sanctions</h6>

                <h2>
                    <?= $pendingSanctions['total']; ?>
                </h2>

            </div>

        </div>

        <div class="col-md-3">

            <div class="stats-card">

                <h6>Completed Cases</h6>

                <h2>
                    <?= $completedCases['total']; ?>
                </h2>

            </div>

        </div>

    </div>

    <!-- CHART 1 -->

    <div class="chart-card">

        <div class="chart-header">

            <h5>Violations Per Month</h5>

            <select class="form-select w-auto">
                <option>2026</option>
                <option>2025</option>
            </select>

        </div>

        <canvas id="monthlyViolations"></canvas>

    </div>

    <!-- CHART 2 -->

    <div class="chart-card">

        <div class="chart-header">

            <h5>Monthly Minor vs Major Violations</h5>

            <select class="form-select w-auto">
                <option>2026</option>
                <option>2025</option>
            </select>

        </div>

        <canvas id="minorMajorChart"></canvas>

    </div>

    <!-- CHART 3 -->

    <div class="chart-card">

        <div class="chart-header">

            <h5>Violations by Department</h5>

        </div>

        <canvas id="departmentChart"></canvas>

    </div>

    <!-- CHART 4 -->

    <div class="chart-card">

        <div class="chart-header">

            <h5>Violations by Course</h5>

            <select class="form-select w-auto">
                <option>2026</option>
                <option>2025</option>
            </select>

        </div>

        <canvas id="courseChart"></canvas>

    </div>

    <!-- CHART 5 -->

    <div class="chart-card">

        <div class="chart-header">

            <h5>Specific Violation by Department</h5>

            <div class="d-flex gap-2">

                <select class="form-select">

                    <option>Select Violation</option>

                </select>

                <select class="form-select">

                    <option>2026</option>

                </select>

            </div>

        </div>

        <canvas id="specificViolationChart"></canvas>

    </div>

</div>

<script>

/* SAMPLE DATA
Replace with database values later
*/

new Chart(
document.getElementById('monthlyViolations'),
{
    type:'bar',
    data:{
        labels:['Jan','Feb','Mar','Apr','May','Jun'],
        datasets:[{
            label:'Violations',
            data:[5,8,12,7,10,15]
        }]
    }
});

new Chart(
document.getElementById('minorMajorChart'),
{
    type:'bar',
    data:{
        labels:['Jan','Feb','Mar','Apr','May'],
        datasets:[
            {
                label:'Minor',
                data:[5,7,3,8,10],
                backgroundColor:'blue'
            },
            {
                label:'Major',
                data:[2,4,5,2,1],
                backgroundColor:'red'
            }
        ]
    }
});

new Chart(
document.getElementById('departmentChart'),
{
    type:'bar',
    data:{
        labels:[
            'School of Technology',
            'School of Education',
            'School of Business'
        ],
        datasets:[{
            data:[20,12,15],
            backgroundColor:[
                'red',
                'blue',
                'orange'
            ]
        }]
    }
});

new Chart(
document.getElementById('courseChart'),
{
    type:'bar',
    data:{
        labels:[
            'BSIT',
            'BIT CompTech',
            'BEEd',
            'BSEd Math',
            'BSBA HRM'
        ],
        datasets:[{
            data:[10,5,7,8,9]
        }]
    }
});

new Chart(
document.getElementById('specificViolationChart'),
{
    type:'line',
    data:{
        labels:[
            'Jan','Feb','Mar','Apr','May'
        ],
        datasets:[
        {
            label:'SOT',
            data:[2,4,5,2,7]
        },
        {
            label:'SOE',
            data:[1,2,3,1,2]
        },
        {
            label:'SOB',
            data:[3,1,2,5,4]
        }]
    }
});

</script>

</body>
</html>