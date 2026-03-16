<?php
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
$sql = "ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NOT NULL";
if (mysqli_query($conn, $sql)) {
    echo "Password column resized successfully";
} else {
    echo "Error resizing column: " . mysqli_error($conn);
}
?>
