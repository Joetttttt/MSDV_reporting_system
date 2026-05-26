<?php

session_start();

include("../config/database.php");

/* CHECK LOGIN */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.html");
    exit();
}

/* GET FORM DATA */
$report_id   = $_POST['report_id'];
$case_status = $_POST['case_status'];
$current_date = date("Y-m-d");

/* ─────────────────────────────────────────
   FETCH VIOLATION TO COMPUTE SANCTION
───────────────────────────────────────── */
$vrow = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT *,
     (SELECT COUNT(*) FROM violations v2
      WHERE v2.student_id = violations.student_id
      AND v2.violation_category = violations.violation_category
      AND v2.id <= violations.id) AS offense_count
     FROM violations WHERE id = '$report_id'"
));

$oc       = $vrow['offense_count'];
$category = strtolower($vrow['violation_category']);
$vtype    = strtolower($vrow['violation_type']);

/* ─────────────────────────────────────────
   COMPUTE SANCTION
───────────────────────────────────────── */
$sanction = '';

if ($category == 'minor') {
    if ($oc == 1)     $sanction = "Verbal Warning and Counseling";
    elseif ($oc == 2) $sanction = "Written Warning and Reflective Essay";
    elseif ($oc == 3) $sanction = "Community Service (5-10 Hours) and Parental Notification";
    elseif ($oc == 4) $sanction = "Short Term Suspension (1-3 Days) and Mandatory Workshop";
    else              $sanction = "Long Term Suspension (1 Week) and Disciplinary Probation";

} elseif ($category == 'major') {
    if ($oc == 1) {
        $sanction = ($vtype == 'academic dishonesty')
            ? "Failing Grade for the Course and Mandatory Ethics Workshop"
            : "Suspension (1 Week to 1 Month) and Mandatory Counseling";
    } elseif ($oc == 2) {
        $sanction = ($vtype == 'academic dishonesty')
            ? "Suspension for 1 Semester"
            : "Suspension (1 Month to 1 Semester) and Extended Counseling";
    } else {
        $sanction = ($vtype == 'academic dishonesty')
            ? "Expulsion"
            : "Expulsion and Notification to Authorities if Applicable";
    }
}

/* ─────────────────────────────────────────
   COMPUTE DISCIPLINARY LEVEL
───────────────────────────────────────── */
$disciplinary_level = '';

if ($category == 'minor') {
    if ($oc <= 2)     $disciplinary_level = "Level 1 - Warning";
    elseif ($oc == 3) $disciplinary_level = "Level 2 - Community Service";
    elseif ($oc == 4) $disciplinary_level = "Level 3 - Short Suspension";
    else              $disciplinary_level = "Level 4 - Long Suspension";
} elseif ($category == 'major') {
    if ($oc == 1)     $disciplinary_level = "Level 3 - Suspension";
    elseif ($oc == 2) $disciplinary_level = "Level 4 - Extended Suspension";
    else              $disciplinary_level = "Level 5 - Expulsion";
}

$sanction_safe           = mysqli_real_escape_string($conn, $sanction);
$disciplinary_level_safe = mysqli_real_escape_string($conn, $disciplinary_level);

/* =========================
   IF STATUS = ONGOING
========================= */
if ($case_status == "Ongoing") {

    $query = "
        UPDATE violations SET
            case_status        = '$case_status',
            sanction           = '$sanction_safe',
            disciplinary_level = '$disciplinary_level_safe',
            action_start       = IF(action_start IS NULL, '$current_date', action_start),
            disciplinary_start = IF(disciplinary_start IS NULL, '$current_date', disciplinary_start)
        WHERE id = '$report_id'
    ";

/* =========================
   IF STATUS = COMPLETED
========================= */
} elseif ($case_status == "Completed") {

    $query = "
        UPDATE violations SET
            case_status        = '$case_status',
            sanction           = '$sanction_safe',
            disciplinary_level = '$disciplinary_level_safe',
            action_end         = '$current_date',
            disciplinary_end   = '$current_date'
        WHERE id = '$report_id'
    ";

/* =========================
   IF STATUS = PENDING
========================= */
} else {

    $query = "
        UPDATE violations SET
            case_status        = '$case_status',
            sanction           = '$sanction_safe',
            disciplinary_level = '$disciplinary_level_safe'
        WHERE id = '$report_id'
    ";

}

/* RUN QUERY */
mysqli_query($conn, $query);

/* REDIRECT */
header("Location: disciplinary_actions.php");
exit();

?>