<?php
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);
if (!$conn) {
    file_put_contents("schema_output.txt", "Connection failed: " . mysqli_connect_error());
    exit();
}
$result = mysqli_query($conn, "DESCRIBE users");
$output = "";
while($row = mysqli_fetch_assoc($result)) {
    $output .= print_r($row, true) . "\n";
}
file_put_contents("schema_output.txt", $output);
?>
