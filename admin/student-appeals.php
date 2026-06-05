<?php
session_start();
require_once '../db/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php'); exit;
}

// APPROVE / REJECT
if (isset($_POST['action']) && in_array($_POST['action'],['approve','reject'])) {
    $appealId = (int)$_POST['appeal_id'];
    $status   = $_POST['action'] === 'approve' ? 'approved' : 'rejected';

    $ap = $pdo->prepare("SELECT * FROM appeals WHERE id=?");
    $ap->execute([$appealId]);
    $appeal = $ap->fetch();

    if ($appeal) {
        $pdo->prepare("UPDATE appeals SET status=? WHERE id=?")->execute([$status, $appealId]);

        if ($status === 'approved') {
            // Remove violation
            $pdo->prepare("DELETE FROM disciplinary_actions WHERE violation_id=?")->execute([$appeal['violation_id']]);
            $pdo->prepare("DELETE FROM violations WHERE id=?")->execute([$appeal['violation_id']]);
            $msg = 'Your appeal has been APPROVED. The violation has been removed.';
        } else {
            $msg = 'Your appeal has been REJECTED. The violation remains on record.';
        }

        // Notify student
        $stuUser = $pdo->prepare("SELECT u.id FROM students s JOIN users u ON s.user_id=u.id WHERE s.student_id=?");
        $stuUser->execute([$appeal['student_id']]);
        $stuRow = $stuUser->fetch();
        if ($stuRow) {
            $pdo->prepare("INSERT INTO notifications (user_id,message,link) VALUES (?,?,?)")
                ->execute([$stuRow['id'], $msg, '../student/dashboard.php']);
        }

        $_SESSION['msg'] = 'Appeal ' . $status . '.';
    }
    header('Location: student-appeals.php'); exit;
}

$appeals = $pdo->query("
    SELECT a.*, s.full_name, v.violation, v.category, v.description, v.date_submitted as vdate
    FROM appeals a
    JOIN students s ON a.student_id = s.student_id
    JOIN violations v ON a.violation_id = v.id
    ORDER BY a.submitted_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Appeals - MDSV</title>
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
    <li><a class="nav-link" href="risk-level.php"><i class="bi bi-bar-chart"></i> Risk Level</a></li>
    <li><a class="nav-link active fw-bold" href="student-appeals.php"><i class="bi bi-chat-left-text"></i> Student Appeals</a></li>
    <li><a class="nav-link" href="data-backup.php"><i class="bi bi-download"></i> Data Backup</a></li>
    <li><a class="nav-link" href="user-management.php"><i class="bi bi-person-gear"></i> User Management</a></li>
  </ul>
</nav>
<div class="d-md-none p-2 bg-light w-100">
  <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#mob6"><i class="bi bi-list"></i> Menu</button>
  <div class="collapse" id="mob6">
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
  <h5 class="mb-3">Student Appeals</h5>
  <?php if(isset($_SESSION['msg'])): ?><div class="alert alert-success"><?= $_SESSION['msg'] ?><?php unset($_SESSION['msg']); ?></div><?php endif; ?>

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
        <span class="badge bg-<?= $ap['category']==='minor'?'primary':'danger' ?>"><?= ucfirst($ap['category']) ?></span>
        <?= htmlspecialchars($ap['violation']) ?>
      </td>
      <td><small><?= htmlspecialchars(substr($ap['explanation'],0,80)) ?>...</small></td>
      <td><?= date('M d, Y', strtotime($ap['submitted_at'])) ?></td>
      <td>
        <span class="badge bg-<?= $ap['status']==='approved'?'success':($ap['status']==='rejected'?'danger':'secondary') ?>">
          <?= ucfirst($ap['status']) ?>
        </span>
      </td>
      <td>
        <?php if($ap['status']==='pending'): ?>
        <form method="POST" class="d-inline">
          <input type="hidden" name="appeal_id" value="<?= $ap['id'] ?>">
          <button name="action" value="approve" class="btn btn-success btn-sm"
                  onclick="return confirm('Approve this appeal? The violation will be removed.')">Approve</button>
          <button name="action" value="reject" class="btn btn-danger btn-sm"
                  onclick="return confirm('Reject this appeal?')">Reject</button>
        </form>
        <?php else: ?>
        <span class="text-muted small">Resolved</span>
        <?php endif; ?>
        <button class="btn btn-info btn-sm" onclick='viewAppeal(<?= json_encode($ap) ?>)'>View</button>
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
      <div class="modal-header"><h5 class="modal-title">Appeal Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
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
        <table class="table table-sm">
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
</script>
</body>
</html>