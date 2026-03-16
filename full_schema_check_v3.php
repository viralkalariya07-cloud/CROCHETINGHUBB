<?php
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);
echo "categories table:\n";
$res = mysqli_query($conn, "DESCRIBE categories");
while($row = mysqli_fetch_assoc($res)) { print_r($row); }

echo "\nusers table:\n";
$res = mysqli_query($conn, "DESCRIBE users");
while($row = mysqli_fetch_assoc($res)) { print_r($row); }

echo "\nuseraccount table:\n";
$res = mysqli_query($conn, "DESCRIBE useraccount");
while($row = mysqli_fetch_assoc($res)) { print_r($row); }
?>
