<?php
session_start();
require_once '../db/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php'); exit;
}

// Get all students with their violation counts
$allStudents = $pdo->query("
    SELECT s.student_id, s.full_name,
        SUM(CASE WHEN v.category='minor' THEN 1 ELSE 0 END) as minor_count,
        SUM(CASE WHEN v.category='major' THEN 1 ELSE 0 END) as major_count,
        COUNT(v.id) as total
    FROM students s
    LEFT JOIN violations v ON s.student_id = v.student_id
    GROUP BY s.student_id
")->fetchAll();

function getRisk($total) {
    if ($total >= 5) return ['label'=>'Critical','class'=>'danger'];
    if ($total >= 4) return ['label'=>'High','class'=>'warning'];
    if ($total >= 2) return ['label'=>'Moderate','class'=>'info'];
    return ['label'=>'Low','class'=>'success'];
}

$moderate=0; $high=0; $critical=0;
foreach($allStudents as $s) {
    $r = getRisk($s['total']);
    if($r['label']==='Moderate') $moderate++;
    if($r['label']==='High')     $high++;
    if($r['label']==='Critical') $critical++;
}

$search = trim($_GET['search'] ?? '');
$filterRisk = $_GET['risk'] ?? '';

$filtered = array_filter($allStudents, function($s) use ($search, $filterRisk) {
    $r = getRisk($s['total']);
    $matchSearch = !$search || stripos($s['full_name'], $search)!==false || stripos($s['student_id'], $search)!==false;
    $matchRisk   = !$filterRisk || $r['label'] === $filterRisk;
    return $matchSearch && $matchRisk;
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Risk Level - MDSV</title>
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
    <li><a class="nav-link active fw-bold" href="risk-level.php"><i class="bi bi-bar-chart"></i> Risk Level</a></li>
    <li><a class="nav-link" href="student-appeals.php"><i class="bi bi-chat-left-text"></i> Student Appeals</a></li>
    <li><a class="nav-link" href="data-backup.php"><i class="bi bi-download"></i> Data Backup</a></li>
    <li><a class="nav-link" href="user-management.php"><i class="bi bi-person-gear"></i> User Management</a></li>
  </ul>
</nav>
<div class="d-md-none p-2 bg-light w-100">
  <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#mob5"><i class="bi bi-list"></i> Menu</button>
  <div class="collapse" id="mob5">
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
  <h5 class="mb-3">Risk Level Indicator</h5>

  <!-- CARDS -->
  <div class="row g-3 mb-4">
    <div class="col-4">
      <div class="card text-white bg-info">
        <div class="card-body text-center">
          <div class="small">Moderate Risk</div>
          <h3><?= $moderate ?></h3>
          <small>2+ violations</small>
        </div>
      </div>
    </div>
    <div class="col-4">
      <div class="card text-white bg-warning">
        <div class="card-body text-center">
          <div class="small">High Risk</div>
          <h3><?= $high ?></h3>
          <small>4+ violations</small>
        </div>
      </div>
    </div>
    <div class="col-4">
      <div class="card text-white bg-danger">
        <div class="card-body text-center">
          <div class="small">Critical</div>
          <h3><?= $critical ?></h3>
          <small>5+ violations</small>
        </div>
      </div>
    </div>
  </div>

  <!-- FILTER -->
  <form method="GET" class="row g-2 mb-3">
    <div class="col-md-4 col-7">
      <input type="text" name="search" class="form-control form-control-sm"
             placeholder="Search name or ID" value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-2 col-5">
      <select name="risk" class="form-select form-select-sm">
        <option value="">All Risk</option>
        <option value="Moderate" <?=$filterRisk==='Moderate'?'selected':''?>>Moderate</option>
        <option value="High"     <?=$filterRisk==='High'    ?'selected':''?>>High</option>
        <option value="Critical" <?=$filterRisk==='Critical'?'selected':''?>>Critical</option>
        <option value="Low"      <?=$filterRisk==='Low'     ?'selected':''?>>Low</option>
      </select>
    </div>
    <div class="col-auto"><button type="submit" class="btn btn-primary btn-sm">Filter</button></div>
    <div class="col-auto"><a href="risk-level.php" class="btn btn-secondary btn-sm">Reset</a></div>
  </form>

  <div class="table-responsive">
  <table class="table table-bordered table-sm table-hover">
    <thead class="table-dark">
      <tr>
        <th>Student ID</th><th>Full Name</th>
        <th>Minor Tally</th><th>Major Tally</th><th>Risk Level</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach($filtered as $s):
        $risk = getRisk($s['total']);
    ?>
    <tr>
      <td><?= htmlspecialchars($s['student_id']) ?></td>
      <td><?= htmlspecialchars($s['full_name']) ?></td>
      <td>
        <div class="d-flex gap-1">
          <?php for($i=1;$i<=5;$i++): ?>
            <div style="width:16px;height:16px;border:2px solid #0d6efd;background:<?= $i<=$s['minor_count']?'#0d6efd':'transparent' ?>;border-radius:3px"></div>
          <?php endfor; ?>
        </div>
      </td>
      <td>
        <div class="d-flex gap-1">
          <?php for($i=1;$i<=3;$i++): ?>
            <div style="width:16px;height:16px;border:2px solid #dc3545;background:<?= $i<=$s['major_count']?'#dc3545':'transparent' ?>;border-radius:3px"></div>
          <?php endfor; ?>
        </div>
      </td>
      <td><span class="badge bg-<?= $risk['class'] ?>"><?= $risk['label'] ?></span></td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($filtered)): ?>
    <tr><td colspan="5" class="text-center text-muted">No records found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
</main>
</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>