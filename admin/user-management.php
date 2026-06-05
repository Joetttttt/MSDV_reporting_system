<?php
session_start();
require_once '../db/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php'); exit;
}

define('DELETE_PASSWORD', 'delete123');

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
        $hashed = password_hash($pass, PASSWORD_DEFAULT);
        try {
            $pdo->prepare("INSERT INTO users (full_name,username,email,password,role,is_first_login) VALUES (?,?,?,?,?,1)")
                ->execute([$full,$uname,$email,$hashed,$role]);
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
        $hashed = password_hash($pass, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hashed,$uid]);
        $_SESSION['msg'] = 'Password changed.';
    }
    header('Location: user-management.php'); exit;
}

// DELETE USER
if (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    $uid  = (int)$_POST['user_id'];
    $pass = trim($_POST['del_password']);
    if ($pass === DELETE_PASSWORD) {
        $pdo->prepare("DELETE FROM users WHERE id=? AND role != 'admin'")->execute([$uid]);
        $_SESSION['msg'] = 'User deleted.';
    } else {
        $_SESSION['err'] = 'Incorrect deletion password.';
    }
    header('Location: user-management.php'); exit;
}

// FETCH USERS (exclude admin)
$search = trim($_GET['search'] ?? '');
$filterRole = $_GET['role'] ?? '';
$where = "WHERE role != 'admin'";
$params = [];
if ($search) { $where .= " AND (full_name LIKE ? OR username LIKE ?)"; $params[]= "%$search%"; $params[]="%$search%"; }
if ($filterRole) { $where .= " AND role=?"; $params[]=$filterRole; }
$stmt = $pdo->prepare("SELECT * FROM users $where ORDER BY id DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Management - MDSV</title>
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
    <li><a class="nav-link" href="student-appeals.php"><i class="bi bi-chat-left-text"></i> Student Appeals</a></li>
    <li><a class="nav-link" href="data-backup.php"><i class="bi bi-download"></i> Data Backup</a></li>
    <li><a class="nav-link active fw-bold" href="user-management.php"><i class="bi bi-person-gear"></i> User Management</a></li>
  </ul>
</nav>
<div class="d-md-none p-2 bg-light w-100">
  <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#mob8"><i class="bi bi-list"></i> Menu</button>
  <div class="collapse" id="mob8">
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
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h5 class="mb-0">User Management</h5>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
      <i class="bi bi-plus"></i> Add User
    </button>
  </div>

  <?php if(isset($_SESSION['msg'])): ?><div class="alert alert-success"><?= $_SESSION['msg'] ?><?php unset($_SESSION['msg']); ?></div><?php endif; ?>
  <?php if(isset($_SESSION['err'])): ?><div class="alert alert-danger"><?= $_SESSION['err'] ?><?php unset($_SESSION['err']); ?></div><?php endif; ?>

  <form method="GET" class="row g-2 mb-3">
    <div class="col-md-4 col-7">
      <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name or username"
             value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-2 col-5">
      <select name="role" class="form-select form-select-sm">
        <option value="">All Roles</option>
        <option value="student" <?=$filterRole==='student'?'selected':''?>>Student</option>
        <option value="teacher" <?=$filterRole==='teacher'?'selected':''?>>Teacher</option>
        <option value="csu"     <?=$filterRole==='csu'    ?'selected':''?>>CSU</option>
        <option value="jassu"   <?=$filterRole==='jassu'  ?'selected':''?>>JASSU</option>
      </select>
    </div>
    <div class="col-auto"><button type="submit" class="btn btn-primary btn-sm">Filter</button></div>
    <div class="col-auto"><a href="user-management.php" class="btn btn-secondary btn-sm">Reset</a></div>
  </form>

  <div class="table-responsive">
  <table class="table table-bordered table-sm table-hover">
    <thead class="table-dark">
      <tr><th>Full Name</th><th>Username</th><th>Email</th><th>Role</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach($users as $u): ?>
    <tr>
      <td><?= htmlspecialchars($u['full_name']) ?></td>
      <td><?= htmlspecialchars($u['username']) ?></td>
      <td><?= htmlspecialchars($u['email']) ?></td>
      <td><span class="badge bg-secondary"><?= ucfirst($u['role']) ?></span></td>
      <td>
        <button class="btn btn-warning btn-sm" onclick='openEditUser(<?= json_encode($u) ?>)'>Edit</button>
        <button class="btn btn-info btn-sm" onclick='openChangePass(<?= $u['id'] ?>)'>Change Pass</button>
        <button class="btn btn-danger btn-sm" onclick='openDeleteUser(<?= $u['id'] ?>)'>Delete</button>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($users)): ?>
    <tr><td colspan="5" class="text-center text-muted">No users found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
</main>
</div></div>

<!-- ADD USER MODAL -->
<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <input type="hidden" name="action" value="add_user">
      <div class="modal-header"><h5 class="modal-title">Add User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-2"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
        <div class="mb-2"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">Confirm Password</label><input type="password" name="confirm_password" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">Role</label>
          <select name="role" class="form-select" required>
            <option value="">Select Role</option>
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
            <option value="csu">CSU</option>
            <option value="jassu">JASSU</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
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
      <div class="modal-header"><h5 class="modal-title">Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-2"><label class="form-label">Full Name</label><input type="text" name="full_name" id="euName" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">Username</label><input type="text" name="username" id="euUsername" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">Email</label><input type="email" name="email" id="euEmail" class="form-control"></div>
        <div class="mb-2"><label class="form-label">Role</label>
          <select name="role" id="euRole" class="form-select">
            <option value="student">Student</option>
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
      <div class="modal-header"><h5 class="modal-title">Change Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-2"><label class="form-label">New Password</label><input type="password" name="new_password" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">Confirm New Password</label><input type="password" name="confirm_new_password" class="form-control" required></div>
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
      <div class="modal-header bg-danger text-white"><h5 class="modal-title">Delete User</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p>Enter deletion password to confirm.</p>
        <input type="password" name="del_password" class="form-control" placeholder="Deletion Password" required>
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
function openEditUser(u) {
    document.getElementById('editUID').value = u.id;
    document.getElementById('euName').value = u.full_name;
    document.getElementById('euUsername').value = u.username;
    document.getElementById('euEmail').value = u.email ?? '';
    document.getElementById('euRole').value = u.role;
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
</script>
</body>
</html>