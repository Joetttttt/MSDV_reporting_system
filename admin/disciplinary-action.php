<?php
session_start();
require_once '../db/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php'); exit;
}

// UPDATE STATUS
if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $daId      = (int)$_POST['da_id'];
    $newStatus = $_POST['new_status'];

    $curr = $pdo->prepare("SELECT * FROM disciplinary_actions WHERE id=?");
    $curr->execute([$daId]);
    $da = $curr->fetch();

    if ($da) {
        $allowed = true;
        // Status flow: pending -> ongoing -> completed (no going back)
        $flow = ['pending'=>0,'ongoing'=>1,'completed'=>2];
        if (($flow[$newStatus] ?? -1) <= ($flow[$da['status']] ?? 0)) {
            $allowed = false;
            $_SESSION['err'] = 'Cannot move status backwards.';
        }

        if ($allowed) {
            $startDate = $da['start_date'];
            $endDate   = $da['end_date'];

            if ($newStatus === 'ongoing' && !$startDate) {
                $startDate = date('Y-m-d');
            }
            if ($newStatus === 'completed' && !$endDate) {
                $endDate = date('Y-m-d');
            }

            $pdo->prepare("UPDATE disciplinary_actions SET status=?, start_date=?, end_date=? WHERE id=?")
                ->execute([$newStatus, $startDate, $endDate, $daId]);

            // Sync violation status
            $pdo->prepare("UPDATE violations SET status=? WHERE id=?")
                ->execute([$newStatus, $da['violation_id']]);

            // Notify student
            $stuUser = $pdo->prepare("SELECT u.id FROM students s JOIN users u ON s.user_id=u.id WHERE s.student_id=?");
            $stuUser->execute([$da['student_id']]);
            $stuRow = $stuUser->fetch();
            if ($stuRow) {
                $pdo->prepare("INSERT INTO notifications (user_id,message,link) VALUES (?,?,?)")
                    ->execute([$stuRow['id'], "Your disciplinary case status updated to: $newStatus", '../student/dashboard.php']);
            }

            $_SESSION['msg'] = 'Status updated successfully.';
        }
    }
    header('Location: disciplinary-action.php'); exit;
}

// SEARCH & FILTER
$search = trim($_GET['search'] ?? '');
$filterStatus = $_GET['status'] ?? '';
$where = "WHERE 1=1";
$params = [];
if ($search) {
    $where .= " AND (s.full_name LIKE ? OR da.student_id LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%";
}
if ($filterStatus) { $where .= " AND da.status=?"; $params[] = $filterStatus; }

$stmt = $pdo->prepare("
    SELECT da.*, s.full_name, v.violation, v.category, v.description
    FROM disciplinary_actions da
    JOIN students s ON da.student_id = s.student_id
    JOIN violations v ON da.violation_id = v.id
    $where
    ORDER BY da.id DESC");
$stmt->execute($params);
$daList = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Disciplinary Action - MDSV</title>
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
    <li><a class="nav-link active fw-bold" href="disciplinary-action.php"><i class="bi bi-shield-exclamation"></i> Disciplinary Action</a></li>
    <li><a class="nav-link" href="risk-level.php"><i class="bi bi-bar-chart"></i> Risk Level</a></li>
    <li><a class="nav-link" href="student-appeals.php"><i class="bi bi-chat-left-text"></i> Student Appeals</a></li>
    <li><a class="nav-link" href="data-backup.php"><i class="bi bi-download"></i> Data Backup</a></li>
    <li><a class="nav-link" href="user-management.php"><i class="bi bi-person-gear"></i> User Management</a></li>
  </ul>
</nav>
<div class="d-md-none p-2 bg-light w-100">
  <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#mob4"><i class="bi bi-list"></i> Menu</button>
  <div class="collapse" id="mob4">
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
  <h5 class="mb-3">Disciplinary Actions</h5>
  <?php if(isset($_SESSION['msg'])): ?><div class="alert alert-success"><?= $_SESSION['msg'] ?><?php unset($_SESSION['msg']); ?></div><?php endif; ?>
  <?php if(isset($_SESSION['err'])): ?><div class="alert alert-danger"><?= $_SESSION['err'] ?><?php unset($_SESSION['err']); ?></div><?php endif; ?>

  <form method="GET" class="row g-2 mb-3">
    <div class="col-md-4 col-7">
      <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name or ID"
             value="<?= htmlspecialchars($search) ?>">
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
    <div class="col-auto"><a href="disciplinary-action.php" class="btn btn-secondary btn-sm">Reset</a></div>
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
        <span class="badge bg-<?= $da['category']==='minor'?'primary':'danger' ?>"><?= ucfirst($da['category']) ?></span>
        <?= htmlspecialchars($da['violation']) ?>
      </td>
      <td><small><?= htmlspecialchars($da['sanction']) ?></small></td>
      <td>
        <span class="badge bg-<?= $da['status']==='completed'?'success':($da['status']==='ongoing'?'warning':'secondary') ?>">
          <?= ucfirst($da['status']) ?>
        </span>
      </td>
      <td><?= $da['start_date'] ?? '-' ?></td>
      <td><?= $da['end_date'] ?? '-' ?></td>
      <td>
        <?php if($da['status'] !== 'completed'): ?>
        <button class="btn btn-sm btn-primary" onclick='openUpdateStatus(<?= json_encode($da) ?>)'>Update</button>
        <?php else: ?>
        <span class="text-muted small">Done</span>
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
        <h5 class="modal-title">Update Disciplinary Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong>Student:</strong> <span id="daStudentName"></span></p>
        <p><strong>Violation:</strong> <span id="daViolation"></span></p>
        <p><strong>Sanction:</strong> <small id="daSanction"></small></p>
        <p><strong>Current Status:</strong> <span id="daCurrentStatus"></span></p>
        <div class="mb-2">
          <label class="form-label">New Status</label>
          <select name="new_status" id="daNewStatus" class="form-select"></select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Update Status</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openUpdateStatus(da) {
    document.getElementById('daID').value = da.id;
    document.getElementById('daStudentName').textContent = da.full_name;
    document.getElementById('daViolation').textContent = da.violation;
    document.getElementById('daSanction').textContent = da.sanction;
    document.getElementById('daCurrentStatus').textContent = da.status;

    const sel = document.getElementById('daNewStatus');
    sel.innerHTML = '';
    const flow = {pending:0, ongoing:1, completed:2};
    const curr = flow[da.status] ?? 0;
    Object.entries(flow).forEach(([s,v]) => {
        if (v > curr) {
            sel.innerHTML += `<option value="${s}">${s.charAt(0).toUpperCase()+s.slice(1)}</option>`;
        }
    });

    new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
}
</script>
</body>
</html>