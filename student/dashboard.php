<?php
session_start();
include("../config/database.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

if ($_SESSION['role'] != 'student') {
    header("Location: ../index.html");
    exit();
}

$username = mysqli_real_escape_string($conn, $_SESSION['username']);
$fullname = mysqli_real_escape_string($conn, $_SESSION['fullname']);

/* Ensure appeal table exists */
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS appeals (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    violation_id INT(11) NOT NULL,
    student_id VARCHAR(50) NOT NULL,
    student_name VARCHAR(100) NOT NULL,
    appeal_reason TEXT NOT NULL,
    appeal_status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

/* Student profile lookup */
$profileResult = mysqli_query($conn, "SELECT * FROM students WHERE student_id='$username' OR fullname='$fullname' LIMIT 1");
$studentProfile = mysqli_fetch_assoc($profileResult);

/* Violations for student */
$violationsQuery = "SELECT * FROM violations WHERE student_id='$username' OR student_name='$fullname' ORDER BY created_at DESC";
$violationsResult = mysqli_query($conn, $violationsQuery);
$totalViolations = mysqli_num_rows($violationsResult);

$pendingViolations = mysqli_num_rows(mysqli_query($conn, "$violationsQuery AND case_status='Pending'"));
$completedViolations = mysqli_num_rows(mysqli_query($conn, "$violationsQuery AND case_status='Completed'"));

/* Appeals for student */
$appealsQuery = "SELECT * FROM appeals WHERE student_id='$username' OR student_name='$fullname' ORDER BY created_at DESC";
$appealsResult = mysqli_query($conn, $appealsQuery);
$totalAppeals = mysqli_num_rows($appealsResult);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; background: #eef2f7; font-family: 'Inter', sans-serif; }
        .sidebar { width: 240px; background: #0d2254; min-height: 100vh; color: #fff; }
        .sidebar a { color: rgba(255,255,255,.9); text-decoration: none; display: block; padding: 14px 20px; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,.08); color: #fff; }
        .main { margin-left: 240px; }
        .card-compact { border-radius: 16px; }
        .badge-status { font-size: .8rem; padding: .45em .8em; }
        .table-responsive { overflow-x: auto; }
        .profile-card { border-radius: 18px; }
        .appeal-card { border-radius: 18px; }
        .status-pending { background: #fef3c7; color: #a16207; }
        .status-completed { background: #dcfce7; color: #166534; }
        .status-requested { background: #dbeafe; color: #1d4ed8; }
    </style>
</head>
<body>
<div class="d-flex">
    <aside class="sidebar d-flex flex-column">
        <div class="p-4 border-bottom border-white/10">
            <h4>Student Panel</h4>
            <p class="text-muted mb-0">Welcome, <?= htmlspecialchars($_SESSION['fullname']); ?></p>
        </div>
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="dashboard.php#violations">My Violations</a>
        <a href="dashboard.php#appeals">My Appeals</a>
        <a href="../auth/logout.php">Logout</a>
    </aside>

    <main class="main flex-grow-1 p-4">
        <div class="d-flex flex-column gap-3">
            <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-start">
                <div>
                    <h2 class="fw-bold">Student Dashboard</h2>
                    <p class="text-secondary">Review your violations and submit appeals directly from your account.</p>
                </div>
                <div>
                    <a href="#appeals" class="btn btn-primary">Submit an Appeal</a>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="card card-compact p-3 shadow-sm bg-white">
                        <div class="text-uppercase text-muted small">Total violations</div>
                        <div class="h2 mb-0"><?= $totalViolations; ?></div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card card-compact p-3 shadow-sm bg-white">
                        <div class="text-uppercase text-muted small">Pending cases</div>
                        <div class="h2 mb-0"><?= $pendingViolations; ?></div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card card-compact p-3 shadow-sm bg-white">
                        <div class="text-uppercase text-muted small">Completed</div>
                        <div class="h2 mb-0"><?= $completedViolations; ?></div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card card-compact p-3 shadow-sm bg-white">
                        <div class="text-uppercase text-muted small">Appeals filed</div>
                        <div class="h2 mb-0"><?= $totalAppeals; ?></div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="card profile-card shadow-sm p-4 bg-white">
                        <h5 class="mb-3">Student Profile</h5>
                        <dl class="row mb-0">
                            <dt class="col-5 text-secondary">Name</dt>
                            <dd class="col-7"><?= htmlspecialchars($fullname); ?></dd>
                            <dt class="col-5 text-secondary">Username</dt>
                            <dd class="col-7"><?= htmlspecialchars($username); ?></dd>
                            <dt class="col-5 text-secondary">Student ID</dt>
                            <dd class="col-7"><?= htmlspecialchars($studentProfile['student_id'] ?? 'N/A'); ?></dd>
                            <dt class="col-5 text-secondary">Course</dt>
                            <dd class="col-7"><?= htmlspecialchars($studentProfile['course'] ?? 'N/A'); ?></dd>
                            <dt class="col-5 text-secondary">Year Level</dt>
                            <dd class="col-7"><?= htmlspecialchars($studentProfile['year_level'] ?? 'N/A'); ?></dd>
                        </dl>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card appeal-card shadow-sm p-4 bg-white" id="appeals">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Submit Appeal</h5>
                            <span class="text-secondary small">Choose a violation and explain why it should be reviewed</span>
                        </div>
                        <form action="save_appeal.php" method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Violation</label>
                                    <select class="form-select" name="violation_id" required>
                                        <option value="">Select a violation</option>
                                        <?php while ($violationOption = mysqli_fetch_assoc($violationsResult)) { ?>
                                            <option value="<?= $violationOption['id']; ?>">
                                                #<?= $violationOption['id']; ?> — <?= htmlspecialchars($violationOption['violation_type']); ?> (<?= htmlspecialchars($violationOption['case_status']); ?>)
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Student ID</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($studentProfile['student_id'] ?? $username); ?>" readonly>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Appeal reason</label>
                                    <textarea class="form-control" name="appeal_reason" rows="4" placeholder="Explain why the case should be reviewed" required></textarea>
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary">Submit Appeal</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <section id="violations">
                <div class="card shadow-sm bg-white p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">My Violations</h5>
                        <span class="text-secondary small">Latest violations associated with your account</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Violation</th>
                                    <th>Status</th>
                                    <th>Case Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            mysqli_data_seek($violationsResult, 0);
                            while ($row = mysqli_fetch_assoc($violationsResult)) {
                                $statusClass = $row['case_status'] === 'Completed' ? 'status-completed' : 'status-pending';
                            ?>
                                <tr>
                                    <td><?= $row['id']; ?></td>
                                    <td><?= date('M d, Y', strtotime($row['created_at'])); ?></td>
                                    <td><?= htmlspecialchars($row['violation_category']); ?></td>
                                    <td><?= htmlspecialchars($row['violation_type']); ?></td>
                                    <td><span class="badge <?= $statusClass; ?> badge-status"><?= htmlspecialchars($row['status'] ?? $row['case_status']); ?></span></td>
                                    <td><span class="badge <?= $statusClass; ?> badge-status"><?= htmlspecialchars($row['case_status']); ?></span></td>
                                </tr>
                            <?php }
                            if ($totalViolations === 0) { ?>
                                <tr><td colspan="6" class="text-center text-muted">No violations found.</td></tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="appeals">
                <div class="card shadow-sm bg-white p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">My Appeals</h5>
                        <span class="text-secondary small">Review the status of submitted appeals</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Violation</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php while ($appeal = mysqli_fetch_assoc($appealsResult)) { ?>
                                <tr>
                                    <td><?= $appeal['id']; ?></td>
                                    <td>#<?= $appeal['violation_id']; ?></td>
                                    <td><?= htmlspecialchars(substr($appeal['appeal_reason'], 0, 80)); ?><?= strlen($appeal['appeal_reason']) > 80 ? '...' : ''; ?></td>
                                    <td><span class="badge <?= $appeal['appeal_status'] === 'Completed' ? 'status-completed' : ($appeal['appeal_status'] === 'Requested' ? 'status-requested' : 'status-pending'); ?> badge-status"><?= htmlspecialchars($appeal['appeal_status']); ?></span></td>
                                    <td><?= date('M d, Y', strtotime($appeal['created_at'])); ?></td>
                                </tr>
                            <?php }
                            if ($totalAppeals === 0) { ?>
                                <tr><td colspan="5" class="text-center text-muted">No appeals submitted yet.</td></tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </main>
</div>
</body>
</html>
