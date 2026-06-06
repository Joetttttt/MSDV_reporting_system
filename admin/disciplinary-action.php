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

// UPDATE STATUS
if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $daId      = (int)$_POST['da_id'];
    $newStatus = $_POST['new_status'];
    $flow      = ['pending'=>0,'ongoing'=>1,'completed'=>2];

    $curr = $pdo->prepare("SELECT * FROM disciplinary_actions WHERE id=?");
    $curr->execute([$daId]);
    $da = $curr->fetch();

    if ($da) {
        if (($flow[$newStatus] ?? -1) <= ($flow[$da['status']] ?? 0)) {
            $_SESSION['err'] = 'Cannot move status backwards.';
        } else {
            $startDate = $da['start_date'];
            $endDate   = $da['end_date'];
            if ($newStatus === 'ongoing'   && !$startDate) $startDate = date('Y-m-d');
            if ($newStatus === 'completed' && !$endDate)   $endDate   = date('Y-m-d');

            $pdo->prepare("UPDATE disciplinary_actions SET status=?,start_date=?,end_date=? WHERE id=?")
                ->execute([$newStatus,$startDate,$endDate,$daId]);
            $pdo->prepare("UPDATE violations SET status=? WHERE id=?")
                ->execute([$newStatus,$da['violation_id']]);

            // Notify student
            $stuUser = $pdo->prepare("SELECT u.id FROM students s JOIN users u ON s.user_id=u.id WHERE s.student_id=?");
            $stuUser->execute([$da['student_id']]);
            $stuRow = $stuUser->fetch();
            if ($stuRow) {
                $pdo->prepare("INSERT INTO notifications (user_id,message,link) VALUES (?,?,?)")
                    ->execute([$stuRow['id'],
                        "Your disciplinary case status has been updated to: ".strtoupper($newStatus),
                        '../student/dashboard.php']);
            }
            $_SESSION['msg'] = 'Status updated to '.ucfirst($newStatus).'.';
        }
    }
    header('Location: disciplinary-action.php'); exit;
}

// SEARCH & FILTER
$search       = trim($_GET['search'] ?? '');
$filterStatus = $_GET['status'] ?? '';
$where  = "WHERE 1=1"; $params = [];
if ($search)       { $where .= " AND (s.full_name LIKE ? OR da.student_id LIKE ?)"; $params[]="%$search%"; $params[]="%$search%"; }
if ($filterStatus) { $where .= " AND da.status=?"; $params[]=$filterStatus; }

$stmt = $pdo->prepare("
    SELECT da.*, s.full_name, v.violation, v.category
    FROM disciplinary_actions da
    JOIN students s ON da.student_id=s.student_id
    JOIN violations v ON da.violation_id=v.id
    $where ORDER BY da.id DESC");
$stmt->execute($params);
$daList = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Disciplinary Action - MDSV Admin</title>
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
    <li><a class="nav-link active fw-bold text-primary" href="disciplinary-action.php"><i class="bi bi-shield-exclamation me-2"></i>Disciplinary Action</a></li>
    <li><a class="nav-link text-dark" href="risk-level.php"><i class="bi bi-bar-chart me-2"></i>Risk Level</a></li>
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
    <li><a class="nav-link fw-bold text-primary" href="disciplinary-action.php"><i class="bi bi-shield-exclamation me-2"></i>Disciplinary Action</a></li>
    <li><a class="nav-link" href="risk-level.php"><i class="bi bi-bar-chart me-2"></i>Risk Level</a></li>
    <li><a class="nav-link" href="student-appeals.php"><i class="bi bi-chat-left-text me-2"></i>Student Appeals</a></li>
    <li><a class="nav-link" href="data-backup.php"><i class="bi bi-download me-2"></i>Data Backup</a></li>
    <li><a class="nav-link" href="user-management.php"><i class="bi bi-person-gear me-2"></i>User Management</a></li>
  </ul>
</div>

<main class="col-md-10 px-3 py-3">
  <h5 class="mb-3">Disciplinary Actions</h5>

  <?php if(isset($_SESSION['msg'])): ?>
  <div class="alert alert-success"><?= $_SESSION['msg'] ?><?php unset($_SESSION['msg']); ?></div>
  <?php endif; ?>
  <?php if(isset($_SESSION['err'])): ?>
  <div class="alert alert-danger"><?= $_SESSION['err'] ?><?php unset($_SESSION['err']); ?></div>
  <?php endif; ?>

  <form method="GET" class="row g-2 mb-3">
    <div class="col-md-4 col-7">
      <input type="text" name="search" class="form-control form-control-sm"
             placeholder="Search name or ID" value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-2 col-5">
      <select name="status" class="form-select form-select-sm">
        <option value="">All Status</option>
        <option value="pending"   <?=$filterStatus==='pending'  ?'selected':''?>>Pending</option>
        <option value="ongoing"   <?=$filterStatus==='ongoing'  ?'selected':''?>>Ongoing</option>
        <option value="completed" <?=$filterStatus==='completed'?'selected':''?>>Completed</option>
      </select>
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    </div>
    <div class="col-auto">
      <a href="disciplinary-action.php" class="btn btn-secondary btn-sm">Reset</a>
    </div>
  </form>

  <div class="table-responsive">
  <table class="table table-bordered table-sm table-hover">
    <thead class="table-dark">
      <tr>
        <th>Student ID</th><th>Full Name</th><th>Violation</th>
        <th>Sanction</th><th>Status</th><th>Start Date</th><th>End Date</th><th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach($daList as $da): ?>
    <tr>
      <td><?= htmlspecialchars($da['student_id']) ?></td>
      <td><?= htmlspecialchars($da['full_name']) ?></td>
      <td>
        <span class="badge bg-<?= $da['category']==='minor'?'primary':'danger' ?>">
          <?= ucfirst($da['category']) ?>
        </span>
        <?= htmlspecialchars($da['violation']) ?>
      </td>
      <td><small><?= htmlspecialchars($da['sanction']) ?></small></td>
      <td>
        <span class="badge bg-<?= $da['status']==='completed'?'success':
            ($da['status']==='ongoing'?'warning':'secondary') ?>">
          <?= ucfirst($da['status']) ?>
        </span>
      </td>
      <td><?= $da['start_date'] ?? '-' ?></td>
      <td><?= $da['end_date']   ?? '-' ?></td>
      <td>
        <?php if($da['status'] !== 'completed'): ?>
        <button class="btn btn-sm btn-primary"
                onclick='openUpdateStatus(<?= json_encode($da) ?>)'>
          <i class="bi bi-arrow-repeat"></i> Update
        </button>
        <?php else: ?>
        <span class="text-muted small"><i class="bi bi-check-all"></i> Done</span>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($daList)): ?>
    <tr><td colspan="8" class="text-center text-muted">No records found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
</main>
</div></div>

<!-- UPDATE STATUS MODAL -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <input type="hidden" name="action" value="update_status">
      <input type="hidden" name="da_id" id="daID">
      <div class="modal-header">
        <h5 class="modal-title">Update Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <table class="table table-sm mb-3">
          <tr><th>Student</th><td id="daStudentName"></td></tr>
          <tr><th>Violation</th><td id="daViolation"></td></tr>
          <tr><th>Sanction</th><td><small id="daSanction"></small></td></tr>
          <tr><th>Current Status</th><td id="daCurrentStatus"></td></tr>
        </table>
        <label class="form-label">New Status</label>
        <select name="new_status" id="daNewStatus" class="form-select"></select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Update</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openUpdateStatus(da) {
    document.getElementById('daID').value = da.id;
    document.getElementById('daStudentName').textContent = da.full_name;
    document.getElementById('daViolation').textContent   = da.violation;
    document.getElementById('daSanction').textContent    = da.sanction;
    document.getElementById('daCurrentStatus').textContent = da.status;
    const flow = {pending:0, ongoing:1, completed:2};
    const curr = flow[da.status] ?? 0;
    const sel  = document.getElementById('daNewStatus');
    sel.innerHTML = '';
    Object.entries(flow).forEach(([s,v]) => {
        if (v > curr) sel.innerHTML +=
            `<option value="${s}">${s.charAt(0).toUpperCase()+s.slice(1)}</option>`;
    });
    new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
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