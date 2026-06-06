<?php
session_start();
require_once '../db/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php'); exit;
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

define('DELETE_PASSWORD', 'delete123');

// NOTIFICATIONS
$unreadStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
$unreadStmt->execute([$_SESSION['user_id']]);
$unreadCount = (int)$unreadStmt->fetchColumn();
$notifStmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 20");
$notifStmt->execute([$_SESSION['user_id']]);
$notifList = $notifStmt->fetchAll();

// DELETE VIOLATION
if (isset($_POST['action']) && $_POST['action'] === 'delete_violation') {
    $vid  = (int)$_POST['violation_id'];
    $pass = trim($_POST['del_password']);
    if ($pass === DELETE_PASSWORD) {
        $pdo->prepare("DELETE FROM disciplinary_actions WHERE violation_id=?")->execute([$vid]);
        $pdo->prepare("DELETE FROM appeals WHERE violation_id=?")->execute([$vid]);
        $pdo->prepare("DELETE FROM violations WHERE id=?")->execute([$vid]);
        $_SESSION['msg'] = 'Violation deleted successfully.';
    } else {
        $_SESSION['err'] = 'Incorrect deletion password.';
    }
    header('Location: violation-records.php'); exit;
}

// SEARCH & FILTER
$search       = trim($_GET['search'] ?? '');
$filterCat    = $_GET['category'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$where  = "WHERE 1=1"; $params = [];
if ($search)       { $where .= " AND (s.full_name LIKE ? OR v.student_id LIKE ?)"; $params[]="%$search%"; $params[]="%$search%"; }
if ($filterCat)    { $where .= " AND v.category=?";  $params[]=$filterCat; }
if ($filterStatus) { $where .= " AND v.status=?";    $params[]=$filterStatus; }

$stmt = $pdo->prepare("
    SELECT v.*, s.full_name, c.name as course_name, d.name as dept_name,
           u.full_name as reporter_name, u.role as reporter_role,
           da.sanction, da.status as da_status
    FROM violations v
    JOIN students s ON v.student_id=s.student_id
    JOIN courses c ON s.course_id=c.id
    JOIN departments d ON s.department_id=d.id
    JOIN users u ON v.reporter_id=u.id
    LEFT JOIN disciplinary_actions da ON da.violation_id=v.id
    $where ORDER BY v.date_submitted DESC");
$stmt->execute($params);
$violations = $stmt->fetchAll();

// PRE-FETCH tallies
$tallyRows = $pdo->query("SELECT student_id, category, COUNT(*) as cnt FROM violations GROUP BY student_id, category")->fetchAll();
$tallies = [];
foreach($tallyRows as $t) $tallies[$t['student_id']][$t['category']] = (int)$t['cnt'];

$highlight = (int)($_GET['highlight'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Violation Records - MDSV Admin</title>
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
    <li><a class="nav-link active fw-bold text-primary" href="violation-records.php"><i class="bi bi-exclamation-triangle me-2"></i>Violation Records</a></li>
    <li><a class="nav-link text-dark" href="disciplinary-action.php"><i class="bi bi-shield-exclamation me-2"></i>Disciplinary Action</a></li>
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
    <li><a class="nav-link fw-bold text-primary" href="violation-records.php"><i class="bi bi-exclamation-triangle me-2"></i>Violation Records</a></li>
    <li><a class="nav-link" href="disciplinary-action.php"><i class="bi bi-shield-exclamation me-2"></i>Disciplinary Action</a></li>
    <li><a class="nav-link" href="risk-level.php"><i class="bi bi-bar-chart me-2"></i>Risk Level</a></li>
    <li><a class="nav-link" href="student-appeals.php"><i class="bi bi-chat-left-text me-2"></i>Student Appeals</a></li>
    <li><a class="nav-link" href="data-backup.php"><i class="bi bi-download me-2"></i>Data Backup</a></li>
    <li><a class="nav-link" href="user-management.php"><i class="bi bi-person-gear me-2"></i>User Management</a></li>
  </ul>
</div>

<main class="col-md-10 px-3 py-3">
  <h5 class="mb-3">Violation Records</h5>

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
    <div class="col-auto">
      <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    </div>
    <div class="col-auto">
      <a href="violation-records.php" class="btn btn-secondary btn-sm">Reset</a>
    </div>
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
    <?php foreach($violations as $v):
        $minorCount = $tallies[$v['student_id']]['minor'] ?? 0;
        $majorCount = $tallies[$v['student_id']]['major'] ?? 0;
        $rowClass   = ($highlight && $highlight == $v['id']) ? 'table-warning' : '';
    ?>
    <tr class="<?= $rowClass ?>" id="row-<?= $v['id'] ?>">
      <td><?= htmlspecialchars($v['student_id']) ?></td>
      <td><?= htmlspecialchars($v['full_name']) ?></td>
      <td>
        <span class="badge bg-<?= $v['category']==='minor'?'primary':'danger' ?>">
          <?= ucfirst($v['category']) ?>
        </span>
      </td>
      <td><?= htmlspecialchars($v['violation']) ?></td>
      <td><?= date('M d, Y', strtotime($v['date_submitted'])) ?></td>
      <td>
        <div class="d-flex gap-1">
          <?php if($v['category']==='minor'):
            for($i=1;$i<=5;$i++): ?>
            <div style="width:16px;height:16px;border:2px solid #0d6efd;
                        background:<?= $i<=$minorCount?'#0d6efd':'transparent' ?>;
                        border-radius:3px"></div>
          <?php endfor; else:
            for($i=1;$i<=3;$i++): ?>
            <div style="width:16px;height:16px;border:2px solid #dc3545;
                        background:<?= $i<=$majorCount?'#dc3545':'transparent' ?>;
                        border-radius:3px"></div>
          <?php endfor; endif; ?>
        </div>
      </td>
      <td>
        <span class="badge bg-<?= $v['status']==='completed'?'success':
            ($v['status']==='ongoing'?'warning':'secondary') ?>">
          <?= ucfirst($v['status']) ?>
        </span>
      </td>
      <td>
        <button class="btn btn-info btn-sm"
                onclick='openViolationView(<?= json_encode($v) ?>)'>
          <i class="bi bi-eye"></i> View
        </button>
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
</div></div>

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
        <button class="btn btn-danger btn-sm" id="vvDeleteBtn">
          <i class="bi bi-trash"></i> Delete
        </button>
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
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Enter deletion password to confirm.</p>
        <input type="password" name="del_password" class="form-control"
               placeholder="Deletion Password" required>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger">Delete</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
<?php if($highlight): ?>
document.addEventListener('DOMContentLoaded', function(){
    const row = document.getElementById('row-<?= $highlight ?>');
    if (row) row.scrollIntoView({behavior:'smooth', block:'center'});
});
<?php endif; ?>

function openViolationView(v) {
    const evidence = v.evidence_path
        ? `<img src="../${v.evidence_path}" class="img-fluid rounded" style="max-height:200px">`
        : '<span class="text-muted small">No evidence uploaded</span>';
    const face = v.face_capture_path
        ? `<img src="../${v.face_capture_path}" class="img-fluid rounded" style="max-height:150px">`
        : '<span class="text-muted small">No face capture</span>';
    const sig = v.signature_path
        ? `<img src="../${v.signature_path}" class="img-fluid" style="max-height:80px">`
        : '<span class="text-muted small">No signature</span>';

    document.getElementById('vvBody').innerHTML = `
      <div class="row">
        <div class="col-md-6">
          <h6 class="fw-bold">Student Info</h6>
          <table class="table table-sm table-bordered mb-3">
            <tr><th>Student ID</th><td>${v.student_id}</td></tr>
            <tr><th>Full Name</th><td>${v.full_name}</td></tr>
          </table>
          <h6 class="fw-bold">Violation Info</h6>
          <table class="table table-sm table-bordered mb-3">
            <tr><th>Category</th>
                <td><span class="badge bg-${v.category==='minor'?'primary':'danger'}">${v.category}</span></td></tr>
            <tr><th>Violation</th><td>${v.violation}</td></tr>
            <tr><th>Description</th><td>${v.description ?? '-'}</td></tr>
            <tr><th>Status</th>
                <td><span class="badge bg-${v.status==='completed'?'success':v.status==='ongoing'?'warning':'secondary'}">${v.status}</span></td></tr>
            <tr><th>Date Submitted</th><td>${v.date_submitted}</td></tr>
            <tr><th>Sanction</th><td>${v.sanction ?? '-'}</td></tr>
          </table>
          <h6 class="fw-bold">Reporter Info</h6>
          <table class="table table-sm table-bordered">
            <tr><th>Name</th><td>${v.reporter_name}</td></tr>
            <tr><th>Role</th><td>${v.reporter_role}</td></tr>
          </table>
        </div>
        <div class="col-md-6">
          <h6 class="fw-bold">Evidence</h6>
          <div class="mb-3">${evidence}</div>
          <h6 class="fw-bold">Face Capture</h6>
          <div class="mb-3">${face}</div>
          <h6 class="fw-bold">E-Signature</h6>
          <div>${sig}</div>
        </div>
      </div>`;

    document.getElementById('vvDeleteBtn').onclick = function() {
        bootstrap.Modal.getInstance(document.getElementById('viewViolationModal'))?.hide();
        document.getElementById('delViolID').value = v.id;
        setTimeout(() => new bootstrap.Modal(document.getElementById('delViolModal')).show(), 400);
    };
    new bootstrap.Modal(document.getElementById('viewViolationModal')).show();
}

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