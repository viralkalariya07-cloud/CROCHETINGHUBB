<?php
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);
$result = mysqli_query($conn, "SHOW COLUMNS FROM users");
while($row = mysqli_fetch_assoc($result)) {
    echo $row['Field'] . ",";
}
?>
