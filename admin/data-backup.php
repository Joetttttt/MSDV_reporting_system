<?php
session_start();
require_once '../db/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php'); exit;
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// NOTIFICATIONS
$unreadStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
$unreadStmt->execute([$_SESSION['user_id']]);
$unreadCount = (int)$unreadStmt->fetchColumn();
$notifStmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 20");
$notifStmt->execute([$_SESSION['user_id']]);
$notifList = $notifStmt->fetchAll();

// EXPORT HANDLER
if (isset($_GET['export'])) {
    $type     = $_GET['export'];
    $filename = $type . '_export_' . date('Ymd_His') . '.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');

    if ($type === 'students') {
        fputcsv($out, ['Student ID','Full Name','Course','Year Level','Department']);
        $rows = $pdo->query("
            SELECT s.student_id, s.full_name, c.name, s.year_level, d.name
            FROM students s
            JOIN courses c ON s.course_id=c.id
            JOIN departments d ON s.department_id=d.id
            ORDER BY s.student_id")->fetchAll();
        foreach ($rows as $r) fputcsv($out, $r);

    } elseif ($type === 'violations') {
        fputcsv($out, ['ID','Student ID','Full Name','Category','Violation','Description','Status','Date Submitted']);
        $rows = $pdo->query("
            SELECT v.id, v.student_id, s.full_name, v.category,
                   v.violation, v.description, v.status, v.date_submitted
            FROM violations v
            JOIN students s ON v.student_id=s.student_id
            ORDER BY v.date_submitted DESC")->fetchAll();
        foreach ($rows as $r) fputcsv($out, $r);

    } elseif ($type === 'disciplinary') {
        fputcsv($out, ['ID','Student ID','Full Name','Violation','Sanction','Status','Start Date','End Date']);
        $rows = $pdo->query("
            SELECT da.id, da.student_id, s.full_name, v.violation,
                   da.sanction, da.status, da.start_date, da.end_date
            FROM disciplinary_actions da
            JOIN students s ON da.student_id=s.student_id
            JOIN violations v ON da.violation_id=v.id
            ORDER BY da.id DESC")->fetchAll();
        foreach ($rows as $r) fputcsv($out, $r);

    } elseif ($type === 'risk') {
        fputcsv($out, ['Student ID','Full Name','Minor Count','Major Count','Total','Risk Level']);
        $rows = $pdo->query("
            SELECT s.student_id, s.full_name,
                   SUM(CASE WHEN v.category='minor' THEN 1 ELSE 0 END) AS minor_c,
                   SUM(CASE WHEN v.category='major' THEN 1 ELSE 0 END) AS major_c,
                   COUNT(v.id) AS total
            FROM students s
            LEFT JOIN violations v ON s.student_id=v.student_id
            GROUP BY s.student_id
            ORDER BY total DESC")->fetchAll();
        foreach ($rows as $r) {
            $t    = (int)$r['total'];
            $risk = $t>=5 ? 'Critical' : ($t>=4 ? 'High' : ($t>=2 ? 'Moderate' : 'Low'));
            fputcsv($out, [$r['student_id'],$r['full_name'],$r['minor_c'],$r['major_c'],$t,$risk]);
        }

    } elseif ($type === 'all') {
        // STUDENTS
        fputcsv($out, ['=== STUDENTS ===']);
        fputcsv($out, ['Student ID','Full Name','Course','Year Level','Department']);
        $rows = $pdo->query("
            SELECT s.student_id, s.full_name, c.name, s.year_level, d.name
            FROM students s
            JOIN courses c ON s.course_id=c.id
            JOIN departments d ON s.department_id=d.id")->fetchAll();
        foreach ($rows as $r) fputcsv($out, $r);
        fputcsv($out, []);

        // VIOLATIONS
        fputcsv($out, ['=== VIOLATIONS ===']);
        fputcsv($out, ['ID','Student ID','Full Name','Category','Violation','Description','Status','Date']);
        $rows = $pdo->query("
            SELECT v.id, v.student_id, s.full_name, v.category,
                   v.violation, v.description, v.status, v.date_submitted
            FROM violations v JOIN students s ON v.student_id=s.student_id")->fetchAll();
        foreach ($rows as $r) fputcsv($out, $r);
        fputcsv($out, []);

        // DISCIPLINARY
        fputcsv($out, ['=== DISCIPLINARY ACTIONS ===']);
        fputcsv($out, ['ID','Student ID','Violation','Sanction','Status','Start Date','End Date']);
        $rows = $pdo->query("
            SELECT da.id, da.student_id, v.violation,
                   da.sanction, da.status, da.start_date, da.end_date
            FROM disciplinary_actions da
            JOIN violations v ON da.violation_id=v.id")->fetchAll();
        foreach ($rows as $r) fputcsv($out, $r);
        fputcsv($out, []);

        // RISK
        fputcsv($out, ['=== RISK LEVELS ===']);
        fputcsv($out, ['Student ID','Full Name','Minor','Major','Total','Risk Level']);
        $rows = $pdo->query("
            SELECT s.student_id, s.full_name,
                   SUM(CASE WHEN v.category='minor' THEN 1 ELSE 0 END) AS mc,
                   SUM(CASE WHEN v.category='major' THEN 1 ELSE 0 END) AS mj,
                   COUNT(v.id) AS total
            FROM students s LEFT JOIN violations v ON s.student_id=v.student_id
            GROUP BY s.student_id")->fetchAll();
        foreach ($rows as $r) {
            $t    = (int)$r['total'];
            $risk = $t>=5 ? 'Critical' : ($t>=4 ? 'High' : ($t>=2 ? 'Moderate' : 'Low'));
            fputcsv($out, [$r['student_id'],$r['full_name'],$r['mc'],$r['mj'],$t,$risk]);
        }
    }

    fclose($out);
    exit;
}

// COUNTS for display
$studentCount     = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$violationCount   = $pdo->query("SELECT COUNT(*) FROM violations")->fetchColumn();
$disciplinaryCount= $pdo->query("SELECT COUNT(*) FROM disciplinary_actions")->fetchColumn();

// PREVIEW DATA
$previewStudents = $pdo->query("
    SELECT s.student_id, s.full_name, c.name as cname, s.year_level, d.name as dname
    FROM students s
    JOIN courses c ON s.course_id=c.id
    JOIN departments d ON s.department_id=d.id
    ORDER BY s.student_id LIMIT 8")->fetchAll();

$previewViolations = $pdo->query("
    SELECT v.student_id, s.full_name, v.category, v.violation, v.status,
           v.date_submitted
    FROM violations v
    JOIN students s ON v.student_id=s.student_id
    ORDER BY v.date_submitted DESC LIMIT 8")->fetchAll();

$previewDisciplinary = $pdo->query("
    SELECT da.student_id, s.full_name, v.violation, da.sanction, da.status
    FROM disciplinary_actions da
    JOIN students s ON da.student_id=s.student_id
    JOIN violations v ON da.violation_id=v.id
    ORDER BY da.id DESC LIMIT 8")->fetchAll();

$previewRisk = $pdo->query("
    SELECT s.student_id, s.full_name,
           SUM(CASE WHEN v.category='minor' THEN 1 ELSE 0 END) AS minor_c,
           SUM(CASE WHEN v.category='major' THEN 1 ELSE 0 END) AS major_c,
           COUNT(v.id) AS total
    FROM students s
    LEFT JOIN violations v ON s.student_id=v.student_id
    GROUP BY s.student_id
    ORDER BY total DESC LIMIT 8")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Backup - MDSV Admin</title>
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

<div class="container-fluid"><div class="row">
<!-- SIDEBAR -->
<nav class="col-md-2 d-none d-md-block bg-light border-end py-3"
     style="min-height:calc(100vh - 56px)">
  <ul class="nav flex-column">
    <li><a class="nav-link text-dark" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
    <li><a class="nav-link text-dark" href="student-records.php"><i class="bi bi-people me-2"></i>Student Records</a></li>
    <li><a class="nav-link text-dark" href="violation-records.php"><i class="bi bi-exclamation-triangle me-2"></i>Violation Records</a></li>
    <li><a class="nav-link text-dark" href="disciplinary-action.php"><i class="bi bi-shield-exclamation me-2"></i>Disciplinary Action</a></li>
    <li><a class="nav-link text-dark" href="risk-level.php"><i class="bi bi-bar-chart me-2"></i>Risk Level</a></li>
    <li><a class="nav-link text-dark" href="student-appeals.php"><i class="bi bi-chat-left-text me-2"></i>Student Appeals</a></li>
    <li><a class="nav-link active fw-bold text-primary" href="data-backup.php"><i class="bi bi-download me-2"></i>Data Backup</a></li>
    <li><a class="nav-link text-dark" href="user-management.php"><i class="bi bi-person-gear me-2"></i>User Management</a></li>
  </ul>
</nav>
<div class="collapse d-md-none position-fixed w-100 bg-white border-bottom shadow"
     id="sidebarMenu" style="z-index:1045;top:56px">
  <ul class="nav flex-column p-2">
    <li><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
    <li><a class="nav-link" href="student-records.php"><i class="bi bi-people me-2"></i>Student Records</a></li>
    <li><a class="nav-link" href="violation-records.php"><i class="bi bi-exclamation-triangle me-2"></i>Violation Records</a></li>
    <li><a class="nav-link" href="disciplinary-action.php"><i class="bi bi-shield-exclamation me-2"></i>Disciplinary Action</a></li>
    <li><a class="nav-link" href="risk-level.php"><i class="bi bi-bar-chart me-2"></i>Risk Level</a></li>
    <li><a class="nav-link" href="student-appeals.php"><i class="bi bi-chat-left-text me-2"></i>Student Appeals</a></li>
    <li><a class="nav-link fw-bold text-primary" href="data-backup.php"><i class="bi bi-download me-2"></i>Data Backup</a></li>
    <li><a class="nav-link" href="user-management.php"><i class="bi bi-person-gear me-2"></i>User Management</a></li>
  </ul>
</div>

<main class="col-md-10 px-3 py-3">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="mb-0">Data Backup &amp; Export</h5>
    <a href="?export=all" class="btn btn-success">
      <i class="bi bi-download"></i> Export All Data
    </a>
  </div>

  <!-- STUDENTS -->
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>
        <i class="bi bi-people me-1"></i>
        Student Records
        <span class="badge bg-secondary ms-1"><?= $studentCount ?></span>
      </strong>
      <a href="?export=students" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-download"></i> Export CSV
      </a>
    </div>
    <div class="table-responsive">
    <table class="table table-sm table-bordered mb-0">
      <thead class="table-dark">
        <tr><th>Student ID</th><th>Full Name</th><th>Course</th><th>Year</th><th>Department</th></tr>
      </thead>
      <tbody>
      <?php foreach($previewStudents as $s): ?>
      <tr>
        <td><?= htmlspecialchars($s['student_id']) ?></td>
        <td><?= htmlspecialchars($s['full_name']) ?></td>
        <td><?= htmlspecialchars($s['cname']) ?></td>
        <td>Year <?= $s['year_level'] ?></td>
        <td><?= htmlspecialchars($s['dname']) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($previewStudents)): ?>
      <tr><td colspan="5" class="text-center text-muted">No students yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
    </div>
    <?php if($studentCount > 8): ?>
    <div class="card-footer text-muted small">
      Showing 8 of <?= $studentCount ?> records. Export to see all.
    </div>
    <?php endif; ?>
  </div>

  <!-- VIOLATIONS -->
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>
        <i class="bi bi-exclamation-triangle me-1"></i>
        Violation Records
        <span class="badge bg-secondary ms-1"><?= $violationCount ?></span>
      </strong>
      <a href="?export=violations" class="btn btn-sm btn-outline-danger">
        <i class="bi bi-download"></i> Export CSV
      </a>
    </div>
    <div class="table-responsive">
    <table class="table table-sm table-bordered mb-0">
      <thead class="table-dark">
        <tr><th>Student ID</th><th>Name</th><th>Type</th><th>Violation</th><th>Status</th><th>Date</th></tr>
      </thead>
      <tbody>
      <?php foreach($previewViolations as $v): ?>
      <tr>
        <td><?= htmlspecialchars($v['student_id']) ?></td>
        <td><?= htmlspecialchars($v['full_name']) ?></td>
        <td>
          <span class="badge bg-<?= $v['category']==='minor'?'primary':'danger' ?>">
            <?= ucfirst($v['category']) ?>
          </span>
        </td>
        <td><?= htmlspecialchars($v['violation']) ?></td>
        <td>
          <span class="badge bg-<?= $v['status']==='completed'?'success':
              ($v['status']==='ongoing'?'warning':'secondary') ?>">
            <?= ucfirst($v['status']) ?>
          </span>
        </td>
        <td><?= date('M d, Y', strtotime($v['date_submitted'])) ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($previewViolations)): ?>
      <tr><td colspan="6" class="text-center text-muted">No violations yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
    </div>
    <?php if($violationCount > 8): ?>
    <div class="card-footer text-muted small">
      Showing 8 of <?= $violationCount ?> records. Export to see all.
    </div>
    <?php endif; ?>
  </div>

  <!-- DISCIPLINARY -->
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>
        <i class="bi bi-shield-exclamation me-1"></i>
        Disciplinary Actions
        <span class="badge bg-secondary ms-1"><?= $disciplinaryCount ?></span>
      </strong>
      <a href="?export=disciplinary" class="btn btn-sm btn-outline-warning">
        <i class="bi bi-download"></i> Export CSV
      </a>
    </div>
    <div class="table-responsive">
    <table class="table table-sm table-bordered mb-0">
      <thead class="table-dark">
        <tr><th>Student ID</th><th>Name</th><th>Violation</th><th>Sanction</th><th>Status</th></tr>
      </thead>
      <tbody>
      <?php foreach($previewDisciplinary as $da): ?>
      <tr>
        <td><?= htmlspecialchars($da['student_id']) ?></td>
        <td><?= htmlspecialchars($da['full_name']) ?></td>
        <td><?= htmlspecialchars($da['violation']) ?></td>
        <td><small><?= htmlspecialchars(substr($da['sanction'],0,60)) ?>...</small></td>
        <td>
          <span class="badge bg-<?= $da['status']==='completed'?'success':
              ($da['status']==='ongoing'?'warning':'secondary') ?>">
            <?= ucfirst($da['status']) ?>
          </span>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($previewDisciplinary)): ?>
      <tr><td colspan="5" class="text-center text-muted">No disciplinary records yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
    </div>
    <?php if($disciplinaryCount > 8): ?>
    <div class="card-footer text-muted small">
      Showing 8 of <?= $disciplinaryCount ?> records. Export to see all.
    </div>
    <?php endif; ?>
  </div>

  <!-- RISK LEVEL -->
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>
        <i class="bi bi-bar-chart me-1"></i>
        Risk Level Data
      </strong>
      <a href="?export=risk" class="btn btn-sm btn-outline-info">
        <i class="bi bi-download"></i> Export CSV
      </a>
    </div>
    <div class="table-responsive">
    <table class="table table-sm table-bordered mb-0">
      <thead class="table-dark">
        <tr><th>Student ID</th><th>Full Name</th><th>Minor</th><th>Major</th><th>Total</th><th>Risk Level</th></tr>
      </thead>
      <tbody>
      <?php foreach($previewRisk as $r):
          $t    = (int)$r['total'];
          $risk = $t>=5?['Critical','danger']:($t>=4?['High','warning']:($t>=2?['Moderate','info']:['Low','success']));
      ?>
      <tr>
        <td><?= htmlspecialchars($r['student_id']) ?></td>
        <td><?= htmlspecialchars($r['full_name']) ?></td>
        <td><?= $r['minor_c'] ?></td>
        <td><?= $r['major_c'] ?></td>
        <td><?= $t ?></td>
        <td><span class="badge bg-<?= $risk[1] ?>"><?= $risk[0] ?></span></td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($previewRisk)): ?>
      <tr><td colspan="6" class="text-center text-muted">No data yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>

</main>
</div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
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