<?php
session_start();
require_once '../db/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php'); exit;
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");

$currentYear = date('Y');

// CARDS
$totalStudents    = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalViolations  = $pdo->query("SELECT COUNT(*) FROM violations")->fetchColumn();
$pendingSanctions = $pdo->query("SELECT COUNT(*) FROM disciplinary_actions WHERE status='pending'")->fetchColumn();
$completedCases   = $pdo->query("SELECT COUNT(*) FROM disciplinary_actions WHERE status='completed'")->fetchColumn();

// NOTIFICATIONS
$unreadStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
$unreadStmt->execute([$_SESSION['user_id']]);
$unreadCount = (int)$unreadStmt->fetchColumn();
$notifStmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 20");
$notifStmt->execute([$_SESSION['user_id']]);
$notifList = $notifStmt->fetchAll();

// CHART 1: violations per month
$vpm = $pdo->prepare("SELECT MONTH(date_submitted) m, COUNT(*) c FROM violations WHERE YEAR(date_submitted)=? GROUP BY m");
$vpm->execute([$currentYear]);
$vpmData = array_fill(0,12,0);
foreach($vpm->fetchAll() as $r) $vpmData[$r['m']-1] = (int)$r['c'];

// CHART 2: minor vs major per month
$minorData = array_fill(0,12,0);
$majorData = array_fill(0,12,0);
$s1 = $pdo->prepare("SELECT MONTH(date_submitted) m, COUNT(*) c FROM violations WHERE YEAR(date_submitted)=? AND category='minor' GROUP BY m");
$s1->execute([$currentYear]);
foreach($s1->fetchAll() as $r) $minorData[$r['m']-1] = (int)$r['c'];
$s2 = $pdo->prepare("SELECT MONTH(date_submitted) m, COUNT(*) c FROM violations WHERE YEAR(date_submitted)=? AND category='major' GROUP BY m");
$s2->execute([$currentYear]);
foreach($s2->fetchAll() as $r) $majorData[$r['m']-1] = (int)$r['c'];

// CHART 3: violations by department
$deptColorMap = ['School of Technology'=>'#dc3545','School of Education'=>'#0d6efd','School of Business'=>'#fd7e14'];
$vbd = $pdo->prepare("SELECT d.name, COUNT(v.id) c FROM violations v JOIN students s ON v.student_id=s.student_id JOIN departments d ON s.department_id=d.id WHERE YEAR(v.date_submitted)=? GROUP BY d.id");
$vbd->execute([$currentYear]);
$deptLabels=[]; $deptData=[]; $deptColors=[];
foreach($vbd->fetchAll() as $r) {
    $deptLabels[]=$r['name']; $deptData[]=(int)$r['c'];
    $deptColors[]=$deptColorMap[$r['name']] ?? '#6c757d';
}

// CHART 4: violations by course
$vbc = $pdo->prepare("SELECT c.name cn, d.name dn, COUNT(v.id) c FROM violations v JOIN students s ON v.student_id=s.student_id JOIN courses c ON s.course_id=c.id JOIN departments d ON s.department_id=d.id WHERE YEAR(v.date_submitted)=? GROUP BY c.id ORDER BY d.id");
$vbc->execute([$currentYear]);
$courseLabels=[]; $courseData=[]; $courseColors=[];
foreach($vbc->fetchAll() as $r) {
    $courseLabels[]=$r['cn']; $courseData[]=(int)$r['c'];
    $courseColors[]=$deptColorMap[$r['dn']] ?? '#6c757d';
}

// YEARS for filters
$years = $pdo->query("SELECT DISTINCT YEAR(date_submitted) y FROM violations ORDER BY y DESC")->fetchAll(PDO::FETCH_COLUMN);
if (empty($years)) $years = [date('Y')];

// Violations list for specific violation chart
$violations_list = ['Disruptive Behavior','Littering','Dress Code','Unapproved Absences',
    'Inappropriate Language','Unauthorized Use of College Property','Smoking on Campus',
    'Failure to Display ID','Noise Violations','Minor Vandalism','Academic Dishonesty',
    'Theft','Physical Violence','Substance Abuse','Harassment','Unauthorized Entry',
    'Forgery','Moral Infractions','Weapons Possession','Cyber Bullying','Hazing',
    'Major Dishonesty','Extortion','Sexual Misconduct'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - MDSV Admin</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-dark bg-primary px-3 sticky-top">
  <div class="d-flex align-items-center gap-2">
    <button class="navbar-toggler d-md-none border-0" type="button"
            data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <span class="navbar-brand fw-bold mb-0">
      <i class="bi bi-shield-fill-check"></i> MDSV Admin
    </span>
  </div>
  <div class="d-flex align-items-center gap-3">

    <!-- NOTIFICATION BELL -->
    <div class="dropdown">
      <button class="btn btn-outline-light btn-sm position-relative"
              data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell-fill"></i>
        <?php if($unreadCount > 0): ?>
        <span class="position-absolute top-0 start-100 translate-middle
                     badge rounded-pill bg-danger" id="notifBadge">
          <?= $unreadCount ?>
        </span>
        <?php endif; ?>
      </button>
      <div class="dropdown-menu dropdown-menu-end shadow p-0"
           style="min-width:320px;max-height:400px;overflow-y:auto">
        <div class="d-flex justify-content-between align-items-center
                    px-3 py-2 border-bottom bg-light">
          <strong class="small">Notifications</strong>
          <?php if($unreadCount > 0): ?>
          <button class="btn btn-link btn-sm p-0 text-primary small"
                  onclick="markAllRead()">Mark all read</button>
          <?php endif; ?>
        </div>
        <?php if(empty($notifList)): ?>
        <div class="text-center text-muted py-4 small">
          <i class="bi bi-bell-slash fs-4 d-block mb-1"></i>No notifications
        </div>
        <?php else: foreach($notifList as $n): ?>
        <a class="dropdown-item py-2 border-bottom
                  <?= $n['is_read'] ? 'text-muted' : 'bg-light fw-semibold' ?>"
           href="<?= htmlspecialchars($n['link'] ?? '#') ?>"
           onclick="markRead(<?= $n['id'] ?>, this); return true;">
          <div class="small"><?= htmlspecialchars($n['message']) ?></div>
          <div class="text-muted" style="font-size:11px">
            <i class="bi bi-clock"></i>
            <?= date('M d, Y h:i A', strtotime($n['created_at'])) ?>
          </div>
        </a>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <span class="text-white small d-none d-lg-inline">
      <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['full_name']) ?>
    </span>
    <a href="../logout.php" class="btn btn-outline-light btn-sm">
      <i class="bi bi-box-arrow-right"></i>
      <span class="d-none d-sm-inline">Logout</span>
    </a>
  </div>
</nav>

<div class="container-fluid">
<div class="row">

<!-- SIDEBAR -->
<nav class="col-md-2 d-none d-md-block bg-light border-end py-3"
     style="min-height:calc(100vh - 56px)">
  <ul class="nav flex-column">
    <li><a class="nav-link active fw-bold text-primary" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
    <li><a class="nav-link text-dark" href="student-records.php"><i class="bi bi-people me-2"></i>Student Records</a></li>
    <li><a class="nav-link text-dark" href="violation-records.php"><i class="bi bi-exclamation-triangle me-2"></i>Violation Records</a></li>
    <li><a class="nav-link text-dark" href="disciplinary-action.php"><i class="bi bi-shield-exclamation me-2"></i>Disciplinary Action</a></li>
    <li><a class="nav-link text-dark" href="risk-level.php"><i class="bi bi-bar-chart me-2"></i>Risk Level</a></li>
    <li><a class="nav-link text-dark" href="student-appeals.php"><i class="bi bi-chat-left-text me-2"></i>Student Appeals</a></li>
    <li><a class="nav-link text-dark" href="data-backup.php"><i class="bi bi-download me-2"></i>Data Backup</a></li>
    <li><a class="nav-link text-dark" href="user-management.php"><i class="bi bi-person-gear me-2"></i>User Management</a></li>
  </ul>
</nav>

<!-- MOBILE SIDEBAR -->
<div class="collapse d-md-none position-fixed w-100 bg-white border-bottom shadow"
     id="sidebarMenu" style="z-index:1045;top:56px">
  <ul class="nav flex-column p-2">
    <li><a class="nav-link fw-bold text-primary" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
    <li><a class="nav-link" href="student-records.php"><i class="bi bi-people me-2"></i>Student Records</a></li>
    <li><a class="nav-link" href="violation-records.php"><i class="bi bi-exclamation-triangle me-2"></i>Violation Records</a></li>
    <li><a class="nav-link" href="disciplinary-action.php"><i class="bi bi-shield-exclamation me-2"></i>Disciplinary Action</a></li>
    <li><a class="nav-link" href="risk-level.php"><i class="bi bi-bar-chart me-2"></i>Risk Level</a></li>
    <li><a class="nav-link" href="student-appeals.php"><i class="bi bi-chat-left-text me-2"></i>Student Appeals</a></li>
    <li><a class="nav-link" href="data-backup.php"><i class="bi bi-download me-2"></i>Data Backup</a></li>
    <li><a class="nav-link" href="user-management.php"><i class="bi bi-person-gear me-2"></i>User Management</a></li>
  </ul>
</div>

<!-- MAIN -->
<main class="col-md-10 px-3 py-3">
  <h5 class="mb-3">Dashboard</h5>

  <!-- 4 CARDS -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card text-white bg-primary h-100">
        <div class="card-body">
          <div class="small">Total Students</div>
          <h3 class="mb-0"><?= $totalStudents ?></h3>
          <i class="bi bi-people fs-3 opacity-50"></i>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-white bg-danger h-100">
        <div class="card-body">
          <div class="small">Total Violations</div>
          <h3 class="mb-0"><?= $totalViolations ?></h3>
          <i class="bi bi-exclamation-triangle fs-3 opacity-50"></i>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-white bg-warning h-100">
        <div class="card-body">
          <div class="small">Pending Sanctions</div>
          <h3 class="mb-0"><?= $pendingSanctions ?></h3>
          <i class="bi bi-hourglass-split fs-3 opacity-50"></i>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card text-white bg-success h-100">
        <div class="card-body">
          <div class="small">Completed Cases</div>
          <h3 class="mb-0"><?= $completedCases ?></h3>
          <i class="bi bi-check-circle fs-3 opacity-50"></i>
        </div>
      </div>
    </div>
  </div>

  <!-- CHARTS ROW 1 -->
  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <div class="card p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <strong class="small">Violations Per Month</strong>
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
          <strong class="small">Minor vs Major Per Month</strong>
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

  <!-- CHARTS ROW 2 -->
  <div class="row g-3 mb-3">
    <div class="col-md-6">
      <div class="card p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <strong class="small">Violations by Department</strong>
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
          <strong class="small">Violations by Course</strong>
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

  <!-- CHART ROW 3: Specific Violation by Dept -->
  <div class="row g-3 mb-3">
    <div class="col-12">
      <div class="card p-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 gap-2">
          <strong class="small">Specific Violation by Department</strong>
          <div class="d-flex gap-2 flex-wrap">
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

// VPM
let vpmChart = new Chart(document.getElementById('vpmChart'), {
    type: 'bar',
    data: { labels: months, datasets: [{ label: 'Violations', data: <?= json_encode($vpmData) ?>, backgroundColor: '#0d6efd' }] },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
function updateVPM(year) {
    fetch('ajax/chart-data.php?type=vpm&year=' + year)
        .then(r => r.json()).then(d => { vpmChart.data.datasets[0].data = d; vpmChart.update(); });
}

// MVM
let mvmChart = new Chart(document.getElementById('mvmChart'), {
    type: 'bar',
    data: { labels: months, datasets: [
        { label: 'Minor', data: <?= json_encode($minorData) ?>, backgroundColor: '#0d6efd' },
        { label: 'Major', data: <?= json_encode($majorData) ?>, backgroundColor: '#dc3545' }
    ]},
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

// DEPT
let deptChart = new Chart(document.getElementById('deptChart'), {
    type: 'bar',
    data: { labels: <?= json_encode($deptLabels) ?>, datasets: [{ label: 'Violations', data: <?= json_encode($deptData) ?>, backgroundColor: <?= json_encode($deptColors) ?> }] },
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

// COURSE
let courseChart = new Chart(document.getElementById('courseChart'), {
    type: 'bar',
    data: { labels: <?= json_encode($courseLabels) ?>, datasets: [{ label: 'Violations', data: <?= json_encode($courseData) ?>, backgroundColor: <?= json_encode($courseColors) ?> }] },
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

// SV LINE
let svChart = new Chart(document.getElementById('svChart'), {
    type: 'line',
    data: { labels: months, datasets: [
        { label: 'SOT', data: Array(12).fill(0), borderColor: '#dc3545', tension: 0.3, fill: false },
        { label: 'SOE', data: Array(12).fill(0), borderColor: '#0d6efd', tension: 0.3, fill: false },
        { label: 'SOB', data: Array(12).fill(0), borderColor: '#fd7e14', tension: 0.3, fill: false }
    ]},
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
updateSV();

// NOTIFICATIONS
function markRead(id, el) {
    fetch('ajax/notifications.php?action=read&id=' + id);
    el.classList.remove('bg-light','fw-semibold');
    const badge = document.getElementById('notifBadge');
    if (badge) {
        const n = parseInt(badge.textContent) - 1;
        if (n <= 0) badge.remove(); else badge.textContent = n;
    }
}
function markAllRead() {
    fetch('ajax/notifications.php?action=read_all');
    document.querySelectorAll('.dropdown-item.bg-light.fw-semibold')
        .forEach(el => el.classList.remove('bg-light','fw-semibold'));
    const badge = document.getElementById('notifBadge');
    if (badge) badge.remove();
}
</script>
</body>
</html>