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

// ADD USER
if (isset($_POST['action']) && $_POST['action'] === 'add_user') {
    $full  = trim($_POST['full_name']);
    $uname = trim($_POST['username']);
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];
    $pass2 = $_POST['confirm_password'];
    $role  = $_POST['role'];
    if ($pass !== $pass2) {
        $_SESSION['err'] = 'Passwords do not match.';
    } else {
        try {
            $pdo->prepare("INSERT INTO users (full_name,username,email,password,role,is_first_login) VALUES (?,?,?,?,?,1)")
                ->execute([$full,$uname,$email,password_hash($pass,PASSWORD_DEFAULT),$role]);
            $_SESSION['msg'] = 'User added successfully.';
        } catch(Exception $e) {
            $_SESSION['err'] = 'Username already exists.';
        }
    }
    header('Location: user-management.php'); exit;
}

// EDIT USER
if (isset($_POST['action']) && $_POST['action'] === 'edit_user') {
    $uid   = (int)$_POST['user_id'];
    $full  = trim($_POST['full_name']);
    $uname = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role  = $_POST['role'];
    $pdo->prepare("UPDATE users SET full_name=?,username=?,email=?,role=? WHERE id=?")
        ->execute([$full,$uname,$email,$role,$uid]);
    $_SESSION['msg'] = 'User updated.';
    header('Location: user-management.php'); exit;
}

// CHANGE PASSWORD
if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $uid  = (int)$_POST['user_id'];
    $pass = $_POST['new_password'];
    $pass2= $_POST['confirm_new_password'];
    if ($pass !== $pass2) {
        $_SESSION['err'] = 'Passwords do not match.';
    } else {
        $pdo->prepare("UPDATE users SET password=? WHERE id=?")
            ->execute([password_hash($pass,PASSWORD_DEFAULT),$uid]);
        $_SESSION['msg'] = 'Password changed successfully.';
    }
    header('Location: user-management.php'); exit;
}

// DELETE USER (staff only — never students or admin)
if (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $uid  = (int)$_POST['user_id'];
    $pass = trim($_POST['del_password']);
    if ($pass === DELETE_PASSWORD) {
        $pdo->prepare("DELETE FROM users WHERE id=? AND role NOT IN ('admin','student')")
            ->execute([$uid]);
        $_SESSION['msg'] = 'User deleted.';
    } else {
        $_SESSION['err'] = 'Incorrect deletion password.';
    }
    header('Location: user-management.php'); exit;
}

// FETCH STAFF
$searchStaff = trim($_GET['search_staff'] ?? '');
$filterRole  = $_GET['role'] ?? '';
$whereStaff  = "WHERE role NOT IN ('admin','student')";
$paramsStaff = [];
if ($searchStaff) {
    $whereStaff .= " AND (full_name LIKE ? OR username LIKE ?)";
    $paramsStaff[] = "%$searchStaff%";
    $paramsStaff[] = "%$searchStaff%";
}
if ($filterRole && $filterRole !== 'student') {
    $whereStaff .= " AND role=?";
    $paramsStaff[] = $filterRole;
}
$staffStmt = $pdo->prepare("SELECT * FROM users $whereStaff ORDER BY role,full_name");
$staffStmt->execute($paramsStaff);
$staffUsers = $staffStmt->fetchAll();

// FETCH STUDENT ACCOUNTS
$searchStu = trim($_GET['search_stu'] ?? '');
$whereStu  = "WHERE u.role='student'";
$paramsStu = [];
if ($searchStu) {
    $whereStu .= " AND (u.full_name LIKE ? OR u.username LIKE ? OR s.student_id LIKE ?)";
    $paramsStu[] = "%$searchStu%";
    $paramsStu[] = "%$searchStu%";
    $paramsStu[] = "%$searchStu%";
}
$stuStmt = $pdo->prepare("
    SELECT u.*, s.student_id as sid
    FROM users u
    LEFT JOIN students s ON s.user_id=u.id
    $whereStu ORDER BY u.full_name");
$stuStmt->execute($paramsStu);
$studentUsers = $stuStmt->fetchAll();

$activeTab = $_GET['tab'] ?? 'staff';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Management - MDSV Admin</title>
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
    <li><a class="nav-link text-dark" href="data-backup.php"><i class="bi bi-download me-2"></i>Data Backup</a></li>
    <li><a class="nav-link active fw-bold text-primary" href="user-management.php"><i class="bi bi-person-gear me-2"></i>User Management</a></li>
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
    <li><a class="nav-link" href="data-backup.php"><i class="bi bi-download me-2"></i>Data Backup</a></li>
    <li><a class="nav-link fw-bold text-primary" href="user-management.php"><i class="bi bi-person-gear me-2"></i>User Management</a></li>
  </ul>
</div>

<main class="col-md-10 px-3 py-3">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="mb-0">User Management</h5>
    <button class="btn btn-primary btn-sm"
            data-bs-toggle="modal" data-bs-target="#addUserModal">
      <i class="bi bi-plus-circle"></i> Add Staff User
    </button>
  </div>

  <?php if(isset($_SESSION['msg'])): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <?= $_SESSION['msg'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['msg']); endif; ?>
  <?php if(isset($_SESSION['err'])): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <?= $_SESSION['err'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['err']); endif; ?>

  <!-- TABS -->
  <ul class="nav nav-tabs mb-3" id="umTabs">
    <li class="nav-item">
      <button class="nav-link <?= $activeTab==='staff'?'active':'' ?>"
              id="staffTabBtn"
              data-bs-toggle="tab" data-bs-target="#staffTab">
        <i class="bi bi-person-badge"></i> Staff
        <span class="badge bg-secondary ms-1"><?= count($staffUsers) ?></span>
      </button>
    </li>
    <li class="nav-item">
      <button class="nav-link <?= $activeTab==='student'?'active':'' ?>"
              id="studentTabBtn"
              data-bs-toggle="tab" data-bs-target="#studentTab">
        <i class="bi bi-mortarboard"></i> Student Accounts
        <span class="badge bg-secondary ms-1"><?= count($studentUsers) ?></span>
      </button>
    </li>
  </ul>

  <div class="tab-content">

    <!-- STAFF TAB -->
    <div class="tab-pane fade <?= $activeTab==='staff'?'show active':'' ?>" id="staffTab">
      <form method="GET" class="row g-2 mb-3">
        <input type="hidden" name="tab" value="staff">
        <div class="col-md-4 col-7">
          <input type="text" name="search_staff" class="form-control form-control-sm"
                 placeholder="Search name or username"
                 value="<?= htmlspecialchars($searchStaff) ?>">
        </div>
        <div class="col-md-2 col-5">
          <select name="role" class="form-select form-select-sm">
            <option value="">All Roles</option>
            <option value="teacher" <?=$filterRole==='teacher'?'selected':''?>>Teacher</option>
            <option value="csu"     <?=$filterRole==='csu'    ?'selected':''?>>CSU</option>
            <option value="jassu"   <?=$filterRole==='jassu'  ?'selected':''?>>JASSU</option>
          </select>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        </div>
        <div class="col-auto">
          <a href="user-management.php?tab=staff" class="btn btn-secondary btn-sm">Reset</a>
        </div>
      </form>

      <div class="table-responsive">
      <table class="table table-bordered table-sm table-hover">
        <thead class="table-dark">
          <tr>
            <th>Full Name</th><th>Username</th><th>Email</th><th>Role</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($staffUsers as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['full_name']) ?></td>
          <td><?= htmlspecialchars($u['username']) ?></td>
          <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
          <td>
            <span class="badge bg-<?= $u['role']==='teacher'?'primary':
                ($u['role']==='csu'?'success':'warning') ?>">
              <?= ucfirst($u['role']) ?>
            </span>
          </td>
          <td>
            <button class="btn btn-warning btn-sm"
                    onclick='openEditUser(<?= json_encode($u) ?>)'>
              <i class="bi bi-pencil"></i> Edit
            </button>
            <button class="btn btn-info btn-sm"
                    onclick='openChangePass(<?= $u["id"] ?>)'>
              <i class="bi bi-key"></i> Password
            </button>
            <button class="btn btn-danger btn-sm"
                    onclick='openDeleteUser(<?= $u["id"] ?>)'>
              <i class="bi bi-trash"></i> Delete
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($staffUsers)): ?>
        <tr><td colspan="5" class="text-center text-muted">No staff users found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
      </div>
    </div>

    <!-- STUDENT ACCOUNTS TAB -->
    <div class="tab-pane fade <?= $activeTab==='student'?'show active':'' ?>" id="studentTab">
      <div class="alert alert-info py-2 small mb-3">
        <i class="bi bi-info-circle"></i>
        Student accounts are created automatically when a student is added in
        <a href="student-records.php">Student Records</a>.
        Student accounts cannot be deleted here.
        To remove a student account, delete the student from Student Records.
      </div>

      <form method="GET" class="row g-2 mb-3">
        <input type="hidden" name="tab" value="student">
        <div class="col-md-5 col-9">
          <input type="text" name="search_stu" class="form-control form-control-sm"
                 placeholder="Search name, username or student ID"
                 value="<?= htmlspecialchars($searchStu) ?>">
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary btn-sm">Search</button>
        </div>
        <div class="col-auto">
          <a href="user-management.php?tab=student" class="btn btn-secondary btn-sm">Reset</a>
        </div>
      </form>

      <div class="table-responsive">
      <table class="table table-bordered table-sm table-hover">
        <thead class="table-dark">
          <tr>
            <th>Student ID</th><th>Full Name</th><th>Username</th><th>Role</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($studentUsers as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['sid'] ?? '-') ?></td>
          <td><?= htmlspecialchars($u['full_name']) ?></td>
          <td><?= htmlspecialchars($u['username']) ?></td>
          <td><span class="badge bg-primary">Student</span></td>
          <td>
            <!-- Only password change allowed for students -->
            <button class="btn btn-info btn-sm"
                    onclick='openChangePass(<?= $u["id"] ?>)'>
              <i class="bi bi-key"></i> Change Password
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($studentUsers)): ?>
        <tr><td colspan="5" class="text-center text-muted">No student accounts found.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
      </div>
    </div>

  </div><!-- end tab-content -->
</main>
</div></div>

<!-- ADD USER MODAL -->
<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <input type="hidden" name="action" value="add_user">
      <div class="modal-header">
        <h5 class="modal-title">Add Staff User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">Full Name</label>
          <input type="text" name="full_name" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control">
        </div>
        <div class="mb-2">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Confirm Password</label>
          <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Role</label>
          <select name="role" class="form-select" required>
            <option value="">Select Role</option>
            <option value="teacher">Teacher</option>
            <option value="csu">CSU</option>
            <option value="jassu">JASSU</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save User</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT USER MODAL -->
<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <input type="hidden" name="action" value="edit_user">
      <input type="hidden" name="user_id" id="editUID">
      <div class="modal-header">
        <h5 class="modal-title">Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">Full Name</label>
          <input type="text" name="full_name" id="euName" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Username</label>
          <input type="text" name="username" id="euUsername" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Email</label>
          <input type="email" name="email" id="euEmail" class="form-control">
        </div>
        <div class="mb-2">
          <label class="form-label">Role</label>
          <select name="role" id="euRole" class="form-select">
            <option value="teacher">Teacher</option>
            <option value="csu">CSU</option>
            <option value="jassu">JASSU</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-warning">Update</button>
      </div>
    </form>
  </div>
</div>

<!-- CHANGE PASSWORD MODAL -->
<div class="modal fade" id="changePassModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <input type="hidden" name="action" value="change_password">
      <input type="hidden" name="user_id" id="cpUID">
      <div class="modal-header">
        <h5 class="modal-title">Change Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">New Password</label>
          <input type="password" name="new_password" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Confirm New Password</label>
          <input type="password" name="confirm_new_password" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-info">Change Password</button>
      </div>
    </form>
  </div>
</div>

<!-- DELETE USER MODAL -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <input type="hidden" name="action" value="delete_user">
      <input type="hidden" name="user_id" id="delUID">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Delete Staff User</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>This will permanently delete this staff account. Enter deletion password to confirm.</p>
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
// Restore active tab on page reload
document.addEventListener('DOMContentLoaded', function () {
    const tab = new URLSearchParams(window.location.search).get('tab');
    if (tab === 'student') {
        document.getElementById('studentTabBtn').click();
    }
});

function openEditUser(u) {
    document.getElementById('editUID').value    = u.id;
    document.getElementById('euName').value     = u.full_name;
    document.getElementById('euUsername').value = u.username;
    document.getElementById('euEmail').value    = u.email ?? '';
    document.getElementById('euRole').value     = u.role;
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}
function openChangePass(uid) {
    document.getElementById('cpUID').value = uid;
    new bootstrap.Modal(document.getElementById('changePassModal')).show();
}
function openDeleteUser(uid) {
    document.getElementById('delUID').value = uid;
    new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
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