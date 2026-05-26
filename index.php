<?php

session_start();

if (isset($_SESSION['user_id'])) {

    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: user/dashboard.php");
    }
    exit();

}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    include("config/database.php");

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {

        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: user");
            }
            exit();

        } else {
            $error = "Invalid username or password.";
        }

    } else {
        $error = "Invalid username or password.";
    }

}

?>
<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCC — Discipline System Login</title>

    <style>

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #091a47;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ── OUTER WRAPPER ── */
        .card-outer {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 340px;
        }

        /* ── SEAL ── */

        /* ── CARD ── */
        .card {
            background: #ffffff;
            border-radius: 5px;
            width: 100%;
            padding: 80px 19px 70px;
            border-top: 5px solid #b91c1c;
            height: 100%;
        }

        /* ── ERROR ALERT ── */
        .alert-error {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            font-size: 13px;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 18px;
            text-align: center;
            position: fixed;
            transform: translate(0, 40%);
            width: 302px;
        }

        /* ── FORM FIELDS ── */
        .field-wrap {
            margin-bottom: 20px;
        }

        .field-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 8px;
        }

        .field-input {
            width: 100%;
            padding: 12px 10px;
            background: #e9ecef;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            color: #1e293b;
            font-family: inherit;
            outline: none;
            transition: background .15s;
        }

        .field-input:focus {
            background: #dde3ea;
        }

        .field-input::placeholder {
            color: #94a3b8;
        }

        /* ── SIGN IN BUTTON ── */
        .btn-signin {
            width: 100%;
            padding: 14px;
            background: #b91c1c;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            cursor: pointer;
            font-family: inherit;
            transition: background .15s;
            margin-top: 8px;
        }

        .btn-signin:hover {
            background: #991b1b;
        }

        .btn-signin:active {
            background: #7f1d1d;
            transform: scale(.99);
        }
.loginlogo{
    border: 1px solid #091a47;
    border-radius: 100%;
    width: 155px;
    height: 155px;
}
.mcclogo{
    background: #091a47;
    border-radius: 50%;
      position: fixed;
    transform: translate(0%, -55%);
}
    </style>

</head>
<body>

    <div class="card-outer">

        <!-- SEAL -->
        <div class="mcclogo">
        <a href="csu.html"><img src="images/mccLogo.png" class="loginlogo" /></a>
      </div>
        <!-- CARD -->
        <div class="card">

            
            <form action="index.php" method="POST">

                <!-- USERNAME -->
                <div class="field-wrap">
                    <label class="field-label">Username</label>
                    <input
                        type="text"
                        name="username"
                        class="field-input"
                        placeholder="Enter your username"
                        required
                        autocomplete="username"
                    >
                </div>

                <!-- PASSWORD -->
                <div class="field-wrap">
                    <label class="field-label">Password</label>
                    <input
                        type="password"
                        name="password"
                        class="field-input"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <!-- SUBMIT -->
                <button type="submit" class="btn-signin">
                    Sign In
                </button>
<?php if ($error): ?>
                <div class="alert-error">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            </form>

        </div>

    </div>

</body>
</html>