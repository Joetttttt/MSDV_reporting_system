<?php
include("../config/auth.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
</head>
<body>

<h1>Admin Dashboard</h1>

<p>
Welcome,
<?php echo $_SESSION['fullname']; ?>
</p>

</body>
</html>