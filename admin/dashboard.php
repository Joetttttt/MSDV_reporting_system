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
      <li><a class="nav-link" href="student-records.php">Student Records</a></li