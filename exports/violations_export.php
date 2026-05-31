<?php

include("../config/database.php");

header('Content-Type:text/csv');
header('Content-Disposition:attachment;filename=violations.csv');

$output = fopen("php://output","w");

fputcsv(
$output,
[
'Report Number',
'Student ID',
'Category',
'Violation',
'Status'
]
);

$query = mysqli_query(
$conn,
"SELECT * FROM violation_reports"
);

while($row = mysqli_fetch_assoc($query))
{
    fputcsv(
    $output,
    [
        $row['report_number'],
        $row['student_id'],
        $row['category'],
        $row['violation_name'],
        $row['status']
    ]
    );
}

fclose($output);

exit();
?>