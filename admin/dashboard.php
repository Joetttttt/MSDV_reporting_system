<?php
session_start();
require_once '../db/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php'); exit;
}

// --- CARD COUNTS ---
$totalStudents   = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalViolations = $pdo->query("SELECT COUNT(*) FROM violations")->fetchColumn();
$pendingSanctions= $pdo->query("SELECT COUNT(*) FROM disciplinary_actions WHERE status='pending'")->fetchColumn();
$completedCases  = $pdo->query("SELECT COUNT(*) FROM disciplinary_actions WHERE status='completed'")->fetchColumn();

// --- CHART DATA ---
// 1. Violations per month (current year)
$currentYear = date('Y');
$vpm = $pdo->prepare("
    SELECT MONTH(date_submitted) as month, COUNT(*) as total
    FROM violations WHERE YEAR(date_submitted)=?
    GROUP BY MONTH(date_submitted) ORDER BY month");
$vpm->execute([$currentYear]);
$vpmData = array_fill(1,12,0);
foreach($vpm->fetchAll() as $r) $vpmData[$r['month']] = (int)$r['total'];

// 2. Minor vs Major per month
$mvmMinor = $pdo->prepare("SELECT MONTH(date_submitted) m, COUNT(*) c FROM violations WHERE YEAR(date_submitted)=? AND category='minor' GROUP BY m");
$mvmMinor->execute([$currentYear]);
$minorData = array_fill(1,12,0);
foreach($mvmMinor->fetchAll() as $r) $minorData[$r['m']] = (int)$r['c'];

$mvmMajor = $pdo->prepare("SELECT MONTH(date_submitted) m, COUNT(*) c FROM violations WHERE YEAR(date_submitted)=? AND category='major' GROUP BY m");
$mvmMajor->execute([$currentYear]);
$majorData = array_fill(1,12,0);
foreach($mvmMajor->fetchAll() as $r) $majorData[$r['m']] = (int)$r['c'];

// 3. Violations by department
$vbd = $pdo->prepare("SELECT d.name, COUNT(v.id) as total FROM violations v JOIN students s ON v.student_id=s.student_id JOIN departments d ON s.department_id=d.id WHERE YEAR(v.date_submitted)=? GROUP BY d.id");
$vbd->execute([$currentYear]);
$deptLabels=[]; $deptData=[]; $deptColors=[];
$deptColorMap=['School of Technology'=>'#dc3545','School of Education'=>'#0d6efd','School of Business'=>'#fd7e14'];
foreach($vbd->fetchAll() as $r){
    $deptLabels[]=$r['name']; $deptData[]=(int)$r['total'];
    $deptColors[]=$deptColorMap[$r['name']] ?? '#6c757d';
}

// 4. Violations by course
$vbc = $pdo->prepare("SELECT c.name, d.name as dept, COUNT(v.id) as total FROM violations v JOIN students s ON v.student_id=s.student_id JOIN courses c ON s.course_id=c.id JOIN departments d ON s.department_id=d.id WHERE YEAR(v.date_submitted)=? GROUP BY c.id ORDER BY d.id");
$vbc->execute([$currentYear]);
$courseLabels=[]; $courseData=[]; $courseColors=[];
$courseColorMap=['School of Technology'=>'#dc3545','School of Education'=>'#0d6efd','School of Business'=>'#fd7e14'];
foreach($vbc->fetchAll() as $r){
    $courseLabels[]=$r['name']; $courseData[]=(int)$r['total'];
    $courseColors[]=$courseColorMap[$r['dept']] ?? '#6c757d';
}

// 5. Specific violation by department (line chart) - top violation filter
$violations_list = [
    'Disruptive Behavior','Littering','Dress Code','Unapproved Absences','Inappropriate Language',
    'Unauthorized Use of College Property','Smoking on Campus','Failure to Display ID',
    'Noise Violations','Minor Vandalism','Academic Dishonesty','Theft','Physical Violence',
    'Substance Abuse','Harassment','Unauthorized Entry','Forgery','Moral Infractions',
    'Weapons Possession','Cyber Bullying','Hazing','Major Dishonesty','Extortion',
    'Sexual Misconduct'
];

// Get years for filters
$years = $pdo->query("SELECT DISTINCT YEAR(date_submitted) y FROM violations ORDER BY y DESC")->fetchAll(PDO::FETCH_COLUMN);
if (empty($years)) $years = [date('Y')];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - MDSV</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="manifest" href="../manifest.json">
<meta name="theme-color" content="#0d6efd">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-dark bg-primary px-3">
  <span class="navbar-brand fw-bold">MDSV Admin</span>
  <div class="d-flex align-items-center gap-3">
    <!-- Notification Bell -->
    <div class="dropdown">
      <button class="btn btn-outline-light btn-sm position-relative" id="notifBtn" data-bs-toggle="dropdown">
        <i class="bi bi-bell"></i>
        <?php
        $unread = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
        $unread->execute([$_SESSION['user_id']]);
        $unreadCount = $unread->fetchColumn();
        if($unreadCount > 0): ?>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $unreadCount ?></span>
        <?php endif; ?>
      </button>
      <ul class="dropdown-menu dropdown-menu-end" style="min-width:300px;max-height:350px;overflow-y:auto">
        <?php
        $notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 20");
        $notifs->execute([$_SESSION['user_id']]);
        $notifList = $notifs->fetchAll();
        if(empty($notifList)): ?>
          <li><span class="dropdown-item text-muted">No notifications</span></li>
        <?php else: foreach($notifList as $n): ?>
          <li>
            <a class="dropdown-item <?= $n['is_read']?'':'fw-bold' ?>" href="<?= $n['link'] ?? '#' ?>"
               onclick="markRead(<?= $n['id'] ?>)">
              <small><?= htmlspecialchars($n['message']) ?></small><br>
              <span class="text-muted" style="font-size:11px"><?= $n['created_at'] ?></span>
            </a>
          </li>
        <?php endforeach; endif; ?>
      </ul>
    </div>
    <span class="text-white small"><?= htmlspecialchars($_SESSION['full_name']) ?></span>
    <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
  </div>
</nav>

<div class="container-fluid">
<div class="row">

<!-- SIDEBAR -->
<nav class="col-md-2 col-lg-2 d-none d-md-block bg-light sidebar py-3" style="min-height:100vh">
  <ul class="nav flex-column">
    <li class="nav-item"><a class="nav-link active" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
    <li class="nav-item"><a class="nav-link" href="student-records.php"><i class="bi bi-people"></i> Student Records</a></li>
    <li class="nav-item"><a class="nav-link" href="violation-records.php"><i class="bi bi-exclamation-triangle"></i> Violation Records</a></li>
    <li class="nav-item"><a class="nav-link" href="disciplinary-action.php"><i class="bi bi-shield-exclamation"></i> Disciplinary Action</a></li>
    <li class="nav-item"><a class="nav-link" href="risk-level.php"><i class="bi bi-bar-chart"></i> Risk Level</a></li>
    <li class="nav-item"><a class="nav-link" href="student-appeals.php"><i class="bi bi-chat-left-text"></i> Student Appeals</a></li>
    <li class="nav-item"><a class="nav-link" href="data-backup.php"><i class="bi bi-download"></i> Data Backup</a></li>
    <li class="nav-item"><a class="nav-link" href="user-management.php"><i class="bi bi-person-gear"></i> User Management</a></li>
  </ul>
</nav>

<!-- MOBILE SIDEBAR TOGGLE -->
<div class="d-md-none p-2 bg-light">
  <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#mobileSidebar">
    <i class="bi bi-list"></i> Menu
  </button>
  <div class="collapse" id="mobileSidebar">
    <ul class="nav flex-column mt-2">
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

<!-- MAIN CONTENT -->
<main class="col-md-10 ms-sm-auto px-4 py-3">
  <h5 class="mb-3">Dashboard</h5>

  <!-- 4 CARDS -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card text-white bg-primary">
        <div class="card-body">
          <div class="small">Total Students</div>
          <h3><?= $totalStudents ?></h3>
          <i class="bi bi-people fs-4"></i>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-white bg-danger">
        <div class="card-body">
          <div class="small">Total Violations</div>
          <h3><?= $totalViolations ?></h3>
          <i class="bi bi-exclamation-triangle fs-4"></i>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-white bg-warning">
        <div class="card-body">
          <div class="small">Pending Sanctions</div>
          <h3><?= $pendingSanctions ?></h3>
          <i class="bi bi-hourglass-split fs-4"></i>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-white bg-success">
        <div class="card-body">
          <div class="small">Completed Cases</div>
          <h3><?= $completedCases ?></h3>
          <i class="bi bi-check-circle fs-4"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- CHART ROW 1 -->
  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <div class="card p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <strong>Violations Per Month</strong>
          <select class="form-select form-select-sm w-auto" onchange="updateVPM(this.value)">
            <?php foreach($years as $y): ?>
            <option value="<?=$y?>" <?=$y==$currentYear?'selected':''?>><?=$y?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <canvas id="vpmChart"></canvas>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <strong>Minor vs Major Per Month</strong>
          <select class="form-select form-select-sm w-auto" onchange="updateMVM(this.value)">
            <?php foreach($years as $y): ?>
            <option value="<?=$y?>" <?=$y==$currentYear?'selected':''?>><?=$y?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <canvas id="mvmChart"></canvas>
      </div>
    </div>
  </div>

  <!-- CHART ROW 2 -->
  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <div class="card p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <strong>Violations by Department</strong>
          <select class="form-select form-select-sm w-auto" onchange="updateDept(this.value)">
            <?php foreach($years as $y): ?>
            <option value="<?=$y?>" <?=$y==$currentYear?'selected':''?>><?=$y?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <canvas id="deptChart"></canvas>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <strong>Violations by Course</strong>
          <select class="form-select form-select-sm w-auto" onchange="updateCourse(this.value)">
            <?php foreach($years as $y): ?>
            <option value="<?=$y?>" <?=$y==$currentYear?'selected':''?>><?=$y?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <canvas id="courseChart"></canvas>
      </div>
    </div>
  </div>

  <!-- CHART ROW 3: Specific Violation by Dept (Line) -->
  <div class="row g-3 mb-3">
    <div class="col-12">
      <div class="card p-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
          <strong>Specific Violation by Department</strong>
          <div class="d-flex gap-2">
            <select class="form-select form-select-sm w-auto" id="svYear" onchange="updateSV()">
              <?php foreach($years as $y): ?>
              <option value="<?=$y?>" <?=$y==$currentYear?'selected':''?>><?=$y?></option>
              <?php endforeach; ?>
            </select>
            <select class="form-select form-select-sm w-auto" id="svViolation" onchange="updateSV()">
              <?php foreach($violations_list as $vl): ?>
              <option value="<?= htmlspecialchars($vl) ?>"><?= htmlspecialchars($vl) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <canvas id="svChart"></canvas>
      </div>
    </div>
  </div>

</main>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

// --- VPM Chart ---
let vpmChart = new Chart(document.getElementById('vpmChart'), {
  type: 'bar',
  data: {
    labels: months,
    datasets: [{
      label: 'Violations',
      data: <?= json_encode(array_values($vpmData)) ?>,
      backgroundColor: '#0d6efd'
    }]
  },
  options: { responsive: true, plugins: { legend: { display: false } } }
});

function updateVPM(year) {
  fetch('ajax/chart-data.php?type=vpm&year=' + year)
    .then(r => r.json()).then(d => { vpmChart.data.datasets[0].data = d; vpmChart.update(); });
}

// --- MVM Chart ---
let mvmChart = new Chart(document.getElementById('mvmChart'), {
  type: 'bar',
  data: {
    labels: months,
    datasets: [
      { label: 'Minor', data: <?= json_encode(array_values($minorData)) ?>, backgroundColor: '#0d6efd' },
      { label: 'Major', data: <?= json_encode(array_values($majorData)) ?>, backgroundColor: '#dc3545' }
    ]
  },
  options: { responsive: true }
});

function updateMVM(year) {
  fetch('ajax/chart-data.php?type=mvm&year=' + year)
    .then(r => r.json()).then(d => {
      mvmChart.data.datasets[0].data = d.minor;
      mvmChart.data.datasets[1].data = d.major;
      mvmChart.update();
    });
}

// --- Dept Chart ---
let deptChart = new Chart(document.getElementById('deptChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($deptLabels) ?>,
    datasets: [{ label: 'Violations', data: <?= json_encode($deptData) ?>, backgroundColor: <?= json_encode($deptColors) ?> }]
  },
  options: { responsive: true, plugins: { legend: { display: false } } }
});

function updateDept(year) {
  fetch('ajax/chart-data.php?type=dept&year=' + year)
    .then(r => r.json()).then(d => {
      deptChart.data.labels = d.labels;
      deptChart.data.datasets[0].data = d.data;
      deptChart.data.datasets[0].backgroundColor = d.colors;
      deptChart.update();
    });
}

// --- Course Chart ---
let courseChart = new Chart(document.getElementById('courseChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($courseLabels) ?>,
    datasets: [{ label: 'Violations', data: <?= json_encode($courseData) ?>, backgroundColor: <?= json_encode($courseColors) ?> }]
  },
  options: { responsive: true, plugins: { legend: { display: false } } }
});

function updateCourse(year) {
  fetch('ajax/chart-data.php?type=course&year=' + year)
    .then(r => r.json()).then(d => {
      courseChart.data.labels = d.labels;
      courseChart.data.datasets[0].data = d.data;
      courseChart.data.datasets[0].backgroundColor = d.colors;
      courseChart.update();
    });
}

// --- Specific Violation by Dept (Line) ---
let svChart = new Chart(document.getElementById('svChart'), {
  type: 'line',
  data: {
    labels: months,
    datasets: [
      { label: 'SOT', data: Array(12).fill(0), borderColor: '#dc3545', fill: false },
      { label: 'SOE', data: Array(12).fill(0), borderColor: '#0d6efd', fill: false },
      { label: 'SOB', data: Array(12).fill(0), borderColor: '#fd7e14', fill: false }
    ]
  },
  options: { responsive: true }
});

function updateSV() {
  const year = document.getElementById('svYear').value;
  const viol = document.getElementById('svViolation').value;
  fetch('ajax/chart-data.php?type=sv&year=' + year + '&violation=' + encodeURIComponent(viol))
    .then(r => r.json()).then(d => {
      svChart.data.datasets[0].data = d.sot;
      svChart.data.datasets[1].data = d.soe;
      svChart.data.datasets[2].data = d.sob;
      svChart.update();
    });
}

// Load initial SV chart
updateSV();

// Mark notification read
function markRead(id) {
  fetch('ajax/notifications.php?action=read&id=' + id);
}
</script>
</body>
</html>