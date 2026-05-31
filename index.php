<?php
session_start();
include("config/database.php");

if(isset($_POST['login']))
{
    $username = mysqli_real_escape_string(
        $conn,
        $_POST['username']
    );

    $password = mysqli_real_escape_string(
        $conn,
        $_POST['password']
    );

    $query = mysqli_query(
        $conn,
        "SELECT * FROM users
         WHERE username='$username'
         AND password='$password'"
    );

    if(mysqli_num_rows($query) > 0)
    {
        $user = mysqli_fetch_assoc($query);

        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];

        if($user['role'] == 'admin')
        {
            header("Location: admin/dashboard.php");
        }
        elseif($user['role'] == 'teacher')
        {
            header("Location: teacher/dashboard.php");
        }
        elseif($user['role'] == 'csu')
        {
            header("Location: csu/dashboard.php");
        }
        elseif($user['role'] == 'jassu')
        {
            header("Location: jassu/dashboard.php");
        }
        elseif($user['role'] == 'student')
        {
            header("Location: student/dashboard.php");
        }

        exit();
    }
    else
    {
        $error = "Invalid Username or Password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>MSDV Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body class="bg-light">

<div class="container">

<div class="row justify-content-center mt-5">

<div class="col-md-4">

<div class="card shadow">

<div class="card-body">

<h3 class="text-center mb-4">
MSDV Login
</h3>

<?php
if(isset($error))
{
    echo "<div class='alert alert-danger'>$error</div>";
}
?>

<form method="POST">

<div class="mb-3">
<label>Username</label>
<input
type="text"
name="username"
class="form-control"
required>
</div>

<div class="mb-3">
<label>Password</label>
<input
type="password"
name="password"
class="form-control"
required>
</div>

<div class="form-check mb-3">

<input
class="form-check-input"
type="checkbox"
name="remember">

<label class="form-check-label">
Remember Me
</label>

</div>

<button
type="submit"
name="login"
class="btn btn-primary w-100">

Login

</button>

</form>

</div>

</div>

</div>

</div>

</div>

</body>
</html>