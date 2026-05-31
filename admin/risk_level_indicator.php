<?php

session_start();

include("../config/database.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.html");
    exit();
}

/*
|--------------------------------------------------------------------------
| COUNTERS
|--------------------------------------------------------------------------
*/

$low = 0;
$medium = 0;
$high = 0;
$critical = 0;

/*
|--------------------------------------------------------------------------
| GET STUDENTS
|--------------------------------------------------------------------------
*/

$studentsQuery = "
SELECT *
FROM students
ORDER BY fullname ASC
";

$studentsResult = mysqli_query($conn, $studentsQuery);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Risk Level Indicator</title>

    <meta charset="UTF-8">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body{
            background:#f4f6fb;
        }

        .sidebar{
            height:100vh;
            background:#111827;
        }

        .sidebar a{
            color:white;
            text-decoration:none;
            display:block;
            padding:15px;
        }

        .sidebar a:hover{
            background:#1f2937;
        }

        .risk-card{
            border:none;
            border-radius:20px;
            box-shadow:0 2px 10px rgba(0,0,0,0.05);
        }

        .risk-number{
            font-size:50px;
            font-weight:bold;
        }

        .table-container{
            background:white;
            border-radius:20px;
            overflow:hidden;
            box-shadow:0 2px 10px rgba(0,0,0,0.05);
        }

    </style>

</head>

<body>

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-2 sidebar p-0">

            <h4 class="text-white text-center py-3">
                ADMIN
            </h4>

            <a href="dashboard.php">
                Dashboard
            </a>

            <a href="students.php">
                Student Records
            </a>

            <a href="reports.php">
                Violation Reports
            </a>

            <a href="disciplinary_actions.php">
                Disciplinary Actions
            </a>

            <a href="risk_level_indicator.php">
                Risk Level Indicator
            </a>
            <a href="backup.php">Backup & Export</a>

            <a href="../auth/logout.php">
                Logout
            </a>

        </div>

        <!-- MAIN CONTENT -->
        <div class="col-md-10 p-4">

            <h2 class="mb-4">
                Risk Level Indicator
            </h2>

<?php

/*
|--------------------------------------------------------------------------
| FIRST LOOP FOR CARDS
|--------------------------------------------------------------------------
*/

$studentsResult2 = mysqli_query($conn, $studentsQuery);

while($student = mysqli_fetch_assoc($studentsResult2)){

    $studentID = $student['student_id'];

    /*
    |--------------------------------------------------------------------------
    | MINOR COUNT
    |--------------------------------------------------------------------------
    */

    $minorQuery = "
    SELECT COUNT(*) as total
    FROM violations
    WHERE student_id='$studentID'
    AND violation_category='Minor'
    ";

    $minorResult = mysqli_query($conn, $minorQuery);
    $minorData = mysqli_fetch_assoc($minorResult);

    $minor = $minorData['total'];

    /*
    |--------------------------------------------------------------------------
    | MAJOR COUNT
    |--------------------------------------------------------------------------
    */

    $majorQuery = "
    SELECT COUNT(*) as total
    FROM violations
    WHERE student_id='$studentID'
    AND violation_category='Major'
    ";

    $majorResult = mysqli_query($conn, $majorQuery);
    $majorData = mysqli_fetch_assoc($majorResult);

    $major = $majorData['total'];

    /*
    |--------------------------------------------------------------------------
    | IMPROVED RISK COMPUTATION
    |--------------------------------------------------------------------------
    */

    $score =
    ($minor * 1) +
    ($major * 3);

    /*
    |--------------------------------------------------------------------------
    | RISK LEVEL
    |--------------------------------------------------------------------------
    */

    if($major >= 3 || $score >= 10){

        $risk = "Critical";
        $critical++;

    }
    elseif($major >= 2 || $score >= 6){

        $risk = "High";
        $high++;

    }
    elseif($major >= 1 || $score >= 3){

        $risk = "Medium";
        $medium++;

    }
    else{

        $risk = "Low";
        $low++;

    }

}

?>

            <!-- RISK CARDS -->
            <div class="row mb-4">

                <!-- LOW -->
                <div class="col-md-3 mb-3">

                    <div class="card risk-card p-4">

                        <h1 class="risk-number text-primary">
                            <?= $low; ?>
                        </h1>

                        <h5 class="text-primary">
                            Low Risk
                        </h5>

                    </div>

                </div>

                <!-- MEDIUM -->
                <div class="col-md-3 mb-3">

                    <div class="card risk-card p-4">

                        <h1 class="risk-number text-success">
                            <?= $medium; ?>
                        </h1>

                        <h5 class="text-success">
                            Medium Risk
                        </h5>

                    </div>

                </div>

                <!-- HIGH -->
                <div class="col-md-3 mb-3">

                    <div class="card risk-card p-4">

                        <h1 class="risk-number text-warning">
                            <?= $high; ?>
                        </h1>

                        <h5 class="text-warning">
                            High Risk
                        </h5>

                    </div>

                </div>

                <!-- CRITICAL -->
                <div class="col-md-3 mb-3">

                    <div class="card risk-card p-4">

                        <h1 class="risk-number text-danger">
                            <?= $critical; ?>
                        </h1>

                        <h5 class="text-danger">
                            Critical
                        </h5>

                    </div>

                </div>

            </div>

            <!-- TABLE -->
            <div class="table-container">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th>
                                STUDENT ID
                            </th>

                            <th>
                                FULL NAME
                            </th>

                            <th>
                                MINOR TALLY
                            </th>

                            <th>
                                MAJOR TALLY
                            </th>

                            <th>
                                RISK LEVEL
                            </th>

                        </tr>

                    </thead>

                    <tbody>

<?php

/*
|--------------------------------------------------------------------------
| SECOND LOOP FOR TABLE
|--------------------------------------------------------------------------
*/

while($student = mysqli_fetch_assoc($studentsResult)){

    $studentID = $student['student_id'];

    /*
    |--------------------------------------------------------------------------
    | MINOR COUNT
    |--------------------------------------------------------------------------
    */

    $minorQuery = "
    SELECT COUNT(*) as total
    FROM violations
    WHERE student_id='$studentID'
    AND violation_category='Minor'
    ";

    $minorResult = mysqli_query($conn, $minorQuery);
    $minorData = mysqli_fetch_assoc($minorResult);

    $minor = $minorData['total'];

    /*
    |--------------------------------------------------------------------------
    | MAJOR COUNT
    |--------------------------------------------------------------------------
    */

    $majorQuery = "
    SELECT COUNT(*) as total
    FROM violations
    WHERE student_id='$studentID'
    AND violation_category='Major'
    ";

    $majorResult = mysqli_query($conn, $majorQuery);
    $majorData = mysqli_fetch_assoc($majorResult);

    $major = $majorData['total'];

    /*
    |--------------------------------------------------------------------------
    | SCORE
    |--------------------------------------------------------------------------
    */

    $score =
    ($minor * 1) +
    ($major * 3);

    /*
    |--------------------------------------------------------------------------
    | RISK LEVEL
    |--------------------------------------------------------------------------
    */

    if($major >= 3 || $score >= 10){

        $risk = "Critical";

        $badge = '
        <span class="badge bg-danger">
            CRITICAL
        </span>
        ';

    }
    elseif($major >= 2 || $score >= 6){

        $risk = "High";

        $badge = '
        <span class="badge bg-warning text-dark">
            HIGH
        </span>
        ';

    }
    elseif($major >= 1 || $score >= 3){

        $risk = "Medium";

        $badge = '
        <span class="badge bg-success">
            MEDIUM
        </span>
        ';

    }
    else{

        $risk = "Low";

        $badge = '
        <span class="badge bg-primary">
            LOW
        </span>
        ';

    }

?>

                        <tr>

                            <td>

                                <?= $student['student_id']; ?>

                            </td>

                            <td>

                                <?= $student['fullname']; ?>

                            </td>

                            <td>

                                <span class="badge bg-primary">

                                    <?= $minor; ?>

                                </span>

                            </td>

                            <td>

                                <span class="badge bg-danger">

                                    <?= $major; ?>

                                </span>

                            </td>

                           

                            <td>

                                <?= $badge; ?>

                            </td>

                        </tr>

<?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>