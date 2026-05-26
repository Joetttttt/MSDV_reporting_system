<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            background-color: #0d2254;
            background-image:
                radial-gradient(ellipse at 20% 50%, rgba(20, 45, 110, 0.8) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(10, 25, 80, 0.6) 0%, transparent 50%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Barlow', sans-serif;
        }

        .login-wrapper {
            width: 100%;
            max-width: 400px;
            padding: 0 16px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 6px;
            overflow: visible;
            position: relative;
            padding: 0 0 36px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.45);
        }

        /* Red top bar */
        .login-card::before {
            content: '';
            display: block;
            height: 6px;
            background: #c0192c;
            border-radius: 6px 6px 0 0;
        }

        /* Logo circle area */
        .logo-area {
            display: flex;
            justify-content: center;
            margin-top: -6px;
            margin-bottom: 24px;
            padding-top: 20px;
        }

        .logo-circle {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: #0d2254;
            border: 4px solid #c0192c;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.35);
        }

        .logo-circle img {


        /* Fallback SVG crest if image not available */
        
        .card-body-inner {
            padding: 100px 36px 36px;
        }

        .field-label {
            display: block;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: #1a2f6e;
            margin-bottom: 7px;
            text-transform: uppercase;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control-mcc {
            width: 100%;
            padding: 12px 14px;
            border: none;
            border-radius: 5px;
            background: #e8eaf0;
            color: #444;
            font-family: 'Barlow', sans-serif;
            font-size: 14px;
            outline: none;
            transition: background 0.2s, box-shadow 0.2s;
        }

        .form-control-mcc::placeholder {
            color: #9aa0b5;
            font-size: 13.5px;
        }

        .form-control-mcc:focus {
            background: #dde0ec;
            box-shadow: 0 0 0 3px rgba(192, 25, 44, 0.18);
        }

        .btn-signin {
            width: 100%;
            padding: 13px;
            background: #c0192c;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-family: 'Barlow', sans-serif;
            font-size: 13.5px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 6px;
            transition: background 0.2s, transform 0.1s;
        }

        .btn-signin:hover {
            background: #a5151f;
        }

        .btn-signin:active {
            transform: scale(0.98);
        }

        .error-msg {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fdf0f1;
            border: 1px solid #f5c0c5;
            border-left: 4px solid #c0192c;
            border-radius: 5px;
            padding: 10px 14px;
            margin-bottom: 18px;
            color: #9b1222;
            font-size: 13px;
            font-weight: 500;
            animation: fadeInDown 0.3s ease;
        }

        .error-msg svg {
            flex-shrink: 0;
            width: 16px;
            height: 16px;
            color: #c0192c;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-6px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .loginlogo{
    border: 5px solid #091a47;
    border-radius: 100%;
    width: 165px;
    height: 165px;
}
.mcclogo{
    background: #091a47;
    border-radius: 50%;
      position: fixed;
    transform: translate(60%, -55%);
    z-index: 9999;
}
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="mcclogo">
        <a href="csu.html"><img src="images/mccLogo.png" class="loginlogo" /></a>
      </div>
    <div class="login-card">

        <!-- Logo -->
        

        <div class="card-body-inner">
            <?php
                $error = '';
                if (isset($_GET['error'])) {
                    if ($_GET['error'] === 'invalid_password') {
                        $error = 'Incorrect password. Please try again.';
                    } elseif ($_GET['error'] === 'user_not_found') {
                        $error = 'Username not found. Please check your credentials.';
                    }
                }
            ?>

            <?php if ($error): ?>
            <div class="error-msg">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form action="auth/login.php" method="POST">

                <div class="form-group">
                    <label class="field-label" for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-control-mcc"
                        placeholder="Enter your username"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="field-label" for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control-mcc"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <button type="submit" class="btn-signin">
                    Sign In
                </button>

            </form>
        </div>

    </div>
</div>

</body>
</html>