<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$output = "DATABASE SCHEMA CHECK\n=====================\n\n";

// Check 'users' table
$output .= "TABLE: users\n";
$res = mysqli_query($conn, "SHOW CREATE TABLE users");
if ($res) {
    $row = mysqli_fetch_row($res);
    $output .= $row[1] . "\n\n";
} else {
    $output .= "Error: " . mysqli_error($conn) . "\n\n";
}

// Check 'useraccount' table (just in case)
$output .= "TABLE: useraccount\n";
$res = mysqli_query($conn, "SHOW CREATE TABLE useraccount");
if ($res) {
    $row = mysqli_fetch_row($res);
    $output .= $row[1] . "\n\n";
} else {
    $output .= "Error: " . mysqli_error($conn) . "\n\n";
}

file_put_contents("C:/xampp/htdocs/CROCHETINGHUBB/full_schema_check.txt", $output);
echo "Schema written to full_schema_check.txt";
?>
