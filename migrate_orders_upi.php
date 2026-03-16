<?php
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "ALTER TABLE orderss ADD COLUMN upi_id VARCHAR(100) DEFAULT NULL AFTER payment_method";
if (mysqli_query($conn, $sql)) {
    echo "Column upi_id added successfully to orderss table.";
} else {
    echo "Error: " . mysqli_error($conn);
}
unlink(__FILE__);
?>
