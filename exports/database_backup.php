<?php

$database = "mcc_discipline_system";

$filename =
"backup_"
.date("Y-m-d_H-i-s")
.".sql";

header(
"Content-Type: application/octet-stream"
);

header(
"Content-Disposition: attachment; filename=$filename"
);

$command =
"mysqldump -u root $database";

passthru($command);

exit();

?>