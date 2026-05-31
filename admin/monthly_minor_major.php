<?php

include("../config/database.php");

$year = $_GET['year'];

$where = "";

if($year != ""){
    $where = "WHERE YEAR(created_at)='$year'";
}

$query = "

SELECT
MONTH(created_at) AS month,
SUM(CASE WHEN violation_category='Minor' THEN 1 ELSE 0 END) AS minor_total,
SUM(CASE WHEN violation_category='Major' THEN 1 ELSE 0 END) AS major_total

FROM violations

$where

GROUP BY MONTH(created_at)

";

$result = mysqli_query($conn, $query);

$months = [];
$minor = [];
$major = [];

while($row = mysqli_fetch_assoc($result)){

    $months[] = $row['month'];
    $minor[] = $row['minor_total'];
    $major[] = $row['major_total'];

}

echo json_encode([
    "months"=>$months,
    "minor"=>$minor,
    "major"=>$major
]);

?>