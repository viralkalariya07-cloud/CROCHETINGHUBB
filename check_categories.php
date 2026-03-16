<?php
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);
$res = mysqli_query($conn, "DESCRIBE categories");
while($row = mysqli_fetch_assoc($res)) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
