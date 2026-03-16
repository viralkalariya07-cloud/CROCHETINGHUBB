<?php
include("includes/db.php");
$result = mysqli_query($conn, "SHOW TABLES");
echo "Tables in database:\n";
while($row = mysqli_fetch_row($result)) {
    echo "- " . $row[0] . "\n";
}
?>
