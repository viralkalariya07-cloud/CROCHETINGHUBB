<?php
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
$result = mysqli_query($conn, "DESCRIBE users");
while($row = mysqli_fetch_assoc($result)) {
    print_r($row);
}
?>
