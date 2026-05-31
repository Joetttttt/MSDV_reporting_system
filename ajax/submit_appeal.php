<?php

include("../config/database.php");

$report_id = $_POST['report_id'];

$student_id = $_POST['student_id'];

$appeal_reason = mysqli_real_escape_string(
$conn,
$_POST['appeal_reason']
);

mysqli_query(
$conn,
"INSERT INTO appeals
(
report_id,
student_id,
appeal_reason
)
VALUES
(
'$report_id',
'$student_id',
'$appeal_reason'
)"
);

echo "
<script>
alert('Appeal Submitted');
window.history.back();
</script>
";
?>