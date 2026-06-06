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

// APPROVE / REJECT
if (isset($_POST['action']) && in_array($_POST['action'],['approve','reject'])) {
    $appealId = (int)$_POST['appeal_id'];
    $status   = $_POST['action']==='approve' ? 'approved' : 'rejected';
    $ap = $pdo->prepare("SELECT * FROM appeals WHERE id=?");
    $ap->execute([$appealId]);
    $appeal = $ap->fetch();
    if ($appeal) {
        $pdo->prepare("UPDATE appeals SET status=? WHERE id=?")->execute([$status,$appealId]);
        if ($status==='approved') {
            $pdo->prepare("DELETE FROM disciplinary_actions WHERE violation_id=?")->execute([$appeal['violation_id']]);
            $pdo->prepare("DELETE FROM violations WHERE id=?")->execute([$appeal['violation_id']]);
            $notifMsg = 'Your appeal has been APPROVED. The violation has been removed from your record.';
        } else {
            $notifMsg = 'Your appeal has been REJECTED. The violation remains on your record.';
        }
        $stuUser = $pdo->prepare("SELECT u.id FROM students s JOIN users u ON s.user_id=u.id WHERE s.student_id=?");
        $stuUser->execute([$appeal['student_id']]);
        $stuRow = $stuUser->fetch();
        if ($stuRow) {
            $pdo->prepare("INSERT INTO notifications (user_id,message,link) VALUES (?,?,?)")
                ->execute([$stuRow['id'],$notifMsg,'../student/dashboard.php']);
        }
        $_SESSION['msg'] = 'Appeal '.ucfirst($status).'.';
    }
    header('Location: student-appeals.php'); exit;
}

$appeals = $pdo->query("
    SELECT a.*, s.full_name, v.violation, v.category, v.date_submitted as vdate
    FROM appeals a
    JOIN students s ON a.student_id=s.student_id
    JOIN violations v ON a.violation_id=v.id
    ORDER BY a.submitted_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Appeals - MDSV Admin</title>
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
    <li><a class="nav-link active fw-bold text-primary" href="student-appeals.php"><i class="bi bi-chat-left-text me-2"></i>Student Appeals</a></li>
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
    <li><a class="nav-link" href="risk-level.php"><i class="bi bi-bar-chart me-2"></i>Risk Level</a></li>
    <li><a class="nav-link fw-bold text-primary" href="student-appeals.php"><i class="bi bi-chat-left-text me-2"></i>Student Appeals</a></li>
    <li><a class="nav-link" href="data-backup.php"><i class="bi bi-download me-2"></i>Data Backup</a></li>
    <li><a class="nav-link" href="user-management.php"><i class="bi bi-person-gear me-2"></i>User Management</a></li>
  </ul>
</div>

<main class="col-md-10 px-3 py-3">
  <h5 class="mb-3">Student Appeals</h5>

  <?php if(isset($_SESSION['msg'])): ?>
  <div class="alert alert-success"><?= $_SESSION['msg'] ?><?php unset($_SESSION['msg']); ?></div>
  <?php endif; ?>

  <div class="table-responsive">
  <table class="table table-bordered table-sm table-hover">
    <thead class="table-dark">
      <tr>
        <th>Student ID</th><th>Full Name</th><th>Violation</th>
        <th>Explanation</th><th>Submitted</th><th>Status</th><th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach($appeals as $ap): ?>
    <tr>
      <td><?= htmlspecialchars($ap['student_id']) ?></td>
      <td><?= htmlspecialchars($ap['full_name']) ?></td>
      <td>
        <span class="badge bg-<?= $ap['category']==='minor'?'primary':'danger' ?>">
          <?= ucfirst($ap['category']) ?>
        </span>
        <?= htmlspecialchars($ap['violation']) ?>
      </td>
      <td><small><?= htmlspecialchars(substr($ap['explanation'],0,60)) ?>...</small></td>
      <td><?= date('M d, Y', strtotime($ap['submitted_at'])) ?></td>
      <td>
        <span class="badge bg-<?= $ap['status']==='approved'?'success':
            ($ap['status']==='rejected'?'danger':'secondary') ?>">
          <?= ucfirst($ap['status']) ?>
        </span>
      </td>
      <td class="d-flex gap-1 flex-wrap">
        <?php if($ap['status']==='pending'): ?>
        <form method="POST" class="d-inline">
          <input type="hidden" name="appeal_id" value="<?= $ap['id'] ?>">
          <button name="action" value="approve" class="btn btn-success btn-sm"
                  onclick="return confirm('Approve this appeal? The violation will be removed.')">
            <i class="bi bi-check-circle"></i> Approve
          </button>
          <button name="action" value="reject" class="btn btn-danger btn-sm"
                  onclick="return confirm('Reject this appeal?')">
            <i class="bi bi-x-circle"></i> Reject
          </button>
        </form>
        <?php else: ?>
        <span class="text-muted small">Resolved</span>
        <?php endif; ?>
        <button class="btn btn-info btn-sm"
                onclick='viewAppeal(<?= json_encode($ap) ?>)'>
          <i class="bi bi-eye"></i> View
        </button>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($appeals)): ?>
    <tr><td colspan="7" class="text-center text-muted">No appeals yet.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
</main>
</div></div>

<!-- VIEW APPEAL MODAL -->
<div class="modal fade" id="viewAppealModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Appeal Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="vaBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function viewAppeal(ap) {
    document.getElementById('vaBody').innerHTML = `
        <table class="table table-sm table-bordered">
          <tr><th>Student ID</th><td>${ap.student_id}</td></tr>
          <tr><th>Full Name</th><td>${ap.full_name}</td></tr>
          <tr><th>Violation</th><td>${ap.violation}</td></tr>
          <tr><th>Category</th><td>${ap.category}</td></tr>
          <tr><th>Violation Date</th><td>${ap.vdate}</td></tr>
          <tr><th>Appeal Submitted</th><td>${ap.submitted_at}</td></tr>
          <tr><th>Status</th><td>${ap.status}</td></tr>
          <tr><th>Explanation</th><td>${ap.explanation}</td></tr>
        </table>`;
    new bootstrap.Modal(document.getElementById('viewAppealModal')).show();
}
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