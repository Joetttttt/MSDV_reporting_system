<?php
session_start();
require_once '../db/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php'); exit;
}

define('DELETE_PASSWORD', 'delete123');

// DELETE VIOLATION
if (isset($_POST['action']) && $_POST['action'] === 'delete_violation') {
    $vid  = (int)$_POST['violation_id'];
    $pass = trim($_POST['del_password']);
    if ($pass === DELETE_PASSWORD) {
        $pdo->prepare("DELETE FROM disciplinary_actions WHERE violation_id=?")->execute([$vid]);
        $pdo->prepare("DELETE FROM appeals WHERE violation_id=?")->execute([$vid]);
        $pdo->prepare("DELETE FROM violations WHERE id=?")->execute([$vid]);
        $_SESSION['msg'] = 'Violation deleted.';
    } else {
        $_SESSION['err'] = 'Incorrect deletion password.';
    }
    header('Location: violation-records.php'); exit;
}

// SEARCH & FILTER
$search   = trim($_GET['search'] ?? '');
$filterCat= $_GET['category'] ?? '';
$filterStatus = $_GET['status'] ?? '';

$where = "WHERE 1=1";
$params = [];
if ($search) {
    $where .= " AND (s.full_name LIKE ? OR v.student_id LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
}
if ($filterCat)    { $where .= " AND v.category=?";  $params[] = $filterCat; }
if ($filterStatus) { $where .= " AND v.status=?";    $params[] = $filterStatus; }

$stmt = $pdo->prepare("
    SELECT v.*, s.full_name, s.course_id, s.year_level,
           c.name as course_name, d.name as dept_name,
           u.full_name as reporter_name, u.role as reporter_role,
           da.sanction, da.status as da_status
    FROM violations v
    JOIN students s ON v.student_id = s.student_id
    JOIN courses c ON s.course_id = c.id
    JOIN departments d ON s.department_id = d.id
    JOIN users u ON v.reporter_id = u.id
    LEFT JOIN disciplinary_actions da ON da.violation_id = v.id
    $where
    ORDER BY v.date_submitted DESC");
$stmt->execute($params);
$violations = $stmt->fetchAll();

// Get tally per student
function getMinorTally($pdo, $sid) {
    return (int)$pdo->prepare("SELECT COUNT(*) FROM violations WHERE student_id=? AND category='minor'")->execute([$sid]) ? $pdo->prepare("SELECT COUNT(*) FROM violations WHERE student_id=? AND category='minor'")->execute([$sid]) : 0;
}

// Highlight
$highlight = (int)($_GET['highlight'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Violation Records - MDSV</title>
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
<div class="container-fluid">
<div class="row">
<nav class="col-md-2 d-none d-md-block bg-light py-3" style="min-height:100vh">
  <ul class="nav flex-column">
    <li><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
    <li><a class="nav-link" href="student-records.php"><i class="bi bi-people"></i> Student Records</a></li>
    <li><a class="nav-link active fw-bold" href="violation-records.php"><i class="bi bi-exclamation-triangle"></i> Violation Records</a></li>
    <li><a class="nav-link" href="disciplinary-action.php"><i class="bi bi-shield-exclamation"></i> Disciplinary Action</a></li>
    <li><a class="nav-link" href="risk-level.php"><i class="bi bi-bar-chart"></i> Risk Level</a></li>
    <li><a class="nav-link" href="student-appeals.php"><i class="bi bi-chat-left-text"></i> Student Appeals</a></li>
    <li><a class="nav-link" href="data-backup.php"><i class="bi bi-download"></i> Data Backup</a></li>
    <li><a class="nav-link" href="user-management.php"><i class="bi bi-person-gear"></i> User Management</a></li>
  </ul>
</nav>
<div class="d-md-none p-2 bg-light w-100">
  <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#mob3"><i class="bi bi-list"></i> Menu</button>
  <div class="collapse" id="mob3">
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
  <h5 class="mb-3">Violation Records</h5>

  <?php if(isset($_SESSION['msg'])): ?>
    <div class="alert alert-success"><?= $_SESSION['msg'] ?><?php unset($_SESSION['msg']); ?></div>
  <?php endif; ?>
  <?php if(isset($_SESSION['err'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['err'] ?><?php unset($_SESSION['err']); ?></div>
  <?php endif; ?>

  <!-- SEARCH & FILTER -->
  <form method="GET" class="row g-2 mb-3">
    <div class="col-md-4 col-7">
      <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name or ID"
             value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-2 col-5">
      <select name="category" class="form-select form-select-sm">
        <option value="">All Types</option>
        <option value="minor" <?=$filterCat==='minor'?'selected':''?>>Minor</option>
        <option value="major" <?=$filterCat==='major'?'selected':''?>>Major</option>
      </select>
    </div>
    <div class="col-md-2 col-5">
      <select name="status" class="form-select form-select-sm">
        <option value="">All Status</option>
        <option value="pending"   <?=$filterStatus==='pending'  ?'selected':''?>>Pending</option>
        <option value="ongoing"   <?=$filterStatus==='ongoing'  ?'selected':''?>>Ongoing</option>
        <option value="completed" <?=$filterStatus==='completed'?'selected':''?>>Completed</option>
      </select>
    </div>
    <div class="col-auto"><button type="submit" class="btn btn-primary btn-sm">Filter</button></div>
    <div class="col-auto"><a href="violation-records.php" class="btn btn-secondary btn-sm">Reset</a></div>
  </form>

  <div class="table-responsive">
  <table class="table table-bordered table-sm table-hover">
    <thead class="table-dark">
      <tr>
        <th>Student ID</th><th>Full Name</th><th>Type</th>
        <th>Violation</th><th>Date</th><th>Tally</th><th>Status</th><th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php
    // Pre-fetch tallies
    $tallyStmt = $pdo->query("SELECT student_id, category, COUNT(*) as cnt FROM violations GROUP BY student_id, category");
    $tallies = [];
    foreach($tallyStmt->fetchAll() as $t) {
        $tallies[$t['student_id']][$t['category']] = (int)$t['cnt'];
    }

    foreach($violations as $v):
        $minorCount = $tallies[$v['student_id']]['minor'] ?? 0;
        $majorCount = $tallies[$v['student_id']]['major'] ?? 0;
        $rowClass = ($highlight && $highlight == $v['id']) ? 'table-warning' : '';
    ?>
    <tr class="<?= $rowClass ?>" id="row-<?= $v['id'] ?>">
      <td><?= htmlspecialchars($v['student_id']) ?></td>
      <td><?= htmlspecialchars($v['full_name']) ?></td>
      <td><span class="badge bg-<?= $v['category']==='minor'?'primary':'danger' ?>"><?= ucfirst($v['category']) ?></span></td>
      <td><?= htmlspecialchars($v['violation']) ?></td>
      <td><?= date('M d, Y', strtotime($v['date_submitted'])) ?></td>
      <td>
        <!-- TALLY BOXES -->
        <div class="d-flex gap-1 flex-wrap">
          <?php if($v['category'] === 'minor'): ?>
            <?php for($i=1;$i<=5;$i++): ?>
              <div style="width:18px;height:18px;border:2px solid #0d6efd;background:<?= $i<=$minorCount?'#0d6efd':'transparent' ?>;border-radius:3px"></div>
            <?php endfor; ?>
          <?php else: ?>
            <?php for($i=1;$i<=3;$i++): ?>
              <div style="width:18px;height:18px;border:2px solid #dc3545;background:<?= $i<=$majorCount?'#dc3545':'transparent' ?>;border-radius:3px"></div>
            <?php endfor; ?>
          <?php endif; ?>
        </div>
      </td>
      <td><span class="badge bg-<?= $v['status']==='completed'?'success':($v['status']==='ongoing'?'warning':'secondary') ?>"><?= ucfirst($v['status']) ?></span></td>
      <td>
        <button class="btn btn-info btn-sm" onclick='openViolationView(<?= json_encode($v) ?>)'>View</button>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($violations)): ?>
    <tr><td colspan="8" class="text-center text-muted">No violations found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
</main>
</div>
</div>

<!-- VIEW VIOLATION MODAL -->
<div class="modal fade" id="viewViolationModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Violation Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="vvBody"></div>
      <div class="modal-footer">
        <button class="btn btn-danger btn-sm" id="vvDeleteBtn">Delete</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- DELETE VIOLATION MODAL -->
<div class="modal fade" id="delViolModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <input type="hidden" name="action" value="delete_violation">
      <input type="hidden" name="violation_id" id="delViolID">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Delete Violation</h5>
        <button type="button" class="btn-close btn-close-white"