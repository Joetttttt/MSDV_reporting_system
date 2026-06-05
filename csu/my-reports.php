<?php
session_start();
require_once '../db/connection.php';
$allowedRoles = ['teacher','csu','jassu'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], $allowedRoles)) {
    header('Location: ../index.php'); exit;
}

$role = $_SESSION['role'];
$reports = $pdo->prepare("
    SELECT v.*, s.full_name, s.student_id as sid,
           c.name as cname, d.name as dname,
           da.sanction, da.status as da_status
    FROM violations v
    JOIN students s ON v.student_id = s.student_id
    JOIN courses c ON s.course_id = c.id
    JOIN departments d ON s.department_id = d.id
    LEFT JOIN disciplinary_actions da ON da.violation_id = v.id
    WHERE v.reporter_id = ?
    ORDER BY v.date_submitted DESC");
$reports->execute([$_SESSION['user_id']]);
$myReports = $reports->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Reports - MDSV</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
<nav class="navbar navbar-dark bg-success px-3">
  <span class="navbar-brand fw-bold">MDSV - <?= ucfirst($role) ?></span>
  <div class="d-flex align-items-center gap-2">
    <span class="text-white small d-none d-md-inline"><?= htmlspecialchars($_SESSION['full_name']) ?></span>
    <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
  </div>
</nav>
<div class="container-fluid"><div class="row">
<nav class="col-md-2 d-none d-md-block bg-light py-3" style="min-height:100vh">
  <ul class="nav flex-column">
    <li><a class="nav-link" href="dashboard.php"><i class="bi bi-house"></i> Dashboard</a></li>
    <li><a class="nav-link" href="report-form.php"><i class="bi bi-file-earmark-plus"></i> Report Violation</a></li>
    <li><a class="nav-link active fw-bold" href="my-reports.php"><i class="bi bi-list-ul"></i> My Reports</a></li>
  </ul>
</nav>
<div class="d-md-none p-2 bg-light w-100">
  <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#mobMR"><i class="bi bi-list"></i> Menu</button>
  <div class="collapse" id="mobMR">
    <ul class="nav flex-column mt-1">
      <li><a class="nav-link" href="dashboard.php">Dashboard</a></li>
      <li><a class="nav-link" href="report-form.php">Report Violation</a></li>
      <li><a class="nav-link" href="my-reports.php">My Reports</a></li>
    </ul>
  </div>
</div>
<main class="col-md-10 px-3 py-3">
  <h5 class="mb-3">My Submitted Reports</h5>
  <div class="table-responsive">
  <table class="table table-bordered table-sm table-hover">
    <thead class="table-dark">
      <tr><th>Student ID</th><th>Name</th><th>Course/Dept</th><th>Type</th><th>Violation</th><th>Description</th><th>Status</th><th>Sanction</th><th>Date</th><th>Evidence</th></tr>
    </thead>
    <tbody>
    <?php foreach($myReports as $r): ?>
    <tr>
      <td><?= htmlspecialchars($r['student_id']) ?></td>
      <td><?= htmlspecialchars($r['full_name']) ?></td>
      <td><small><?= htmlspecialchars($r['cname']) ?><br><?= htmlspecialchars($r['dname']) ?></small></td>
      <td><span class="badge bg-<?= $r['category']==='minor'?'primary':'danger' ?>"><?= ucfirst($r['category']) ?></span></td>
      <td><?= htmlspecialchars($r['violation']) ?></td>
      <td><small><?= htmlspecialchars(substr($r['description'],0,60)) ?></small></td>
      <td><span class="badge bg-<?= $r['status']==='completed'?'success':($r['status']==='ongoing'?'warning':'secondary') ?>"><?= ucfirst($r['status']) ?></span></td>
      <td><small><?= htmlspecialchars(substr($r['sanction'] ?? '-', 0, 60)) ?></small></td>
      <td><?= date('M d Y', strtotime($r['date_submitted'])) ?></td>
      <td>
        <?php if($r['evidence_path']): ?>
          <a href="../<?= $r['evidence_path'] ?>" target="_blank" class="btn btn-sm btn-outline-info">View</a>
        <?php else: ?>-<?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($myReports)): ?>
    <tr><td colspan="10" class="text-center text-muted">No reports submitted yet.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
</main>
</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>