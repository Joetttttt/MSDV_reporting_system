<?php

include("../config/database.php");

$year = $_GET['year'];

$where = "";

if($year != ""){
    $where = "WHERE YEAR(created_at)='$year'";
}

$query = "

SELECT course, COUNT(*) AS total

FROM violations

$where

GROUP BY course

";

$result = mysqli_query($conn, $query);

$labels = [];
$values = [];

while($row = mysqli_fetch_assoc($result)){

    $labels[] = $row['course'];
    $values[] = $row['total'];

}

echo json_encode([
    "labels"=>$labels,
    "values"=>$values
]);

?>