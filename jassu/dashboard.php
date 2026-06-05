<?php
session_start();
require_once '../db/connection.php';
$allowedRoles = ['teacher','csu','jassu'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], $allowedRoles)) {
    header('Location: ../index.php'); exit;
}

// FIRST LOGIN - force password change
if ($_SESSION['is_first_login'] == 1) {
    // handled via modal below
}

// CHANGE PASSWORD on first login
if (isset($_POST['action']) && $_POST['action'] === 'first_change_pass') {
    $new  = $_POST['new_password'];
    $conf = $_POST['confirm_password'];
    if ($new !== $conf) {
        $_SESSION['cp_err'] = 'Passwords do not match.';
    } elseif (strlen($new) < 6) {
        $_SESSION['cp_err'] = 'Minimum 6 characters.';
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password=?, is_first_login=0 WHERE id=?")
            ->execute([$hashed, $_SESSION['user_id']]);
        session_destroy();
        header('Location: ../index.php?changed=1'); exit;
    }
    header('Location: dashboard.php'); exit;
}

$role = $_SESSION['role'];
$myReports = $pdo->prepare("
    SELECT v.*, s.full_name FROM violations v
    JOIN students s ON v.student_id = s.student_id
    WHERE v.reporter_id = ? ORDER BY v.date_submitted DESC LIMIT 5");
$myReports->execute([$_SESSION['user_id']]);
$recentReports = $myReports->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= ucfirst($role) ?> Dashboard - MDSV</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="manifest" href="../manifest.json">
</head>
<body>

<?php if($_SESSION['is_first_login'] == 1): ?>
<!-- FIRST LOGIN MODAL - auto shows -->
<div class="modal fade" id="firstLoginModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <input type="hidden" name="action" value="first_change_pass">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">Welcome! Please change your password</h5>
      </div>
      <div class="modal-body">
        <?php if(isset($_SESSION['cp_err'])): ?>
          <div class="alert alert-danger"><?= $_SESSION['cp_err'] ?><?php unset($_SESSION['cp_err']); ?></div>
        <?php endif; ?>
        <p class="text-muted small">For your security, set a new password before continuing.</p>
        <div class="mb-2"><label class="form-label">New Password</label>
          <input type="password" name="new_password" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">Confirm Password</label>
          <input type="password" name="confirm_password" class="form-control" required></div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-warning w-100">Change Password & Login Again</button>
      </div>
    </form>
  </div>
</div>
<script>
window.addEventListener('load', () => {
    new bootstrap.Modal(document.getElementById('firstLoginModal')).show();
});
</script>
<?php endif; ?>

<nav class="navbar navbar-dark bg-success px-3">
  <span class="navbar-brand fw-bold">MDSV - <?= ucfirst($role) ?></span>
  <div class="d-flex align-items-center gap-2">
    <span class="text-white small d-none d-md-inline"><?= htmlspecialchars($_SESSION['full_name']) ?></span>
    <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
  </div>
</nav>
<div class="container-fluid"><div class="row">
<nav class="col-md-2 d-none d-md-block bg-light py-3" style="min-height:100vh">
  <ul class="nav flex-column">
    <li><a class="nav-link active fw-bold" href="dashboard.php"><i class="bi bi-house"></i> Dashboard</a></li>
    <li><a class="nav-link" href="report-form.php"><i class="bi bi-file-earmark-plus"></i> Report Violation</a></li>
    <li><a class="nav-link" href="my-reports.php"><i class="bi bi-list-ul"></i> My Reports</a></li>
  </ul>
</nav>
<div class="d-md-none p-2 bg-light w-100">
  <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#mobR"><i class="bi bi-list"></i> Menu</button>
  <div class="collapse" id="mobR">
    <ul class="nav flex-column mt-1">
      <li><a class="nav-link" href="dashboard.php">Dashboard</a></li>
      <li><a class="nav-link" href="report-form.php">Report Violation</a></li>
      <li><a class="nav-link" href="my-reports.php">My Reports</a></li>
    </ul>
  </div>
</div>
<main class="col-md-10 px-3 py-3">
  <h5>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></h5>
  <p class="text-muted">Role: <?= ucfirst($role) ?></p>

  <div class="d-flex gap-2 mb-4">
    <a href="report-form.php" class="btn btn-success"><i class="bi bi-file-earmark-plus"></i> Submit New Report</a>
    <a href="my-reports.php" class="btn btn-outline-secondary"><i class="bi bi-list-ul"></i> View My Reports</a>
  </div>

  <h6>Recent Reports</h6>
  <div class="table-responsive">
  <table class="table table-sm table-bordered">
    <thead class="table-dark"><tr><th>Student</th><th>Violation</th><th>Type</th><th>Status</th><th>Date</th></tr></thead>
    <tbody>a
    <?php foreach($recentReports as $r): ?>
    <tr>
      <td><?= htmlspecialchars($r['full_name']) ?></td>
      <td><?= htmlspecialchars($r['violation']) ?></td>
      <td><span class="badge bg-<?= $r['category']==='minor'?'primary':'danger' ?>"><?= ucfirst($r['category']) ?></span></td>
      <td><?= ucfirst($r['status']) ?></td>
      <td><?= date('M d Y', strtotime($r['date_submitted'])) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($recentReports)): ?>
    <tr><td colspan="5" class="text-center text-muted">No reports yet.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
</main>
</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>