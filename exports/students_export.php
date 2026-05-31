<?php

include("../config/database.php");

header('Content-Type:text/csv');
header('Content-Disposition:attachment;filename=students.csv');

$output = fopen("php://output","w");

fputcsv(
$output,
[
'Student ID',
'Full Name',
'Course',
'Year Level',
'Department'
]
);

$query = mysqli_query(
$conn,
"SELECT * FROM students"
);

while($row = mysqli_fetch_assoc($query))
{
    fputcsv(
    $output,
    [
        $row['student_id'],
        $row['full_name'],
        $row['course'],
        $row['year_level'],
        $row['department']
    ]
    );
}

fclose($output);

exit();
?>