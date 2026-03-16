<?php
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);
$output = "";

$tables = mysqli_query($conn, "SHOW TABLES");
while($t = mysqli_fetch_row($tables)) {
    $tableName = $t[0];
    $output .= "--- Table: $tableName ---\n";
    $cols = mysqli_query($conn, "DESCRIBE $tableName");
    while($c = mysqli_fetch_assoc($cols)) {
        $output .= "  " . $c['Field'] . " (" . $c['Type'] . ")\n";
    }
}

file_put_contents("schema_dump.txt", $output);
echo "Dumped to schema_dump.txt";
?>
