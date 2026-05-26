<?php

session_start();

include("../config/database.php");

if (!isset($_SESSION['user_id'])) {

    header("Location: ../index.html");
    exit();

}

if ($_SESSION['role'] != 'admin') {

    header("Location: ../index.php");
    exit();

}

/* FETCH ONLY REPORTS WITH SANCTIONS */

$query = "

SELECT *,

(
    SELECT COUNT(*)

    FROM violations v2

    WHERE
    v2.student_id = violations.student_id

    AND
    v2.violation_category =
    violations.violation_category

    AND
    v2.id <= violations.id

) AS offense_count

FROM violations

ORDER BY id DESC

";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html>
<head>

<title>Disciplinary Actions</title>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<style>

body{
    background:#f5f6fa;
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
    transition:0.3s;
}

.sidebar a:hover{
    background:#1f2937;
}

.main-card{
    border-radius:15px;
    overflow:hidden;
}

.header-bg{
    background:#05056b;
}

.table thead th{
    font-size:12px;
    letter-spacing:1px;
    color:#6b7280;
}

.badge-status{
    padding:8px 15px;
    border-radius:20px;
    font-size:12px;
}

</style>

</head>

<body>

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-2 sidebar p-0">

            <h4 class="text-white text-center py-4">
                ADMIN PANEL
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

            <a href="users.php">
                User Management
            </a>

            <a href="../auth/logout.php">
                Logout
            </a>

        </div>

        <!-- CONTENT -->
        <div class="col-md-10 p-4">

            <div class="card shadow border-0 main-card">

                <!-- HEADER -->
                <div class="card-header header-bg text-white p-4">

                    <h2 class="mb-0">
                        Disciplinary Actions
                    </h2>

                </div>

                <!-- BODY -->
                <div class="card-body">

                    <!-- SEARCH + FILTER -->
                    <div class="row mb-4">

                        <!-- SEARCH -->
                        <div class="col-md-8">

                            <input
                                type="text"
                                id="searchInput"
                                class="form-control"
                                placeholder="Search by name or student ID..."
                                onkeyup="filterTable()"
                            >

                        </div>

                        <!-- STATUS FILTER -->
                        <div class="col-md-4">

                            <select
                                id="statusFilter"
                                class="form-select"
                                onchange="filterTable()"
                            >

                                <option value="">
                                    All Status
                                </option>

                                <option value="pending">
                                    Pending
                                </option>

                                <option value="ongoing">
                                    Ongoing
                                </option>

                                <option value="completed">
                                    Completed
                                </option>

                            </select>

                        </div>

                    </div>

                    <!-- TABLE -->
                    <div class="table-responsive">

                        <table class="table align-middle">

                            <thead>

                                <tr>

                                    <th>STUDENT ID</th>
                                    <th>STUDENT</th>
                                    <th>SANCTION</th>
                                    <th>START DATE</th>
                                    <th>END DATE</th>
                                    <th>STATUS</th>
                                    <th>UPDATE</th>

                                </tr>

                            </thead>

                            <tbody>

                            <?php while($row = mysqli_fetch_assoc($result)) { ?>

                                <tr class="action-row">

                                    <!-- STUDENT ID -->
                                    <td>

                                        <?= $row['student_id']; ?>

                                    </td>

                                    <!-- NAME -->
                                    <td>

                                        <strong>

                                            <?= $row['student_name']; ?>

                                        </strong>

                                    </td>

                                    <!-- SANCTION -->
                                    <td>

                                        <?php

$count =
$row['offense_count'];

$category =
strtolower($row['violation_category']);

$violation =
strtolower($row['violation_type']);

$sanction = "";

/* =========================
   MINOR OFFENSES
========================= */

if($category == 'minor'){

    if($count == 1){

        $sanction =
        "Verbal Warning and Counseling";

    }
    elseif($count == 2){

        $sanction =
        "Written Warning and Reflective Essay";

    }
    elseif($count == 3){

        $sanction =
        "Community Service (5-10 Hours) and Parental Notification";

    }
    elseif($count == 4){

        $sanction =
        "Short Term Suspension (1-3 Days) and Mandatory Workshop";

    }
    else{

        $sanction =
        "Long Term Suspension (1 Week) and Disciplinary Probation";

    }

}

/* =========================
   MAJOR OFFENSES
========================= */

elseif($category == 'major'){

    /* FIRST OFFENSE */
    if($count == 1){

        if($violation == 'academic dishonesty'){

            $sanction =
            "Failing Grade for the Course and Mandatory Ethics Workshop";

        }else{

            $sanction =
            "Suspension (1 Week to 1 Month) and Mandatory Counseling";

        }

    }

    /* SECOND OFFENSE */
    elseif($count == 2){

        if($violation == 'academic dishonesty'){

            $sanction =
            "Suspension for 1 Semester";

        }else{

            $sanction =
            "Suspension (1 Month to 1 Semester) and Extended Counseling";

        }

    }

    /* THIRD OFFENSE */
    else{

        if($violation == 'academic dishonesty'){

            $sanction =
            "Expulsion";

        }else{

            $sanction =
            "Expulsion and Notification to Authorities if Applicable";

        }

    }

}

echo $sanction;

?>

                                    </td>

                                    <!-- START -->
                                    <td>

                                        <?php

if($row['disciplinary_start'] != NULL){

    echo $row['disciplinary_start'];

}else{

    echo "-";

}

?>

                                    </td>

                                    <!-- END -->
                                    <td>

                                        <?php

if($row['disciplinary_end'] != NULL){

    echo $row['disciplinary_end'];

}else{

    echo "-";

}

?>
                                    </td>

                                    <!-- STATUS -->
                                    <td>

                                    <?php

                                    $status =
                                    strtolower($row['case_status']);

                                    if($status == 'pending'){

                                        echo '

                                        <span class="badge bg-warning text-dark badge-status status-text">
                                            Pending
                                        </span>

                                        ';

                                    }
                                    elseif($status == 'ongoing'){

                                        echo '

                                        <span class="badge bg-primary badge-status status-text">
                                            Ongoing
                                        </span>

                                        ';

                                    }
                                    elseif($status == 'completed'){

                                        echo '

                                        <span class="badge bg-success badge-status status-text">
                                            Completed
                                        </span>

                                        ';

                                    }

                                    ?>

                                    </td>

                                    <!-- UPDATE -->
                                    <td>

                                        <button
                                            class="btn btn-sm btn-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#updateModal<?= $row['id']; ?>"
                                        >

                                            ✏ Update

                                        </button>

                                    </td>

                                </tr>

                                <!-- UPDATE MODAL -->
                                <div
                                    class="modal fade"
                                    id="updateModal<?= $row['id']; ?>"
                                    tabindex="-1"
                                >

                                    <div class="modal-dialog">

                                        <div class="modal-content">

                                            <!-- HEADER -->
                                            <div class="modal-header bg-primary text-white">

                                                <h5 class="modal-title">
                                                    Update Status
                                                </h5>

                                                <button
                                                    type="button"
                                                    class="btn-close btn-close-white"
                                                    data-bs-dismiss="modal">
                                                </button>

                                            </div>

                                            <!-- BODY -->
                                            <div class="modal-body">

                                                <form
                                                    action="update_disciplinary_status.php"
                                                    method="POST"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="report_id"
                                                        value="<?= $row['id']; ?>"
                                                    >

                                                    <!-- STATUS -->
                                                    <div class="mb-3">

                                                        <label class="form-label">
                                                            Select Status
                                                        </label>

                                                        <select
                                                            name="case_status"
                                                            class="form-select"
                                                            required
                                                        >

                                                            <option value="Pending"
                                                            <?= $row['case_status'] == 'Pending' ? 'selected' : ''; ?>>
                                                                Pending
                                                            </option>

                                                            <option value="Ongoing"
                                                            <?= $row['case_status'] == 'Ongoing' ? 'selected' : ''; ?>>
                                                                Ongoing
                                                            </option>

                                                            <option value="Completed"
                                                            <?= $row['case_status'] == 'Completed' ? 'selected' : ''; ?>>
                                                                Completed
                                                            </option>

                                                        </select>

                                                    </div>

                                                    <button
                                                        type="submit"
                                                        class="btn btn-primary"
                                                    >

                                                        Save Changes

                                                    </button>

                                                </form>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            <?php } ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

/* SEARCH + FILTER */

function filterTable(){

    let search =
    document.getElementById(
        "searchInput"
    ).value.toLowerCase();

    let status =
    document.getElementById(
        "statusFilter"
    ).value.toLowerCase();

    let rows =
    document.querySelectorAll(
        ".action-row"
    );

    rows.forEach(function(row){

        let text =
        row.innerText.toLowerCase();

        let statusText =
        row.querySelector(
            ".status-text"
        ).innerText.toLowerCase();

        let show = true;

        // SEARCH
        if(
            search != "" &&
            !text.includes(search)
        ){

            show = false;

        }

        // STATUS
        if(
            status != "" &&
            !statusText.includes(status)
        ){

            show = false;

        }

        row.style.display =
        show ? "" : "none";

    });

}

</script>

</body>
</html>