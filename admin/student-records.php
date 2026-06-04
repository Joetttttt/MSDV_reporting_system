<?php
session_start();
require_once '../db/connection.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php'); exit;
}

// Deletion password (hardcoded — change as needed)
define('DELETE_PASSWORD', 'delete123');

// --- ADD STUDENT ---
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $sid    = trim($_POST['student_id']);
    $fname  = trim($_POST['full_name']);
    $course = (int)$_POST['course_id'];
    $year   = (int)$_POST['year_level'];
    $dept   = (int)$_POST['department_id'];

    // Create user account for student
    $uname    = 'MCC@' . explode(' ', $fname)[0];
    $lastFive = substr(str_replace('-','',$sid), -5);
    $hashed   = password_hash($lastFive, PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();
        $u = $pdo->prepare("INSERT INTO users (full_name,username,email,password,role,is_first_login) VALUES (?,?,?,?,'student',1)");
        $u->execute([$fname, $uname, '', $hashed]);
        $uid = $pdo->lastInsertId();

        $s = $pdo->prepare("INSERT INTO students (student_id,full_name,course_id,year_level,department_id,user_id) VALUES (?,?,?,?,?,?)");
        $s->execute([$sid,$fname,$course,$year,$dept,$uid]);
        $pdo->commit();
        $_SESSION['msg'] = 'Student added successfully.';
    } catch(Exception $e) {
        $pdo->rollBack();
        $_SESSION['err'] = 'Error: ' . $e->getMessage();
    }
    header('Location: student-records.php'); exit;
}

// --- EDIT STUDENT ---
if (isset($_POST['action']) && $_POST['action'] === 'edit') {
    $sid   = trim($_POST['student_id']);
    $fname = trim($_POST['full_name']);
    $course= (int)$_POST['course_id'];
    $year  = (int)$_POST['year_level'];
    $dept  = (int)$_POST['department_id'];
    $s = $pdo->prepare("UPDATE students SET full_name=?,course_id=?,year_level=?,department_id=? WHERE student_id=?");
    $s->execute([$fname,$course,$year,$dept,$sid]);
    $u = $pdo->prepare("UPDATE users SET full_name=? WHERE id=(SELECT user_id FROM students WHERE student_id=?)");
    $u->execute([$fname,$sid]);
    $_SESSION['msg'] = 'Student updated.';
    header('Location: student-records.php'); exit;
}

// --- DELETE STUDENT ---
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $sid  = trim($_POST['student_id']);
    $pass = trim($_POST['del_password']);
    if ($pass === DELETE_PASSWORD) {
        $uid = $pdo->prepare("SELECT user_id FROM students WHERE student_id=?");
        $uid->execute([$sid]);
        $row = $uid->fetch();
        $pdo->prepare("DELETE FROM students WHERE student_id=?")->execute([$sid]);
        if ($row) $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$row['user_id']]);
        $_SESSION['msg'] = 'Student deleted successfully.';
    } else {
        $_SESSION['err'] = 'Incorrect deletion password.';
    }
    header('Location: student-records.php'); exit;
}

// --- FETCH LISTS ---
$courses = $pdo->query("SELECT c.id, c.name, d.name as dept FROM courses c JOIN departments d ON c.department_id=d.id")->fetchAll();
$departments = $pdo->query("SELECT * FROM departments")->fetchAll();

// --- SEARCH & FILTER ---
$search = trim($_GET['search'] ?? '');
$filterCourse = $_GET['course'] ?? '';
$filterYear   = $_GET['year_level'] ?? '';
$filterDept   = $_GET['dept'] ?? '';

$where = "WHERE 1=1";
$params = [];
if ($search) { $where .= " AND (s.student_id LIKE ? OR s.full_name LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($filterCourse) { $where .= " AND s.course_id=?"; $params[] = $filterCourse; }
if ($filterYear)   { $where .= " AND s.year_level=?"; $params[] = $filterYear; }
if ($filterDept)   { $where .= " AND s.department_id=?"; $params[] = $filterDept; }

$stmt = $pdo->prepare("
    SELECT s.*, c.name as course_name, d.name as dept_name,
           (SELECT COUNT(*) FROM violations v WHERE v.student_id=s.student_id) as total_violations
    FROM students s
    JOIN courses c ON s.course_id=c.id
    JOIN departments d ON s.department_id=d.id
    $where ORDER BY s.student_id");
$stmt->execute($params);
$students = $stmt->fetchAll();

// Risk level function
function getRiskLevel($totalViolations) {
    if ($totalViolations >= 5) return ['label'=>'Critical','class'=>'danger'];
    if ($totalViolations >= 4) return ['label'=>'High','class'=>'warning'];
    if ($totalViolations >= 2) return ['label'=>'Moderate','class'=>'info'];
    return ['label'=>'Low','class'=>'success'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Records - MDSV</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
<!-- NAVBAR -->
<nav class="navbar navbar-dark bg-primary px-3">
  <span class="navbar-brand fw-bold">MDSV Admin</span>
  <div class="d-flex align-items-center gap-2">
    <span class="text-white small d-none d-md-inline"><?= htmlspecialchars($_SESSION['full_name']) ?></span>
    <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
  </div>
</nav>

<div class="container-fluid">
<div class="row">
<!-- SIDEBAR -->
<nav class="col-md-2 d-none d-md-block bg-light py-3" style="min-height:100vh">
  <ul class="nav flex-column">
    <li><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
    <li><a class="nav-link active fw-bold" href="student-records.php"><i class="bi bi-people"></i> Student Records</a></li>
    <li><a class="nav-link" href="violation-records.php"><i class="bi bi-exclamation-triangle"></i> Violation Records</a></li>
    <li><a class="nav-link" href="disciplinary-action.php"><i class="bi bi-shield-exclamation"></i> Disciplinary Action</a></li>
    <li><a class="nav-link" href="risk-level.php"><i class="bi bi-bar-chart"></i> Risk Level</a></li>
    <li><a class="nav-link" href="student-appeals.php"><i class="bi bi-chat-left-text"></i> Student Appeals</a></li>
    <li><a class="nav-link" href="data-backup.php"><i class="bi bi-download"></i> Data Backup</a></li>
    <li><a class="nav-link" href="user-management.php"><i class="bi bi-person-gear"></i> User Management</a></li>
  </ul>
</nav>
<!-- MOBILE MENU -->
<div class="d-md-none p-2 bg-light w-100">
  <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#mob2">
    <i class="bi bi-list"></i> Menu
  </button>
  <div class="collapse" id="mob2">
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
    <h5 class="mb-0">Student Records</h5>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStudentModal">
      <i class="bi bi-plus"></i> Add Student
    </button>
  </div>

  <?php if(isset($_SESSION['msg'])): ?>
    <div class="alert alert-success"><?= $_SESSION['msg'] ?><?php unset($_SESSION['msg']); ?></div>
  <?php endif; ?>
  <?php if(isset($_SESSION['err'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['err'] ?><?php unset($_SESSION['err']); ?></div>
  <?php endif; ?>

  <!-- SEARCH & FILTER -->
  <form method="GET" class="row g-2 mb-3">
    <div class="col-md-3 col-6">
      <input type="text" name="search" class="form-control form-control-sm" placeholder="Search ID or Name"
             value="<?= htmlspecialchars($search) ?>" id="searchInput">
    </div>
    <div class="col-md-2 col-6">
      <select name="course" class="form-select form-select-sm">
        <option value="">All Courses</option>
        <?php foreach($courses as $c): ?>
        <option value="<?=$c['id']?>" <?=$filterCourse==$c['id']?'selected':''?>><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2 col-4">
      <select name="year_level" class="form-select form-select-sm">
        <option value="">All Years</option>
        <?php for($i=1;$i<=4;$i++): ?>
        <option value="<?=$i?>" <?=$filterYear==$i?'selected':''?>>Year <?=$i?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-md-2 col-4">
      <select name="dept" class="form-select form-select-sm">
        <option value="">All Depts</option>
        <?php foreach($departments as $d): ?>
        <option value="<?=$d['id']?>" <?=$filterDept==$d['id']?'selected':''?>><?= htmlspecialchars($d['code']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-1 col-2">
      <button type="submit" class="btn btn-primary btn-sm w-100">Go</button>
    </div>
    <div class="col-md-1 col-2">
      <a href="student-records.php" class="btn btn-secondary btn-sm w-100">Reset</a>
    </div>
  </form>

  <!-- TABLE -->
  <div class="table-responsive">
  <table class="table table-bordered table-sm table-hover">
    <thead class="table-dark">
      <tr>
        <th>Student ID</th><th>Full Name</th><th>Course</th>
        <th>Year</th><th>Department</th><th>Risk Level</th><th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach($students as $st):
      $risk = getRiskLevel($st['total_violations']);
    ?>
    <tr>
      <td><?= htmlspecialchars($st['student_id']) ?></td>
      <td><?= htmlspecialchars($st['full_name']) ?></td>
      <td><?= htmlspecialchars($st['course_name']) ?></td>
      <td>Year <?= $st['year_level'] ?></td>
      <td><?= htmlspecialchars($st['dept_name']) ?></td>
      <td><span class="badge bg-<?= $risk['class'] ?>"><?= $risk['label'] ?></span></td>
      <td>
        <button class="btn btn-info btn-sm" onclick='viewStudent(<?= json_encode($st) ?>)'>View</button>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($students)): ?>
    <tr><td colspan="7" class="text-center text-muted">No students found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
</main>
</div>
</div>

<!-- ADD STUDENT MODAL -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <input type="hidden" name="action" value="add">
      <div class="modal-header"><h5 class="modal-title">Add Student</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">Student ID</label>
          <input type="text" name="student_id" id="addSID" class="form-control" maxlength="9" placeholder="000-00000" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Full Name</label>
          <input type="text" name="full_name" class="form-control" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Department</label>
          <select name="department_id" id="addDept" class="form-select" required onchange="filterCourses(this.value,'addCourse')">
            <option value="">Select Department</option>
            <?php foreach($departments as $d): ?>
            <option value="<?=$d['id']?>"><?= htmlspecialchars($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label">Course</label>
          <select name="course_id" id="addCourse" class="form-select" required>
            <option value="">Select Course</option>
            <?php foreach($courses as $c): ?>
            <option value="<?=$c['id']?>" data-dept="<?= $c['id'] /* we pass dept via JS */ ?>" class="course-opt"><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label">Year Level</label>
          <select name="year_level" class="form-select" required>
            <?php for($i=1;$i<=4;$i++): ?><option value="<?=$i?>">Year <?=$i?></option><?php endfor; ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Student</button>
      </div>
    </form>
  </div>
</div>

<!-- VIEW STUDENT MODAL -->
<div class="modal fade" id="viewStudentModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Student Information</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="viewModalBody"></div>
      <div class="modal-footer">
        <button class="btn btn-warning btn-sm" id="editBtnModal">Edit Student</button>
        <button class="btn btn-danger btn-sm" id="deleteBtnModal">Delete Student</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- EDIT STUDENT MODAL -->
<div class="modal fade" id="editStudentModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="student_id" id="editSID">
      <div class="modal-header"><h5 class="modal-title">Edit Student</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="mb-2"><label class="form-label">Full Name</label>
          <input type="text" name="full_name" id="editName" class="form-control" required></div>
        <div class="mb-2"><label class="form-label">Department</label>
          <select name="department_id" id="editDept" class="form-select" required>
            <?php foreach($departments as $d): ?>
            <option value="<?=$d['id']?>"><?= htmlspecialchars($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2"><label class="form-label">Course</label>
          <select name="course_id" id="editCourse" class="form-select" required>
            <?php foreach($courses as $c): ?>
            <option value="<?=$c['id']?>"><?= htmlspecialchars($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2"><label class="form-label">Year Level</label>
          <select name="year_level" id="editYear" class="form-select" required>
            <?php for($i=1;$i<=4;$i++): ?><option value="<?=$i?>">Year <?=$i?></option><?php endfor; ?>
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

<!-- DELETE MODAL -->
<div class="modal fade" id="deleteStudentModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="student_id" id="deleteSID">
      <div class="modal-header bg-danger text-white"><h5 class="modal-title">Confirm Deletion</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <p>This will permanently delete the student record. Enter deletion password to confirm.</p>
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
// All courses with dept mapping
const allCourses = <?= json_encode(array_map(fn($c)=>['id'=>$c['id'],'name'=>$c['name'],'dept'=>$c['id']], $courses)) ?>;
const coursesRaw = <?= json_encode($courses) ?>;

// Student ID auto-dash
document.getElementById('addSID').addEventListener('input', function(){
    let v = this.value.replace(/[^0-9]/g,'');
    if (v.length > 3) v = v.substring(0,3) + '-' + v.substring(3,8);
    this.value = v;
});

function filterCourses(deptId, selectId) {
    // We need course->department mapping
    fetch('ajax/get-courses.php?dept=' + deptId)
        .then(r => r.json()).then(courses => {
            const sel = document.getElementById(selectId);
            sel.innerHTML = '<option value="">Select Course</option>';
            courses.forEach(c => {
                sel.innerHTML += `<option value="${c.id}">${c.name}</option>`;
            });
        });
}

let currentStudent = null;

function viewStudent(st) {
    currentStudent = st;
    // Load violation records for this student
    fetch('ajax/student-violations.php?student_id=' + encodeURIComponent(st.student_id))
        .then(r => r.json()).then(viols => {
            let vRows = '';
            viols.forEach(v => {
                vRows += `<tr>
                    <td>${v.date_submitted}</td>
                    <td><span class="badge bg-${v.category==='minor'?'primary':'danger'}">${v.category}</span></td>
                    <td>${v.violation}</td>
                    <td>${v.description ?? '-'}</td>
                    <td><span class="badge bg-${v.status==='completed'?'success':v.status==='ongoing'?'warning':'secondary'}">${v.status}</span></td>
                    <td>${v.sanction ?? '-'}</td>
                    <td><a href="violation-records.php?highlight=${v.id}" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>`;
            });

            document.getElementById('viewModalBody').innerHTML = `
                <h6>Student Information</h6>
                <table class="table table-sm mb-3">
                  <tr><th>Student ID</th><td>${st.student_id}</td></tr>
                  <tr><th>Full Name</th><td>${st.full_name}</td></tr>
                  <tr><th>Course</th><td>${st.course_name}</td></tr>
                  <tr><th>Year Level</th><td>Year ${st.year_level}</td></tr>
                  <tr><th>Department</th><td>${st.dept_name}</td></tr>
                </table>
                <h6>Violation Records</h6>
                ${viols.length === 0 ? '<p class="text-muted">No violations recorded.</p>' :
                `<div class="table-responsive"><table class="table table-sm table-bordered">
                  <thead><tr><th>Date</th><th>Type</th><th>Violation</th><th>Description</th><th>Status</th><th>Sanction</th><th></th></tr></thead>
                  <tbody>${vRows}</tbody>
                </table></div>`}
            `;

            document.getElementById('editBtnModal').onclick = () => openEdit(st);
            document.getElementById('deleteBtnModal').onclick = () => openDelete(st.student_id);
            new bootstrap.Modal(document.getElementById('viewStudentModal')).show();
        });
}

function openEdit(st) {
    bootstrap.Modal.getInstance(document.getElementById('viewStudentModal'))?.hide();
    document.getElementById('editSID').value  = st.student_id;
    document.getElementById('editName').value = st.full_name;
    document.getElementById('editDept').value = st.department_id;
    document.getElementById('editCourse').value = st.course_id;
    document.getElementById('editYear').value = st.year_level;
    new bootstrap.Modal(document.getElementById('editStudentModal')).show();
}

function openDelete(sid) {
    bootstrap.Modal.getInstance(document.getElementById('viewStudentModal'))?.hide();
    document.getElementById('deleteSID').value = sid;
    new bootstrap.Modal(document.getElementById('deleteStudentModal')).show();
}
</script>
</body>
</html>