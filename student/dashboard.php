<?php
session_start();
require_once '../db/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../index.php'); exit;
}

// FIRST LOGIN change password
if (isset($_POST['action']) && $_POST['action'] === 'first_change_pass') {
    $new  = $_POST['new_password'];
    $conf = $_POST['confirm_password'];
    if ($new !== $conf) {
        $_SESSION['cp_err'] = 'Passwords do not match.';
    } elseif (strlen($new) < 6) {
        $_SESSION['cp_err'] = 'Minimum 6 characters.';
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password=?, is_first_login=0 WHERE id=?")->execute([$hashed,$_SESSION['user_id']]);
        session_destroy();
        header('Location: ../index.php?changed=1'); exit;
    }
    header('Location: dashboard.php'); exit;
}

// SUBMIT APPEAL
if (isset($_POST['action']) && $_POST['action'] === 'submit_appeal') {
    $violationId = (int)$_POST['violation_id'];
    $explanation = trim($_POST['explanation']);

    // Get student_id from user
    $stuRow = $pdo->prepare("SELECT student_id FROM students WHERE user_id=?");
    $stuRow->execute([$_SESSION['user_id']]);
    $stu = $stuRow->fetch();

    if ($stu) {
        // Check no existing appeal
        $existing = $pdo->prepare("SELECT id FROM appeals WHERE violation_id=? AND student_id=?");
        $existing->execute([$violationId, $stu['student_id']]);
        if ($existing->fetch()) {
            $_SESSION['err'] = 'You have already submitted an appeal for this violation.';
        } else {
            $pdo->prepare("INSERT INTO appeals (violation_id,student_id,explanation) VALUES (?,?,?)")
                ->execute([$violationId, $stu['student_id'], $explanation]);

            // Notify admin
            $adminId = $pdo->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->fetchColumn();
            $pdo->prepare("INSERT INTO notifications (user_id,message,link) VALUES (?,?,?)")
                ->execute([$adminId, "Student {$stu['student_id']} submitted an appeal.", '../admin/student-appeals.php']);

            $_SESSION['msg'] = 'Appeal submitted successfully.';
        }
    }
    header('Location: dashboard.php'); exit;
}

// Get student record
$stuStmt = $pdo->prepare("
    SELECT s.*, c.name as cname, d.name as dname
    FROM students s
    JOIN courses c ON s.course_id = c.id
    JOIN departments d ON s.department_id = d.id
    WHERE s.user_id = ?");
$stuStmt->execute([$_SESSION['user_id']]);
$student = $stuStmt->fetch();

if (!$student) {
    echo "Student record not found. Contact admin."; exit;
}

// Get violations
$viols = $pdo->prepare("
    SELECT v.*, da.sanction, da.status as da_status,
           ap.id as appeal_id, ap.status as appeal_status
    FROM violations v
    LEFT JOIN disciplinary_actions da ON da.violation_id = v.id
    LEFT JOIN appeals ap ON ap.violation_id = v.id AND ap.student_id = v.student_id
    WHERE v.student_id = ?
    ORDER BY v.date_submitted DESC");
$viols->execute([$student['student_id']]);
$violations = $viols->fetchAll();

// Get notifications
$notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 10");
$notifs->execute([$_SESSION['user_id']]);
$notifList = $notifs->fetchAll();
$unreadCount = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
$unreadCount->execute([$_SESSION['user_id']]);
$unread = $unreadCount->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard - MDSV</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="manifest" href="../manifest.json">
</head>
<body>

<?php if($_SESSION['is_first_login'] == 1): ?>
<div class="modal fade" id="firstLoginModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <input type="hidden" name="action" value="first_change_pass">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">Welcome! Please set your password</h5>
      </div>
      <div class="modal-body">
        <?php if(isset($_SESSION['cp_err'])): ?>
          <div class="alert alert-danger"><?= $_SESSION['cp_err'] ?><?php unset($_SESSION['cp_err']); ?></div>
        <?php endif; ?>
        <p class="text-muted small">Your default password is your last 5 student ID digits. Please change it now.</p>
        <div class="mb-2"><label class="form-label">New Password</label>
          <input type="password" name="new_password" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">Confirm Password</label>
          <input type="password" name="confirm_password" class="form-control" required></div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-warning w-100">Change Password & Re-login</button>
      </div>
    </form>
  </div>
</div>
<script>
window.addEventListener('load', () => new bootstrap.Modal(document.getElementById('firstLoginModal')).show());
</script>
<?php endif; ?>

<nav class="navbar navbar-dark bg-dark px-3">
  <span class="navbar-brand fw-bold">MDSV Student Portal</span>
  <div class="d-flex align-items-center gap-2">
    <!-- Notifications -->
    <div class="dropdown">
      <button class="btn btn-outline-light btn-sm position-relative" data-bs-toggle="dropdown">
        <i class="bi bi-bell"></i>
        <?php if($unread > 0): ?>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $unread ?></span>
        <?php endif; ?>
      </button>
      <ul class="dropdown-menu dropdown-menu-end" style="min-width:280px;max-height:300px;overflow-y:auto">
        <?php if(empty($notifList)): ?>
          <li><span class="dropdown-item text-muted">No notifications</span></li>
        <?php else: foreach($notifList as $n): ?>
          <li>
            <span class="dropdown-item <?= $n['is_read']?'':'fw-bold' ?> small">
              <?= htmlspecialchars($n['message']) ?><br>
              <span class="text-muted" style="font-size:11px"><?= $n['created_at'] ?></span>
            </span>
          </li>
        <?php endforeach; endif; ?>
      </ul>
    </div>
    <span class="text-white small d-none d-sm-inline"><?= htmlspecialchars($_SESSION['full_name']) ?></span>
    <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
  </div>
</nav>

<div class="container py-4">

  <!-- STUDENT INFO CARD -->
  <div class="card mb-4">
    <div class="card-body">
      <div class="row">
        <div class="col-md-8">
          <h5><?= htmlspecialchars($student['full_name']) ?></h5>
          <p class="mb-1 text-muted">
            <strong>Student ID:</strong> <?= htmlspecialchars($student['student_id']) ?> &nbsp;|&nbsp;
            <strong>Course:</strong> <?= htmlspecialchars($student['cname']) ?> &nbsp;|&nbsp;
            <strong>Year:</strong> Year <?= $student['year_level'] ?> &nbsp;|&nbsp;
            <strong>Dept:</strong> <?= htmlspecialchars($student['dname']) ?>
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- VIOLATIONS -->
  <h5 class="mb-3">My Violation Records</h5>
  <?php if(isset($_SESSION['msg'])): ?><div class="alert alert-success"><?= $_SESSION['msg'] ?><?php unset($_SESSION['msg']); ?></div><?php endif; ?>
  <?php if(isset($_SESSION['err'])): ?><div class="alert alert-danger"><?= $_SESSION['err'] ?><?php unset($_SESSION['err']); ?></div><?php endif; ?>

  <?php if(empty($violations)): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle"></i> No violations on record. Keep it up!</div>
  <?php else: ?>
  <div class="table-responsive">
  <table class="table table-bordered table-sm">
    <thead class="table-dark">
      <tr><th>Date</th><th>Type</th><th>Violation</th><th>Description</th><th>Status</th><th>Sanction</th><th>Appeal</th></tr>
    </thead>
    <tbody>
    <?php foreach($violations as $v): ?>
    <tr>
      <td><?= date('M d, Y', strtotime($v['date_submitted'])) ?></td>
      <td><span class="badge bg-<?= $v['category']==='minor'?'primary':'danger' ?>"><?= ucfirst($v['category']) ?></span></td>
      <td><?= htmlspecialchars($v['violation']) ?></td>
      <td><small><?= htmlspecialchars($v['description'] ?? '-') ?></small></td>
      <td><span class="badge bg-<?= $v['status']==='completed'?'success':($v['status']==='ongoing'?'warning':'secondary') ?>"><?= ucfirst($v['status']) ?></span></td>
      <td><small><?= htmlspecialchars($v['sanction'] ?? '-') ?></small></td>
      <td>
        <?php if($v['appeal_id']): ?>
          <span class="badge bg-<?= $v['appeal_status']==='approved'?'success':($v['appeal_status']==='rejected'?'danger':'secondary') ?>">
            Appeal: <?= ucfirst($v['appeal_status']) ?>
          </span>
        <?php else: ?>
          <button class="btn btn-sm btn-outline-warning" onclick='openAppeal(<?= json_encode($v) ?>)'>Appeal</button>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php endif; ?>
</div>

<!-- APPEAL MODAL -->
<div class="modal fade" id="appealModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <input type="hidden" name="action" value="submit_appeal">
      <input type="hidden" name="violation_id" id="appealViolID">
      <div class="modal-header">
        <h5 class="modal-title">Submit Appeal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small">Violation: <strong id="appealViolName"></strong></p>
        <label class="form-label">Explain your appeal</label>
        <textarea name="explanation" class="form-control" rows="4"
                  placeholder="Explain why you are appealing this violation..." required></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-warning">Submit Appeal</button>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openAppeal(v) {
    document.getElementById('appealViolID').value = v.id;
    document.getElementById('appealViolName').textContent = v.violation;
    new bootstrap.Modal(document.getElementById('appealModal')).show();
}
</script>
</body>
</html>