<?php

session_start();

include("../config/database.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

if ($_SESSION['role'] != 'admin') {
    header("Location: ../index.html");
    exit();
}

$query = "SELECT * FROM users
          WHERE role != 'admin'
          ORDER BY id DESC";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html>
<head>

<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">

<title>User Management</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

.sidebar{
    height:100vh;
    background:#212529;
}

.sidebar a{
    color:white;
    text-decoration:none;
    display:block;
    padding:15px;
}

.sidebar a:hover{
    background:#343a40;
}

</style>

</head>

<body>

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-2 sidebar p-0">

            <h4 class="text-white text-center py-3">
                ADMIN
            </h4>

            <a href="dashboard.php">Dashboard</a>
            <a href="students.php">Student Records</a>
            <a href="reports.php">Violation Reports</a>
            <a href="disciplinary_actions.php">Disciplinary Actions</a>
            <a href="risk_level_indicator.php">
    Risk Level Indicator
</a>
<a href="backup.php">Backup & Export</a>

            
            <a href="users.php">User Management</a>
            
            
            <a href="../auth/logout.php">Logout</a>
        </div>

        <!-- CONTENT -->
        <div class="col-md-10 p-4">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h2>User Management</h2>

                <button class="btn btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#addUserModal">

                    Add User

                </button>

            </div>

            <!-- TABLE -->
            <table class="table table-bordered table-striped">

                <thead class="table-dark">

                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th width="300">Actions</th>
                    </tr>

                </thead>

                <tbody>

                <?php while($row = mysqli_fetch_assoc($result)) { ?>

                    <tr>

                        <td><?= $row['id']; ?></td>
                        <td><?= $row['fullname']; ?></td>
                        <td><?= $row['username']; ?></td>
                        <td><?= $row['email']; ?></td>
                        <td><?= strtoupper($row['role']); ?></td>

                        <td>

                            <!-- EDIT -->
                            <button
                                class="btn btn-warning btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#editModal<?= $row['id']; ?>">
                                Edit
                            </button>

                            <!-- DELETE -->
                            <a
                                href="delete_user.php?id=<?= $row['id']; ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Delete this user?')">

                                Delete

                            </a>

                            <!-- CHANGE PASSWORD -->
                            <button
                                class="btn btn-secondary btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#passwordModal<?= $row['id']; ?>">

                                Change Password

                            </button>

                        </td>

                    </tr>

                    <!-- EDIT MODAL -->
                    <div class="modal fade" id="editModal<?= $row['id']; ?>">

                        <div class="modal-dialog">

                            <div class="modal-content">

                                <form action="update_user.php" method="POST">

                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            Edit User
                                        </h5>
                                    </div>

                                    <div class="modal-body">

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= $row['id']; ?>"
                                        >

                                        <div class="mb-3">

                                            <label>Full Name</label>

                                            <input
                                                type="text"
                                                name="fullname"
                                                class="form-control"
                                                value="<?= $row['fullname']; ?>"
                                                required
                                            >

                                        </div>

                                        <div class="mb-3">

                                            <label>Username</label>

                                            <input
                                                type="text"
                                                name="username"
                                                class="form-control"
                                                value="<?= $row['username']; ?>"
                                                required
                                            >

                                        </div>

                                        <div class="mb-3">

                                            <label>Email</label>

                                            <input
                                                type="email"
                                                name="email"
                                                class="form-control"
                                                value="<?= $row['email']; ?>"
                                                required
                                            >

                                        </div>

                                        <div class="mb-3">

                                            <label>Role</label>

                                            <select name="role"
                                                    class="form-control"
                                                    required>

                                                <option value="teacher"
                                                <?= $row['role'] == 'teacher' ? 'selected' : ''; ?>>
                                                    Teacher
                                                </option>

                                                <option value="csu"
                                                <?= $row['role'] == 'csu' ? 'selected' : ''; ?>>
                                                    CSU
                                                </option>

                                                <option value="jassu"
                                                <?= $row['role'] == 'jassu' ? 'selected' : ''; ?>>
                                                    JASSU
                                                </option>

                                            </select>

                                        </div>

                                    </div>

                                    <div class="modal-footer">

                                        <button type="submit"
                                                class="btn btn-success">

                                            Update User

                                        </button>

                                    </div>

                                </form>

                            </div>

                        </div>

                    </div>

                    <!-- CHANGE PASSWORD MODAL -->
                    <div class="modal fade" id="passwordModal<?= $row['id']; ?>">

                        <div class="modal-dialog">

                            <div class="modal-content">

                                <form action="change_password.php" method="POST">

                                    <div class="modal-header">

                                        <h5 class="modal-title">
                                            Change Password
                                        </h5>

                                    </div>

                                    <div class="modal-body">

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= $row['id']; ?>"
                                        >

                                        <div class="mb-3">

                                            <label>New Password</label>

                                            <input
                                                type="password"
                                                name="password"
                                                class="form-control"
                                                required
                                            >

                                        </div>

                                    </div>

                                    <div class="modal-footer">

                                        <button type="submit"
                                                class="btn btn-primary">

                                            Update Password

                                        </button>

                                    </div>

                                </form>

                            </div>

                        </div>

                    </div>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- ADD USER MODAL -->
<div class="modal fade" id="addUserModal">

    <div class="modal-dialog">

        <div class="modal-content">

            <form action="add_user.php" method="POST">

                <div class="modal-header">

                    <h5 class="modal-title">
                        Add User
                    </h5>

                </div>

                <div class="modal-body">

                    <div class="mb-3">

                        <label>Full Name</label>

                        <input type="text"
                               name="fullname"
                               class="form-control"
                               required>

                    </div>

                    <div class="mb-3">

                        <label>Username</label>

                        <input type="text"
                               name="username"
                               class="form-control"
                               required>

                    </div>

                    <div class="mb-3">

                        <label>Email</label>

                        <input type="email"
                               name="email"
                               class="form-control"
                               required>

                    </div>

                    <div class="mb-3">

                        <label>Password</label>

                        <input type="password"
                               name="password"
                               class="form-control"
                               required>

                    </div>

                    <div class="mb-3">

                        <label>Role</label>

                        <select name="role"
                                class="form-control"
                                required>

                            <option value="">
                                Select Role
                            </option>

                            <option value="teacher">
                                Teacher
                            </option>

                            <option value="csu">
                                CSU
                            </option>

                            <option value="jassu">
                                JASSU
                            </option>

                        </select>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="submit"
                            class="btn btn-success">

                        Save User

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

window.addEventListener('pageshow', function(event){

    if(event.persisted){
        window.location.reload();
    }

});

</script>

</body>
</html>