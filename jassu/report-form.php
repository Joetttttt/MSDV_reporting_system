<?php
session_start();
require_once '../db/connection.php';
$allowedRoles = ['teacher','csu','jassu'];
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], $allowedRoles)) {
    header('Location: ../index.php'); exit;
}

$role = $_SESSION['role'];
$msg = $err = '';

// SUBMIT REPORT
if (isset($_POST['action']) && $_POST['action'] === 'submit_report') {
    $sid         = trim($_POST['student_id']);
    $category    = $_POST['category'];
    $violation   = trim($_POST['violation']);
    $description = trim($_POST['description']);

    // Validate student exists
    $sCheck = $pdo->prepare("SELECT * FROM students WHERE student_id=?");
    $sCheck->execute([$sid]);
    $student = $sCheck->fetch();

    if (!$student) {
        $err = 'Student ID not found in records.';
    } else {
        // Handle evidence upload
        $evidencePath = null;
        if (!empty($_FILES['evidence']['name'])) {
            $ext = pathinfo($_FILES['evidence']['name'], PATHINFO_EXTENSION);
            $fname = 'uploads/evidence_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['evidence']['tmp_name'], '../' . $fname);
            $evidencePath = $fname;
        }

        // Handle face capture (base64 from JS)
        $facePath = null;
        if (!empty($_POST['face_capture_data'])) {
            $imgData = $_POST['face_capture_data'];
            $imgData = str_replace('data:image/png;base64,', '', $imgData);
            $imgData = base64_decode($imgData);
            $facePath = 'uploads/face_' . time() . '.png';
            file_put_contents('../' . $facePath, $imgData);
        }

        // Handle signature (base64 from canvas)
        $sigPath = null;
        if (!empty($_POST['signature_data'])) {
            $sigData = $_POST['signature_data'];
            $sigData = str_replace('data:image/png;base64,', '', $sigData);
            $sigData = base64_decode($sigData);
            $sigPath = 'uploads/sig_' . time() . '.png';
            file_put_contents('../' . $sigPath, $sigData);
        }

        // Insert violation
        $pdo->prepare("INSERT INTO violations (student_id,reporter_id,category,violation,description,evidence_path,face_capture_path,signature_path) VALUES (?,?,?,?,?,?,?,?)")
            ->execute([$sid, $_SESSION['user_id'], $category, $violation, $description, $evidencePath, $facePath, $sigPath]);

        $violationId = $pdo->lastInsertId();

        // Auto-assign sanction
        $minorCount = (int)$pdo->prepare("SELECT COUNT(*) FROM violations WHERE student_id=? AND category='minor'")->execute([$sid]) ? $pdo->query("SELECT COUNT(*) FROM violations WHERE student_id='$sid' AND category='minor'")->fetchColumn() : 0;
        $majorCount = (int)$pdo->query("SELECT COUNT(*) FROM violations WHERE student_id='$sid' AND category='major'")->fetchColumn();

        $sanction = '';
        if ($category === 'minor') {
            $offense = $minorCount;
            if ($offense == 1) $sanction = 'First Offense: Verbal Warning and Counseling';
            elseif ($offense == 2) $sanction = 'Second Offense: Written Warning and Reflective Essay';
            elseif ($offense == 3) $sanction = 'Third Offense: Community Service 5-10 hours and Parental Notification';
            elseif ($offense == 4) $sanction = 'Fourth Offense: Short-term Suspension 1-3 days and Mandatory Workshop';
            elseif ($offense >= 5) $sanction = 'Fifth Offense: Long-term Suspension 1 week and Disciplinary Probation';
        } else {
            $offense = $majorCount;
            if ($offense == 1) {
                if ($violation === 'Academic Dishonesty') $sanction = 'First Major Offense (Academic Dishonesty): Failing grade for the course and Mandatory Ethics Workshop';
                else $sanction = 'First Major Offense: Suspension (1 week to 1 month) and Mandatory Counseling';
            } elseif ($offense == 2) {
                if ($violation === 'Academic Dishonesty') $sanction = 'Second Major Offense (Academic Dishonesty): Suspension for 1 Semester';
                else $sanction = 'Second Major Offense: Suspension (1 month to 1 semester) and Extended Counseling';
            } elseif ($offense >= 3) {
                if ($violation === 'Academic Dishonesty') $sanction = 'Third Major Offense (Academic Dishonesty): Expulsion';
                else $sanction = 'Third Major Offense: Expulsion and Notification to Authorities if Applicable';
            }
        }

        $pdo->prepare("INSERT INTO disciplinary_actions (violation_id,student_id,sanction,status) VALUES (?,?,?,'pending')")
            ->execute([$violationId, $sid, $sanction]);

        // Notify admin
        $adminId = $pdo->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->fetchColumn();
        $pdo->prepare("INSERT INTO notifications (user_id,message,link) VALUES (?,?,?)")
            ->execute([$adminId, "New violation reported for student $sid by " . $_SESSION['full_name'], '../admin/violation-records.php']);

        $_SESSION['report_success'] = true;
        header('Location: dashboard.php'); exit;
    }
}

// Get student info via AJAX
if (isset($_GET['get_student'])) {
    header('Content-Type: application/json');
    $sid = trim($_GET['get_student']);
    $s = $pdo->prepare("SELECT s.*,c.name as cname,d.name as dname FROM students s JOIN courses c ON s.course_id=c.id JOIN departments d ON s.department_id=d.id WHERE s.student_id=?");
    $s->execute([$sid]);
    echo json_encode($s->fetch() ?: null);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Report Violation - MDSV</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="manifest" href="../manifest.json">
</head>
<body>
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
    <li><a class="nav-link" href="dashboard.php"><i class="bi bi-house"></i> Dashboard</a></li>
    <li><a class="nav-link active fw-bold" href="report-form.php"><i class="bi bi-file-earmark-plus"></i> Report Violation</a></li>
    <li><a class="nav-link" href="my-reports.php"><i class="bi bi-list-ul"></i> My Reports</a></li>
  </ul>
</nav>
<div class="d-md-none p-2 bg-light w-100">
  <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#mobRF"><i class="bi bi-list"></i> Menu</button>
  <div class="collapse" id="mobRF">
    <ul class="nav flex-column mt-1">
      <li><a class="nav-link" href="dashboard.php">Dashboard</a></li>
      <li><a class="nav-link" href="report-form.php">Report Violation</a></li>
      <li><a class="nav-link" href="my-reports.php">My Reports</a></li>
    </ul>
  </div>
</div>

<main class="col-md-10 px-3 py-3">
  <h5 class="mb-3">Report a Violation</h5>
  <?php if($err): ?><div class="alert alert-danger"><?= $err ?></div><?php endif; ?>

  <form method="POST" enctype="multipart/form-data" id="reportForm">
    <input type="hidden" name="action" value="submit_report">
    <input type="hidden" name="face_capture_data" id="faceCaptureData">
    <input type="hidden" name="signature_data" id="signatureData">

    <!-- STEP 1: STUDENT INFO -->
    <div class="card mb-3">
      <div class="card-header"><strong>Step 1: Student Information</strong></div>
      <div class="card-body">
        <div class="row g-2">
          <div class="col-md-4">
            <label class="form-label">Student ID</label>
            <input type="text" name="student_id" id="studentIDInput" class="form-control" maxlength="9" placeholder="000-00000" required>
          </div>
          <div class="col-md-8" id="studentInfoBox" style="display:none">
            <div class="alert alert-info mb-0 py-2">
              <strong id="siName"></strong><br>
              <small>Course: <span id="siCourse"></span> | Year: <span id="siYear"></span> | Dept: <span id="siDept"></span></small>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- STEP 2: VIOLATION DETAILS -->
    <div class="card mb-3">
      <div class="card-header"><strong>Step 2: Violation Details</strong></div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label">Category</label>
          <div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="category" id="catMinor" value="minor" required onchange="loadViolations('minor')">
              <label class="form-check-label" for="catMinor">Minor</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="category" id="catMajor" value="major" onchange="loadViolations('major')">
              <label class="form-check-label" for="catMajor">Major</label>
            </div>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Violation</label>
          <select name="violation" id="violationSelect" class="form-select" required>
            <option value="">Select category first</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Description / Additional Details</label>
          <textarea name="description" class="form-control" rows="3" placeholder="Provide additional details..."></textarea>
        </div>
      </div>
    </div>

    <!-- STEP 3: EVIDENCE -->
    <div class="card mb-3">
      <div class="card-header"><strong>Step 3: Evidence</strong></div>
      <div class="card-body">
        <div class="mb-2">
          <label class="form-label">Upload Image Evidence</label>
          <input type="file" name="evidence" class="form-control" accept="image/*">
        </div>
        <p class="text-muted small">or</p>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="openCamera('evidence')">
          <i class="bi bi-camera"></i> Take Photo as Evidence
        </button>
      </div>
    </div>

    <!-- STEP 4: STUDENT VERIFICATION -->
    <div class="card mb-3">
      <div class="card-header"><strong>Step 4: Student Verification</strong></div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Face Capture</label>
            <p class="text-muted small">Take a photo of the student's face as verification.</p>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="openCamera('face')">
              <i class="bi bi-person-bounding-box"></i> Capture Face
            </button>
            <div id="faceCapturePreview" class="mt-2"></div>
          </div>
          <div class="col-md-6">
            <label class="form-label">E-Signature</label>
            <p class="text-muted small">Student signs below.</p>
            <canvas id="signatureCanvas" width="300" height="120"
                    style="border:1px solid #ccc;background:#fff;touch-action:none;max-width:100%"></canvas>
            <div class="mt-1">
              <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearSignature()">Clear</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- SUBMIT -->
    <button type="button" class="btn btn-success w-100" onclick="confirmSubmit()">
      <i class="bi bi-send"></i> Submit Report
    </button>
  </form>
</main>
</div></div>

<!-- CAMERA MODAL -->
<div class="modal fade" id="cameraModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title" id="cameraModalTitle">Camera</h5>
        <button type="button" class="btn-close" onclick="stopCamera()"></button></div>
      <div class="modal-body text-center">
        <video id="cameraStream" autoplay playsinline style="max-width:100%;border-radius:8px"></video>
        <canvas id="cameraCanvas" style="display:none"></canvas>
        <div id="capturePreview" style="display:none">
          <img id="capturedImg" style="max-width:100%;border-radius:8px">
          <div class="mt-2 d-flex gap-2 justify-content-center">
            <button type="button" class="btn btn-secondary" onclick="retakePhoto()">Try Again</button>
            <button type="button" class="btn btn-success" onclick="confirmPhoto()">Confirm</button>
          </div>
        </div>
        <div id="captureBtn">
          <button type="button" class="btn btn-primary mt-2" onclick="capturePhoto()">
            <i class="bi bi-camera"></i> Capture
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- CONFIRM SUBMIT MODAL -->
<div class="modal fade" id="confirmModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Confirm Submission</h5></div>
      <div class="modal-body">
        <p>By submitting this report, you confirm that all information provided is <strong>accurate and truthful</strong>.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" onclick="doSubmit()">Confirm & Submit</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Student ID auto-dash
document.getElementById('studentIDInput').addEventListener('input', function(){
    let v = this.value.replace(/[^0-9]/g,'');
    if (v.length > 3) v = v.substring(0,3)+'-'+v.substring(3,8);
    this.value = v;
});

// Auto-fetch student info
document.getElementById('studentIDInput').addEventListener('blur', function(){
    const sid = this.value.trim();
    if (sid.length < 5) return;
    fetch('report-form.php?get_student=' + encodeURIComponent(sid))
        .then(r => r.json()).then(s => {
            if (s) {
                document.getElementById('siName').textContent = s.full_name;
                document.getElementById('siCourse').textContent = s.cname;
                document.getElementById('siYear').textContent = 'Year ' + s.year_level;
                document.getElementById('siDept').textContent = s.dname;
                document.getElementById('studentInfoBox').style.display = 'block';
            } else {
                document.getElementById('studentInfoBox').style.display = 'none';
            }
        });
});

// Violations list
const minorViolations = ['Disruptive Behavior','Littering','Dress Code','Unapproved Absences',
    'Inappropriate Language','Unauthorized Use of College Property','Smoking on Campus',
    'Failure to Display ID','Noise Violations','Minor Vandalism'];
const majorViolations = ['Academic Dishonesty','Theft','Physical Violence','Substance Abuse',
    'Harassment','Unauthorized Entry','Forgery','Moral Infractions','Weapons Possession',
    'Cyber Bullying','Hazing','Major Dishonesty','Extortion','Sexual Misconduct'];

function loadViolations(cat) {
    const sel = document.getElementById('violationSelect');
    sel.innerHTML = '<option value="">Select violation</option>';
    const list = cat === 'minor' ? minorViolations : majorViolations;
    list.forEach(v => sel.innerHTML += `<option value="${v}">${v}</option>`);
}

// CAMERA
let stream = null;
let cameraMode = '';

function openCamera(mode) {
    cameraMode = mode;
    document.getElementById('cameraModalTitle').textContent = mode === 'face' ? 'Capture Student Face' : 'Take Photo Evidence';
    document.getElementById('cameraStream').style.display = 'block';
    document.getElementById('captureBtn').style.display = 'block';
    document.getElementById('capturePreview').style.display = 'none';
    navigator.mediaDevices.getUserMedia({video: true}).then(s => {
        stream = s;
        document.getElementById('cameraStream').srcObject = s;
        new bootstrap.Modal(document.getElementById('cameraModal')).show();
    }).catch(() => alert('Camera not accessible.'));
}

function capturePhoto() {
    const video = document.getElementById('cameraStream');
    const canvas = document.getElementById('cameraCanvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    document.getElementById('capturedImg').src = canvas.toDataURL('image/png');
    document.getElementById('cameraStream').style.display = 'none';
    document.getElementById('captureBtn').style.display = 'none';
    document.getElementById('capturePreview').style.display = 'block';
}

function retakePhoto() {
    document.getElementById('cameraStream').style.display = 'block';
    document.getElementById('captureBtn').style.display = 'block';
    document.getElementById('capturePreview').style.display = 'none';
}

function confirmPhoto() {
    const data = document.getElementById('cameraCanvas').toDataURL('image/png');
    if (cameraMode === 'face') {
        document.getElementById('faceCaptureData').value = data;
        document.getElementById('faceCapturePreview').innerHTML =
            `<img src="${data}" style="max-height:120px;border-radius:8px" class="mt-1">`;
    }
    stopCamera();
}

function stopCamera() {
    if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
    bootstrap.Modal.getInstance(document.getElementById('cameraModal'))?.hide();
}

// SIGNATURE
const sigCanvas = document.getElementById('signatureCanvas');
const sigCtx = sigCanvas.getContext('2d');
let signing = false;

function getPos(e) {
    const rect = sigCanvas.getBoundingClientRect();
    const touch = e.touches ? e.touches[0] : e;
    return { x: touch.clientX - rect.left, y: touch.clientY - rect.top };
}

sigCanvas.addEventListener('mousedown', e => { signing=true; sigCtx.beginPath(); const p=getPos(e); sigCtx.moveTo(p.x,p.y); });
sigCanvas.addEventListener('mousemove', e => { if(!signing) return; const p=getPos(e); sigCtx.lineTo(p.x,p.y); sigCtx.stroke(); });
sigCanvas.addEventListener('mouseup', () => signing = false);
sigCanvas.addEventListener('touchstart', e => { e.preventDefault(); signing=true; sigCtx.beginPath(); const p=getPos(e); sigCtx.moveTo(p.x,p.y); });
sigCanvas.addEventListener('touchmove', e => { e.preventDefault(); if(!signing) return; const p=getPos(e); sigCtx.lineTo(p.x,p.y); sigCtx.stroke(); });
sigCanvas.addEventListener('touchend', () => signing = false);

function clearSignature() {
    sigCtx.clearRect(0,0,sigCanvas.width,sigCanvas.height);
}

function confirmSubmit() {
    document.getElementById('signatureData').value = sigCanvas.toDataURL('image/png');
    new bootstrap.Modal(document.getElementById('confirmModal')).show();
}

function doSubmit() {
    document.getElementById('reportForm').submit();
}
</script>
</body>
</html>