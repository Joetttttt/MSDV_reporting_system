<?php
session_start();
require_once '../db/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php'); exit;
}

// EXPORT handler
if (isset($_GET['export'])) {
    $type = $_GET['export'];
    $filename = $type . '_export_' . date('Ymd') . '.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');

    if ($type === 'students') {
        fputcsv($out, ['Student ID','Full Name','Course','Year Level','Department']);
        $rows = $pdo->query("SELECT s.student_id,s.full_name,c.name,s.year_level,d.name FROM students s JOIN courses c ON s.course_id=c.id JOIN departments d ON s.department_id=d.id")->fetchAll();
        foreach($rows as $r) fputcsv($out, $r);

    } elseif ($type === 'violations') {
        fputcsv($out, ['ID','Student ID','Category','Violation','Description','Status','Date Submitted']);
        $rows = $pdo->query("SELECT id,student_id,category,violation,description,status,date_submitted FROM violations")->fetchAll();
        foreach($rows as $r) fputcsv($out, $r);

    } elseif ($type === 'disciplinary') {
        fputcsv($out, ['ID','Student ID','Violation','Sanction','Status','Start Date','End Date']);
        $rows = $pdo->query("SELECT da.id,da.student_id,v.violation,da.sanction,da.status,da.start_date,da.end_date FROM disciplinary_actions da JOIN violations v ON da.violation_id=v.id")->fetchAll();
        foreach($rows as $r) fputcsv($out, $r);

    } elseif ($type === 'risk') {
        fputcsv($out, ['Student ID','Full Name','Minor Count','Major Count','Risk Level']);
        $rows = $pdo->query("SELECT s.student_id,s.full_name, SUM(CASE WHEN v.category='minor' THEN 1 ELSE 0 END) minor, SUM(CASE WHEN v.category='major' THEN 1 ELSE 0 END) major, COUNT(v.id) total FROM students s LEFT JOIN violations v ON s.student_id=v.student_id GROUP BY s.student_id")->fetchAll();
        foreach($rows as $r) {
            $total = $r['total'];
            $risk = $total>=5?'Critical':($total>=4?'High':($total>=2?'Moderate':'Low'));
            fputcsv($out, [$r['student_id'],$r['full_name'],$r['minor'],$r['major'],$risk]);
        }

    } elseif ($type === 'all') {
        // All in one file separated by sections
        fputcsv($out, ['=== STUDENTS ===']);
        fputcsv($out, ['Student ID','Full Name','Course','Year Level','Department']);
        $rows = $pdo->query("SELECT s.student_id,s.full_name,c.name,s.year_level,d.name FROM students s JOIN courses c ON s.course_id=c.id JOIN departments d ON s.department_id=d.id")->fetchAll();
        foreach($rows as $r) fputcsv($out, $r);
        fputcsv($out, []);
        fputcsv($out, ['=== VIOLATIONS ===']);
        fputcsv($out, ['ID','Student ID','Category','Violation','Description','Status','Date']);
        $rows = $pdo->query("SELECT id,student_id,category,violation,description,status,date_submitted FROM violations")->fetchAll();
        foreach($rows as $r) fputcsv($out, $r);
    }

    fclose($out);
    exit;
}

$studentCount    = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$violationCount  = $pdo->query("SELECT COUNT(*) FROM violations")->fetchColumn();
$disciplinaryCount = $pdo->query("SELECT COUNT(*) FROM disciplinary_actions")->fetchColumn();
$students  = $pdo->query("SELECT s.*,c.name as cname,d.name as dname FROM students s JOIN courses c ON s.course_id=c.id JOIN departments d ON s.department_id=d.id LIMIT 10")->fetchAll();
$violations = $pdo->query("SELECT v.*,s.full_name FROM violations v JOIN students s ON v.student_id=s.student_id ORDER BY v.date_submitted DESC LIMIT 10")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Backup - MDSV</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
<nav class="navbar navbar-dark bg-primary px-3">
  <span class="navbar-brand fw-bold">MDSV Admin</span>
  <div class="d-flex align-items-center gap-2">
    <span class="text-white small d-none d-md-inline"><?= htmlspecialchars($_SESSION['full_name']) ?></span>
    <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
  </div>
</nav>
<div class="container-fluid"><div class="row">
<nav class="col-md-2 d-none d-md-block bg-light py-3" style="min-height:100vh">
  <ul class="nav flex-column">
    <li><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
    <li><a class="nav-link" href="student-records.php"><i class="bi bi-people"></i> Student Records</a></li>
    <li><a class="nav-link" href="violation-records.php"><i class="bi bi-exclamation-triangle"></i> Violation Records</a></li>
    <li><a class="nav-link" href="disciplinary-action.php"><i class="bi bi-shield-exclamation"></i> Disciplinary Action</a></li>
    <li><a class="nav-link" href="risk-level.php"><i class="bi bi-bar-chart"></i> Risk Level</a></li>
    <li><a class="nav-link" href="student-appeals.php"><i class="bi bi-chat-left-text"></i> Student Appeals</a></li>
    <li><a class="nav-link active fw-bold" href="data-backup.php"><i class="bi bi-download"></i> Data Backup</a></li>
    <li><a class="nav-link" href="user-management.php"><i class="bi bi-person-gear"></i> User Management</a></li>
  </ul>
</nav>
<div class="d-md-none p-2 bg-light w-100">
  <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#mob7"><i class="bi bi-list"></i> Menu</button>
  <div class="collapse" id="mob7">
    <ul class="nav flex-column mt-1">
      <li><a class="nav-link" href="dashboard.php">Dashboard</a></li>
      <li><a class="nav-link" href="student-records.php">Student Records</a></li>
      <li><a class="nav-link" href="violation-records.php">Violation Records</a></li>
      <li><a class="nav-link" href="disciplinary-action.php">Disciplinary Action</a></li>
      <li><a class="nav-link" href="risk-level.php">Risk Level</a></li>
      <li><a class="nav-link" href="student-appeals.php">Student Appeals</a></li>
      <li><a class="nav-link" href="data-backup.php">Data Backup</a></li>
      <li><a class="nav-link" href="user-management.php">User Management</a></li>
    </ul>
  </div>
</div>

<main class="col-md-10 px-3 py-3">
  <h5 class="mb-3">Data Backup & Export</h5>
  <a href="?export=all" class="btn btn-success mb-4"><i class="bi bi-download"></i> Export All Data</a>

  <!-- STUDENTS TABLE -->
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>Student Records (<?= $studentCount ?> total)</strong>
      <a href="?export=students" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i> Export</a>
    </div>
    <div class="table-responsive card-body p-0">
    <table class="table table-sm table-bordered mb-0">
      <thead class="table-dark"><tr><th>Student ID</th><th>Full Name</th><th>Course</th><th>Year</th><th>Department</th></tr></thead>
      <tbody>
      <?php foreach($students as $s): ?>
      <tr><td><?= htmlspecialchars($s['student_id']) ?></td><td><?= htmlspecialchars($s['full_name']) ?></td>
          <td><?= htmlspecialchars($s['cname']) ?></td><td>Year <?= $s['year_level'] ?></td><td><?= htmlspecialchars($s['dname']) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>

  <!-- VIOLATIONS TABLE -->
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>Violation Records (<?= $violationCount ?> total)</strong>
      <a href="?export=violations" class="btn btn-sm btn-outline-danger"><i class="bi bi-download"></i> Export</a>
    </div>
    <div class="table-responsive card-body p-0">
    <table class="table table-sm table-bordered mb-0">
      <thead class="table-dark"><tr><th>Student ID</th><th>Name</th><th>Type</th><th>Violation</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
      <?php foreach($violations as $v): ?>
      <tr><td><?= htmlspecialchars($v['student_id']) ?></td><td><?= htmlspecialchars($v['full_name']) ?></td>
          <td><?= ucfirst($v['category']) ?></td><td><?= htmlspecialchars($v['violation']) ?></td>
          <td><?= ucfirst($v['status']) ?></td><td><?= date('M d Y', strtotime($v['date_submitted'])) ?></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>

  <!-- DISCIPLINARY -->
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>Disciplinary Actions (<?= $disciplinaryCount ?> total)</strong>
      <a href="?export=disciplinary" class="btn btn-sm btn-outline-warning"><i class="bi bi-download"></i> Export</a>
    </div>
  </div>

  <!-- RISK LEVEL -->
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>Risk Level Data</strong>
      <a href="?export=risk" class="btn btn-sm btn-outline-info"><i class="bi bi-download"></i> Export</a>
    </div>
  </div>
</main>
</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>