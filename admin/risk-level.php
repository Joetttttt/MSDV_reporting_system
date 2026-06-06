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

$allStudents = $pdo->query("
    SELECT s.student_id, s.full_name,
           SUM(CASE WHEN v.category='minor' THEN 1 ELSE 0 END) as minor_count,
           SUM(CASE WHEN v.category='major' THEN 1 ELSE 0 END) as major_count,
           COUNT(v.id) as total
    FROM students s
    LEFT JOIN violations v ON s.student_id=v.student_id
    GROUP BY s.student_id
")->fetchAll();

function getRisk($total) {
    if ($total >= 5) return ['label'=>'Critical','class'=>'danger'];
    if ($total >= 4) return ['label'=>'High',    'class'=>'warning'];
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

$search     = trim($_GET['search'] ?? '');
$filterRisk = $_GET['risk'] ?? '';
$filtered   = array_filter($allStudents, function($s) use ($search,$filterRisk) {
    $r = getRisk($s['total']);
    $ms = !$search || stripos($s['full_name'],$search)!==false || stripos($s['student_id'],$search)!==false;
    $mr = !$filterRisk || $r['label']===$filterRisk;
    return $ms && $mr;
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Risk Level - MDSV Admin</title>
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
    <li><a class="nav-link active fw-bold text-primary" href="risk-level.php"><i class="bi bi-bar-chart me-2"></i>Risk Level</a></li>
    <li><a class="nav-link text-dark" href="student-appeals.php"><i class="bi bi-chat-left-text me-2"></i>Student Appeals</a></li>
    <li><a class="nav-link text-dark" href="data-backup.php"><i class="bi bi-download me-2"></i>Data Backup</a></li>
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
    <li><a class="nav-link fw-bold text-primary" href="risk-level.php"><i class="bi bi-bar-chart me-2"></i>Risk Level</a></li>
    <li><a class="nav-link" href="student-appeals.php"><i class="bi bi-chat-left-text me-2"></i>Student Appeals</a></li>
    <li><a class="nav-link" href="data-backup.php"><i class="bi bi-download me-2"></i>Data Backup</a></li>
    <li><a class="nav-link" href="user-management.php"><i class="bi bi-person-gear me-2"></i>User Management</a></li>
  </ul>
</div>

<main class="col-md-10 px-3 py-3">
  <h5 class="mb-3">Risk Level Indicator</h5>

  <div class="row g-3 mb-4">
    <div class="col-4">
      <div class="card text-white bg-info h-100">
        <div class="card-body text-center">
          <div class="small fw-bold">Moderate Risk</div>
          <h3 class="mb-0"><?= $moderate ?></h3>
          <small>2+ violations</small>
        </div>
      </div>
    </div>
    <div class="col-4">
      <div class="card text-white bg-warning h-100">
        <div class="card-body text-center">
          <div class="small fw-bold">High Risk</div>
          <h3 class="mb-0"><?= $high ?></h3>
          <small>4+ violations</small>
        </div>
      </div>
    </div>
    <div class="col-4">
      <div class="card text-white bg-danger h-100">
        <div class="card-body text-center">
          <div class="small fw-bold">Critical</div>
          <h3 class="mb-0"><?= $critical ?></h3>
          <small>5+ violations</small>
        </div>
      </div>
    </div>
  </div>

  <form method="GET" class="row g-2 mb-3">
    <div class="col-md-4 col-7">
      <input type="text" name="search" class="form-control form-control-sm"
             placeholder="Search name or ID" value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-2 col-5">
      <select name="risk" class="form-select form-select-sm">
        <option value="">All Risk</option>
        <option value="Low"      <?=$filterRisk==='Low'     ?'selected':''?>>Low</option>
        <option value="Moderate" <?=$filterRisk==='Moderate'?'selected':''?>>Moderate</option>
        <option value="High"     <?=$filterRisk==='High'    ?'selected':''?>>High</option>
        <option value="Critical" <?=$filterRisk==='Critical'?'selected':''?>>Critical</option>
      </select>
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    </div>
    <div class="col-auto">
      <a href="risk-level.php" class="btn btn-secondary btn-sm">Reset</a>
    </div>
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
        $risk = getRisk($s['total']); ?>
    <tr>
      <td><?= htmlspecialchars($s['student_id']) ?></td>
      <td><?= htmlspecialchars($s['full_name']) ?></td>
      <td>
        <div class="d-flex gap-1">
          <?php for($i=1;$i<=5;$i++): ?>
          <div style="width:16px;height:16px;border:2px solid #0d6efd;
                      background:<?= $i<=$s['minor_count']?'#0d6efd':'transparent' ?>;
                      border-radius:3px"></div>
          <?php endfor; ?>
        </div>
      </td>
      <td>
        <div class="d-flex gap-1">
          <?php for($i=1;$i<=3;$i++): ?>
          <div style="width:16px;height:16px;border:2px solid #dc3545;
                      background:<?= $i<=$s['major_count']?'#dc3545':'transparent' ?>;
                      border-radius:3px"></div>
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
<script>
function markRead(id, el) {
    fetch('ajax/notifications.php?action=read&id=' + id);
    el.classList.remove('bg-light','fw-semibold');
    const badge = document.getElementById('notifBadge');
    if (badge) { const n = parseInt(badge.textContent)-1; if(n<=0) badge.remove(); else badge.textContent=n; }
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