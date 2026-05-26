<?php
session_start();
include("../config/database.php");

if (!isset($_SESSION['user_id'])) { header("Location: ../index.html"); exit(); }
if ($_SESSION['role'] != 'admin') { header("Location: ../index.html"); exit(); }

/* ─────────────────────────────────────────
   LOG EXPORT HISTORY (stored in a file)
───────────────────────────────────────── */
$history_file = __DIR__ . '/export_history.json';

function logExport($type) {
    global $history_file;
    $history = file_exists($history_file) ? json_decode(file_get_contents($history_file), true) : [];
    array_unshift($history, [
        'type'     => $type,
        'datetime' => date('Y-m-d H:i:s'),
        'user'     => $_SESSION['username'] ?? $_SESSION['user_id'],
        'file'     => 'backup_' . $type . '_' . date('Y-m-d') . '.xls'
    ]);
    $history = array_slice($history, 0, 30); // keep last 30
    file_put_contents($history_file, json_encode($history));
}

/* ─────────────────────────────────────────
   HANDLE EXPORT
───────────────────────────────────────── */
if (isset($_GET['export'])) {
    $type = $_GET['export'];
    logExport($type);
    $filename = 'backup_' . $type . '_' . date('Y-m-d_His') . '.xls';
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office"
               xmlns:x="urn:schemas-microsoft-com:office:excel"
               xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta charset="UTF-8"><style>
        body  { font-family: Arial; font-size: 10pt; }
        h2    { font-size: 13pt; }
        th    { background:#1e3a5f; color:white; font-weight:bold; border:1px solid #aaa; padding:6px 10px; }
        td    { border:1px solid #ddd; padding:5px 10px; vertical-align:top; }
        tr:nth-child(even) td { background:#f0f4ff; }
        .pending   { color:#856404; font-weight:bold; }
        .completed { color:#155724; font-weight:bold; }
        .ongoing   { color:#004085; font-weight:bold; }
        .section-gap { height:30px; }
    </style></head><body>';

    if ($type == 'students')      { exportStudentsHTML($conn); }
    elseif ($type == 'violations'){ exportViolationsHTML($conn); }
    elseif ($type == 'disciplinary'){ exportDisciplinaryHTML($conn); }
    elseif ($type == 'risklevel') { exportRiskHTML($conn); }
    elseif ($type == 'all') {
        exportStudentsHTML($conn);     echo '<br><br><hr><br>';
        exportViolationsHTML($conn);   echo '<br><br><hr><br>';
        exportDisciplinaryHTML($conn); echo '<br><br><hr><br>';
        exportRiskHTML($conn);
    }
    echo '</body></html>';
    exit();
}

/* ─────────────────────────────────────────
   EXPORT HTML FUNCTIONS
───────────────────────────────────────── */
function exportStudentsHTML($conn) {
    $q = mysqli_query($conn, "SELECT * FROM students ORDER BY id ASC");
    echo '<h2 style="color:#1e3a5f;">🎓 Student Records &nbsp;<small style="font-size:10pt;color:#888;">Generated: ' . date('F d, Y h:i A') . '</small></h2>';
    echo '<table><thead><tr>
        <th>#</th><th>Student ID</th><th>Full Name</th><th>Course</th><th>Year Level</th><th>Department</th>
    </tr></thead><tbody>';
    $i = 1;
    while ($r = mysqli_fetch_assoc($q)) {
        echo '<tr>
            <td>'.$i++.'</td>
            <td>'.htmlspecialchars($r['student_id']).'</td>
            <td>'.htmlspecialchars($r['fullname']).'</td>
            <td>'.htmlspecialchars($r['course']).'</td>
            <td>'.htmlspecialchars($r['year_level']).'</td>
            <td>'.htmlspecialchars($r['department']).'</td>
        </tr>';
    }
    echo '</tbody></table>';
}

function exportViolationsHTML($conn) {
    $q = mysqli_query($conn, "SELECT *,
        (SELECT COUNT(*) FROM violations v2
         WHERE v2.student_id=violations.student_id
         AND v2.violation_category=violations.violation_category
         AND v2.id<=violations.id) AS offense_count
        FROM violations ORDER BY created_at DESC");
    echo '<h2 style="color:#c0392b;">⚠️ Violation Records &nbsp;<small style="font-size:10pt;color:#888;">Generated: ' . date('F d, Y h:i A') . '</small></h2>';
    echo '<table><thead><tr>
        <th>#</th><th>Date</th><th>Student ID</th><th>Student Name</th><th>Course</th><th>Year Level</th>
        <th>Department</th><th>Category</th><th>Violation Type</th><th>Description</th>
        <th>Evidence</th><th>Camera Capture</th><th>E-Signature</th>
        <th>Reported By</th><th>Reporter Role</th><th>Status</th><th>Case Status</th>
        <th>Sanction</th><th>Action Start</th><th>Action End</th>
        <th>Disciplinary Level</th><th>Disciplinary Start</th><th>Disciplinary End</th>
    </tr></thead><tbody>';
    $i = 1;
    while ($r = mysqli_fetch_assoc($q)) {
        $san = computeSanction($r['offense_count'], $r['violation_category'], $r['violation_type']);
        $cs  = strtolower($r['case_status'] ?? '');
        $cls = $cs == 'pending' ? 'pending' : ($cs == 'completed' ? 'completed' : 'ongoing');
        echo '<tr>
            <td>'.$i++.'</td>
            <td>'.htmlspecialchars($r['created_at']).'</td>
            <td>'.htmlspecialchars($r['student_id']).'</td>
            <td>'.htmlspecialchars($r['student_name']).'</td>
            <td>'.htmlspecialchars($r['course']).'</td>
            <td>'.htmlspecialchars($r['year_level']).'</td>
            <td>'.htmlspecialchars($r['department']).'</td>
            <td>'.htmlspecialchars($r['violation_category'] ?? '—').'</td>
            <td>'.htmlspecialchars($r['violation_type']).'</td>
            <td>'.htmlspecialchars($r['description']).'</td>
            <td>'.htmlspecialchars($r['evidence'] ?? '—').'</td>
            <td>'.($r['camera_capture'] ? 'Yes' : '—').'</td>
            <td>'.($r['e_signature'] ? 'Yes' : '—').'</td>
            <td>'.htmlspecialchars($r['reported_by']).'</td>
            <td>'.htmlspecialchars($r['reporter_role']).'</td>
            <td>'.htmlspecialchars($r['status'] ?? '—').'</td>
            <td class="'.$cls.'">'.htmlspecialchars($r['case_status'] ?? '—').'</td>
            <td>'.htmlspecialchars($san).'</td>
            <td>'.htmlspecialchars($r['action_start'] ?? '—').'</td>
            <td>'.htmlspecialchars($r['action_end'] ?? '—').'</td>
            <td>'.htmlspecialchars($r['disciplinary_level'] ?? '—').'</td>
            <td>'.htmlspecialchars($r['disciplinary_start'] ?? '—').'</td>
            <td>'.htmlspecialchars($r['disciplinary_end'] ?? '—').'</td>
        </tr>';
    }
    echo '</tbody></table>';
}

function exportDisciplinaryHTML($conn) {
    $q = mysqli_query($conn, "SELECT *,
        (SELECT COUNT(*) FROM violations v2
         WHERE v2.student_id=violations.student_id
         AND v2.violation_category=violations.violation_category
         AND v2.id<=violations.id) AS offense_count
        FROM violations ORDER BY created_at DESC");
    echo '<h2 style="color:#7c3aed;">⚖️ Disciplinary Actions &nbsp;<small style="font-size:10pt;color:#888;">Generated: ' . date('F d, Y h:i A') . '</small></h2>';
    echo '<table><thead><tr>
        <th>#</th><th>Student ID</th><th>Student Name</th><th>Violation Type</th><th>Category</th>
        <th>Sanction</th><th>Disciplinary Level</th><th>Case Status</th>
        <th>Disciplinary Start</th><th>Disciplinary End</th>
    </tr></thead><tbody>';
    $i = 1;
    while ($r = mysqli_fetch_assoc($q)) {
        $san = computeSanction($r['offense_count'], $r['violation_category'], $r['violation_type']);
        $cs  = strtolower($r['case_status'] ?? '');
        $cls = $cs == 'pending' ? 'pending' : ($cs == 'completed' ? 'completed' : 'ongoing');
        echo '<tr>
            <td>'.$i++.'</td>
            <td>'.htmlspecialchars($r['student_id']).'</td>
            <td>'.htmlspecialchars($r['student_name']).'</td>
            <td>'.htmlspecialchars($r['violation_type']).'</td>
            <td>'.htmlspecialchars($r['violation_category'] ?? '—').'</td>
            <td>'.htmlspecialchars($san).'</td>
            <td>'.htmlspecialchars($r['disciplinary_level'] ?? '—').'</td>
            <td class="'.$cls.'">'.htmlspecialchars($r['case_status'] ?? '—').'</td>
            <td>'.htmlspecialchars($r['disciplinary_start'] ?? '—').'</td>
            <td>'.htmlspecialchars($r['disciplinary_end'] ?? '—').'</td>
        </tr>';
    }
    echo '</tbody></table>';
}

function exportRiskHTML($conn) {
    $q = mysqli_query($conn, "SELECT student_id, student_name, course, department,
        COUNT(*) as total_violations,
        SUM(CASE WHEN violation_category='Major' THEN 1 ELSE 0 END) as major_count,
        SUM(CASE WHEN violation_category='Minor' THEN 1 ELSE 0 END) as minor_count,
        SUM(CASE WHEN case_status='Pending' THEN 1 ELSE 0 END) as pending_count
        FROM violations GROUP BY student_id, student_name, course, department
        ORDER BY total_violations DESC");
    echo '<h2 style="color:#d97706;">🔴 Risk Level Indicator &nbsp;<small style="font-size:10pt;color:#888;">Generated: ' . date('F d, Y h:i A') . '</small></h2>';
    echo '<table><thead><tr>
        <th>#</th><th>Student ID</th><th>Student Name</th><th>Course</th><th>Department</th>
        <th>Total Violations</th><th>Major</th><th>Minor</th><th>Pending</th><th>Risk Level</th>
    </tr></thead><tbody>';
    $i = 1;
    while ($r = mysqli_fetch_assoc($q)) {
        $total = $r['total_violations'];
        $major = $r['major_count'];
        if ($major >= 2 || $total >= 5)       { $risk = 'HIGH';   $rc = '#dc2626'; }
        elseif ($major == 1 || $total >= 3)   { $risk = 'MEDIUM'; $rc = '#d97706'; }
        else                                   { $risk = 'LOW';    $rc = '#16a34a'; }
        echo '<tr>
            <td>'.$i++.'</td>
            <td>'.htmlspecialchars($r['student_id']).'</td>
            <td>'.htmlspecialchars($r['student_name']).'</td>
            <td>'.htmlspecialchars($r['course']).'</td>
            <td>'.htmlspecialchars($r['department']).'</td>
            <td style="text-align:center;">'.$total.'</td>
            <td style="text-align:center;">'.$major.'</td>
            <td style="text-align:center;">'.$r['minor_count'].'</td>
            <td style="text-align:center;">'.$r['pending_count'].'</td>
            <td style="color:'.$rc.';font-weight:bold;">'.$risk.'</td>
        </tr>';
    }
    echo '</tbody></table>';
}

function computeSanction($oc, $category, $violation) {
    $cat = strtolower($category);
    $vio = strtolower($violation);
    if ($cat == 'minor') {
        if ($oc == 1) return "Verbal Warning and Counseling";
        if ($oc == 2) return "Written Warning and Reflective Essay";
        if ($oc == 3) return "Community Service (5-10 Hours) and Parental Notification";
        if ($oc == 4) return "Short Term Suspension (1-3 Days) and Mandatory Workshop";
        return "Long Term Suspension (1 Week) and Disciplinary Probation";
    } elseif ($cat == 'major') {
        if ($oc == 1) return $vio == 'academic dishonesty'
            ? "Failing Grade for the Course and Mandatory Ethics Workshop"
            : "Suspension (1 Week to 1 Month) and Mandatory Counseling";
        if ($oc == 2) return $vio == 'academic dishonesty'
            ? "Suspension for 1 Semester"
            : "Suspension (1 Month to 1 Semester) and Extended Counseling";
        return $vio == 'academic dishonesty' ? "Expulsion" : "Expulsion and Notification to Authorities if Applicable";
    }
    return '—';
}

/* ─────────────────────────────────────────
   FETCH COUNTS
───────────────────────────────────────── */
$total_students    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM students"))['c'];
$total_violations  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM violations"))['c'];
$total_pending     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM violations WHERE case_status='Pending'"))['c'];
$total_completed   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM violations WHERE case_status='Completed'"))['c'];
$total_ongoing     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM violations WHERE case_status='Ongoing'"))['c'];
$total_risk_high   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM (SELECT student_id, COUNT(*) t, SUM(violation_category='Major') m FROM violations GROUP BY student_id HAVING m>=2 OR t>=5) x"))['c'];

// Preview data (5 rows each)
$prev_students    = mysqli_query($conn, "SELECT * FROM students ORDER BY id DESC LIMIT 5");
$prev_violations  = mysqli_query($conn, "SELECT * FROM violations ORDER BY created_at DESC LIMIT 5");
$prev_disciplinary= mysqli_query($conn, "SELECT * FROM violations WHERE case_status IS NOT NULL ORDER BY created_at DESC LIMIT 5");
$prev_risk        = mysqli_query($conn, "SELECT student_id, student_name, course, COUNT(*) total, SUM(violation_category='Major') major FROM violations GROUP BY student_id ORDER BY total DESC LIMIT 5");

// Export history
$export_history = file_exists($history_file) ? json_decode(file_get_contents($history_file), true) : [];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Backup & Export</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f1f5f9; }
        .sidebar { min-height: 100vh; background: #212529; }
        .sidebar a { color: #cbd5e1; text-decoration: none; display: block; padding: 13px 20px; transition: 0.2s; font-size: 0.92rem; }
        .sidebar a:hover, .sidebar a.active { background: #343a40; color: #fff; }

        /* Export All Banner */
        .export-all-banner {
            background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
            border-radius: 16px;
            padding: 2rem 2.5rem;
            color: white;
            margin-bottom: 2rem;
        }
        .export-all-banner h3 { font-weight: 700; font-size: 1.4rem; margin: 0; }
        .export-all-banner p  { opacity: 0.8; margin: 0.3rem 0 0; font-size: 0.9rem; }
        .btn-export-all {
            background: white;
            color: #1e3a5f;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            padding: 12px 28px;
            font-size: 1rem;
            text-decoration: none;
            transition: 0.2s;
            white-space: nowrap;
        }
        .btn-export-all:hover { background: #e2e8f0; color: #1e3a5f; transform: translateY(-1px); }

        /* Table Cards */
        .table-card { border-radius: 14px; border: none; box-shadow: 0 2px 12px rgba(0,0,0,0.07); margin-bottom: 1.5rem; overflow: hidden; }
        .table-card .card-header { padding: 1rem 1.5rem; font-weight: 700; font-size: 1rem; display: flex; align-items: center; justify-content: space-between; }
        .table-card .card-body { padding: 0; }
        .table-card .card-footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 0.8rem 1.5rem; }

        .preview-table { font-size: 0.82rem; margin: 0; }
        .preview-table th { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; color: #6c757d; background: #f8fafc; border-bottom: 2px solid #e2e8f0; padding: 8px 14px; }
        .preview-table td { padding: 8px 14px; vertical-align: middle; border-color: #f1f5f9; }
        .preview-label { font-size: 0.72rem; color: #94a3b8; font-style: italic; padding: 6px 14px 8px; }

        .btn-export-card { border-radius: 8px; font-weight: 600; font-size: 0.85rem; padding: 7px 18px; }

        /* Stats mini */
        .mini-stat { font-size: 0.78rem; color: #64748b; }
        .mini-stat strong { color: #1e293b; }

        /* History */
        .history-card { border-radius: 14px; border: none; box-shadow: 0 2px 12px rgba(0,0,0,0.07); }
        .history-item { display: flex; align-items: center; gap: 14px; padding: 12px 0; border-bottom: 1px solid #f1f5f9; }
        .history-item:last-child { border-bottom: none; }
        .history-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
        .history-meta { flex: 1; }
        .history-meta .filename { font-weight: 600; font-size: 0.87rem; color: #1e293b; }
        .history-meta .datetime { font-size: 0.75rem; color: #94a3b8; }
        .history-empty { text-align: center; color: #94a3b8; padding: 2rem; font-size: 0.9rem; }

        .risk-high    { color: #dc2626; font-weight: 700; }
        .risk-medium  { color: #d97706; font-weight: 700; }
        .risk-low     { color: #16a34a; font-weight: 700; }
    </style>
</head>
<body>
<div class="container-fluid">
<div class="row">

    <!-- SIDEBAR -->
    <div class="col-md-2 sidebar p-0">
        <h5 class="text-white text-center py-3 mb-0">ADMIN</h5>
        <a href="dashboard.php">Dashboard</a>
        <a href="students.php">Student Records</a>
        <a href="reports.php">Violation Reports</a>
        <a href="disciplinary_actions.php">Disciplinary Actions</a>
        <a href="risk_level_indicator.php">Risk Level Indicator</a>
        <a href="users.php">User Management</a>
        <a href="backup.php" class="active" style="color:#4ade80;">💾 Backup & Export</a>
        <a href="../auth/logout.php">Logout</a>
    </div>

    <!-- CONTENT -->
    <div class="col-md-10 p-4">

        <h4 class="fw-bold mb-1">💾 Backup & Export</h4>
        <p class="text-muted mb-4">Preview and export each data table to Excel individually, or export everything at once.</p>

        <!-- ══════════════════════════════
             EXPORT ALL BANNER
        ══════════════════════════════ -->
        <div class="export-all-banner d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h3>📦 Export All Data</h3>
                <p>Download a complete backup of all tables — Students, Violations, Disciplinary Actions, and Risk Levels — in one Excel file.</p>
                <div class="d-flex gap-3 mt-2 flex-wrap">
                    <span class="mini-stat" style="color:rgba(255,255,255,0.7);">🎓 <strong style="color:white;"><?= $total_students ?></strong> Students</span>
                    <span class="mini-stat" style="color:rgba(255,255,255,0.7);">⚠️ <strong style="color:white;"><?= $total_violations ?></strong> Violations</span>
                    <span class="mini-stat" style="color:rgba(255,255,255,0.7);">⏳ <strong style="color:white;"><?= $total_pending ?></strong> Pending</span>
                    <span class="mini-stat" style="color:rgba(255,255,255,0.7);">🔴 <strong style="color:white;"><?= $total_risk_high ?></strong> High Risk</span>
                </div>
            </div>
            <a href="?export=all" class="btn-export-all">⬇ Export All to Excel</a>
        </div>

        <!-- ══════════════════════════════
             CARD 1: STUDENT RECORDS
        ══════════════════════════════ -->
        <div class="table-card card">
            <div class="card-header bg-primary text-white">
                <div class="d-flex align-items-center gap-2">
                    <span>🎓</span>
                    <span>Student Records</span>
                    <span class="badge bg-white text-primary ms-1"><?= $total_students ?> records</span>
                </div>
                <a href="?export=students" class="btn btn-light btn-export-card">⬇ Export to Excel</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table preview-table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Student ID</th><th>Full Name</th><th>Course</th><th>Year Level</th><th>Department</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($r = mysqli_fetch_assoc($prev_students)): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['student_id']) ?></td>
                                <td><?= htmlspecialchars($r['fullname']) ?></td>
                                <td><?= htmlspecialchars($r['course']) ?></td>
                                <td><?= htmlspecialchars($r['year_level']) ?></td>
                                <td><?= htmlspecialchars($r['department']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer preview-label">Showing latest 5 of <?= $total_students ?> records</div>
        </div>

        <!-- ══════════════════════════════
             CARD 2: VIOLATION REPORTS
        ══════════════════════════════ -->
        <div class="table-card card">
            <div class="card-header bg-danger text-white">
                <div class="d-flex align-items-center gap-2">
                    <span>⚠️</span>
                    <span>Violation Reports</span>
                    <span class="badge bg-white text-danger ms-1"><?= $total_violations ?> records</span>
                </div>
                <a href="?export=violations" class="btn btn-light btn-export-card">⬇ Export to Excel</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table preview-table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th><th>Student ID</th><th>Student Name</th><th>Category</th><th>Violation Type</th><th>Reported By</th><th>Case Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($r = mysqli_fetch_assoc($prev_violations)):
                            $cs = strtolower($r['case_status'] ?? '');
                            $cls = $cs == 'pending' ? 'warning' : ($cs == 'completed' ? 'success' : 'info');
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($r['created_at']) ?></td>
                                <td><?= htmlspecialchars($r['student_id']) ?></td>
                                <td><?= htmlspecialchars($r['student_name']) ?></td>
                                <td><?= htmlspecialchars($r['violation_category'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($r['violation_type']) ?></td>
                                <td><?= htmlspecialchars($r['reported_by']) ?></td>
                                <td><span class="badge bg-<?= $cls ?>"><?= htmlspecialchars($r['case_status'] ?? '—') ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer preview-label">Showing latest 5 of <?= $total_violations ?> records</div>
        </div>

        <!-- ══════════════════════════════
             CARD 3: DISCIPLINARY ACTIONS
        ══════════════════════════════ -->
        <div class="table-card card">
            <div class="card-header text-white" style="background:#7c3aed;">
                <div class="d-flex align-items-center gap-2">
                    <span>⚖️</span>
                    <span>Disciplinary Actions</span>
                    <span class="badge bg-white ms-1" style="color:#7c3aed;"><?= $total_violations ?> records</span>
                </div>
                <a href="?export=disciplinary" class="btn btn-light btn-export-card">⬇ Export to Excel</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table preview-table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Student ID</th><th>Student Name</th><th>Violation</th><th>Sanction</th><th>Disc. Level</th><th>Case Status</th><th>Disc. Start</th><th>Disc. End</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        mysqli_data_seek($prev_disciplinary, 0);
                        // re-query with offense count for sanction
                        $dq = mysqli_query($conn, "SELECT *,
                            (SELECT COUNT(*) FROM violations v2
                             WHERE v2.student_id=violations.student_id
                             AND v2.violation_category=violations.violation_category
                             AND v2.id<=violations.id) AS offense_count
                            FROM violations ORDER BY created_at DESC LIMIT 5");
                        while($r = mysqli_fetch_assoc($dq)):
                            $san = computeSanction($r['offense_count'], $r['violation_category'], $r['violation_type']);
                            $cs  = strtolower($r['case_status'] ?? '');
                            $cls = $cs == 'pending' ? 'warning' : ($cs == 'completed' ? 'success' : 'info');
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($r['student_id']) ?></td>
                                <td><?= htmlspecialchars($r['student_name']) ?></td>
                                <td><?= htmlspecialchars($r['violation_type']) ?></td>
                                <td style="max-width:200px;white-space:normal;"><?= htmlspecialchars($san) ?></td>
                                <td><?= htmlspecialchars($r['disciplinary_level'] ?? '—') ?></td>
                                <td><span class="badge bg-<?= $cls ?>"><?= htmlspecialchars($r['case_status'] ?? '—') ?></span></td>
                                <td><?= htmlspecialchars($r['disciplinary_start'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($r['disciplinary_end'] ?? '—') ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer preview-label">Showing latest 5 of <?= $total_violations ?> records</div>
        </div>

        <!-- ══════════════════════════════
             CARD 4: RISK LEVEL INDICATOR
        ══════════════════════════════ -->
        <div class="table-card card">
            <div class="card-header text-white" style="background:#d97706;">
                <div class="d-flex align-items-center gap-2">
                    <span>🔴</span>
                    <span>Risk Level Indicator</span>
                    <span class="badge bg-white ms-1" style="color:#d97706;"><?= $total_risk_high ?> High Risk</span>
                </div>
                <a href="?export=risklevel" class="btn btn-light btn-export-card">⬇ Export to Excel</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table preview-table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Student ID</th><th>Student Name</th><th>Course</th><th>Total Violations</th><th>Major</th><th>Minor</th><th>Risk Level</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($r = mysqli_fetch_assoc($prev_risk)):
                            $major = $r['major'];
                            $total = $r['total'];
                            if ($major >= 2 || $total >= 5)     { $risk = 'HIGH';   $rc = 'risk-high'; }
                            elseif ($major == 1 || $total >= 3) { $risk = 'MEDIUM'; $rc = 'risk-medium'; }
                            else                                 { $risk = 'LOW';    $rc = 'risk-low'; }
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($r['student_id']) ?></td>
                                <td><?= htmlspecialchars($r['student_name']) ?></td>
                                <td><?= htmlspecialchars($r['course']) ?></td>
                                <td class="text-center"><?= $total ?></td>
                                <td class="text-center"><?= $major ?></td>
                                <td class="text-center"><?= $total - $major ?></td>
                                <td class="<?= $rc ?>"><?= $risk ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer preview-label">Showing top 5 students by violation count</div>
        </div>

        <!-- ══════════════════════════════
             EXPORT HISTORY
        ══════════════════════════════ -->
        <div class="history-card card">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3 px-4">
                <div>
                    <h6 class="fw-bold mb-0">📋 Export History</h6>
                    <small class="text-muted">Last <?= count($export_history) ?> exports</small>
                </div>
                <?php if(count($export_history) > 0): ?>
                <button class="btn btn-outline-secondary btn-sm" onclick="if(confirm('Clear export history?')) window.location='?clear_history=1'">🗑 Clear History</button>
                <?php endif; ?>
            </div>
            <div class="card-body px-4 py-2">
                <?php if(empty($export_history)): ?>
                    <div class="history-empty">No exports yet. Click any export button above to get started.</div>
                <?php else: ?>
                    <?php
                    $type_icons  = ['students'=>'🎓','violations'=>'⚠️','disciplinary'=>'⚖️','risklevel'=>'🔴','all'=>'📦'];
                    $type_colors = ['students'=>'#dbeafe','violations'=>'#fee2e2','disciplinary'=>'#ede9fe','risklevel'=>'#fef3c7','all'=>'#dcfce7'];
                    $type_labels = ['students'=>'Student Records','violations'=>'Violation Reports','disciplinary'=>'Disciplinary Actions','risklevel'=>'Risk Level Indicator','all'=>'Full Backup'];
                    foreach($export_history as $h):
                        $t   = $h['type'];
                        $ico = $type_icons[$t]  ?? '📄';
                        $col = $type_colors[$t] ?? '#f1f5f9';
                        $lbl = $type_labels[$t]  ?? ucfirst($t);
                    ?>
                    <div class="history-item">
                        <div class="history-icon" style="background:<?= $col ?>;"><?= $ico ?></div>
                        <div class="history-meta">
                            <div class="filename"><?= htmlspecialchars($h['file']) ?></div>
                            <div class="datetime">
                                <?= htmlspecialchars($lbl) ?> &nbsp;·&nbsp;
                                Exported by <strong><?= htmlspecialchars($h['user']) ?></strong> &nbsp;·&nbsp;
                                <?= date('M d, Y h:i A', strtotime($h['datetime'])) ?>
                            </div>
                        </div>
                        <span class="badge rounded-pill" style="background:<?= $col ?>;color:#374151;font-size:0.72rem;">
                            <?= $lbl ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /col -->
</div><!-- /row -->
</div><!-- /container -->

<?php
// Handle clear history
if(isset($_GET['clear_history'])) {
    if(file_exists($history_file)) unlink($history_file);
    header("Location: backup.php");
    exit();
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>