<?php
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);
if (!$conn) die("Connection failed");

echo "--- USERS TABLE ---\n";
$res = mysqli_query($conn, "SELECT id, full_name, email FROM users");
while($row = mysqli_fetch_assoc($res)) print_r($row);

echo "\n--- USERACCOUNT TABLE ---\n";
$res = mysqli_query($conn, "SELECT id, full_name, email FROM useraccount");
while($row = mysqli_fetch_assoc($res)) print_r($row);

echo "\n--- ORDERSS TABLE ---\n";
$res = mysqli_query($conn, "SELECT id, customer_id, product_id, seller_id FROM orderss");
while($row = mysqli_fetch_assoc($res)) print_r($row);

echo "\n--- SELLERPRODUCTS TABLE ---\n";
$res = mysqli_query($conn, "SELECT id, name, seller_id FROM sellerproducts");
while($row = mysqli_fetch_assoc($res)) print_r($row);
?>
