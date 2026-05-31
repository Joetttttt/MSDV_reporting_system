<?php
include("../config/session.php");
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
</head>
<body>

<h1>Admin Dashboard</h1>

<h3>
Welcome
<?php echo $_SESSION['full_name']; ?>
</h3>

<a href="../logout.php">
Logout
</a>

</body>
</html>