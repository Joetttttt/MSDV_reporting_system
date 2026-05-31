<?php

include("../config/database.php");

header('Content-Type:text/csv');
header('Content-Disposition:attachment;filename=appeals.csv');

$output = fopen("php://output","w");

fputcsv(
$output,
[
'Appeal ID',
'Student ID',
'Reason',
'Status'
]
);

$query = mysqli_query(
$conn,
"SELECT * FROM appeals"
);

while($row = mysqli_fetch_assoc($query))
{
    fputcsv(
    $output,
    [
        $row['appeal_id'],
        $row['student_id'],
        $row['appeal_reason'],
        $row['status']
    ]
    );
}

fclose($output);

exit();
?>