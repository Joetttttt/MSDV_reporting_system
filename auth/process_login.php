<!DOCTYPE html>
<html>
<head>
    <title>MSDV Login</title>
</head>
<body>

<h2>MSDV Login</h2>

<form action="process_login.php" method="POST">

    <label>Username</label><br>
    <input type="text" name="username" required>

    <br><br>

    <label>Password</label><br>
    <input type="password" name="password" required>

    <br><br>

    <input type="checkbox" name="remember_me">
    Remember Me

    <br><br>

    <button type="submit">
        Login
    </button>

</form>

</body>
</html>