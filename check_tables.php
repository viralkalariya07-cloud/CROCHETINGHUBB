<?php
$conn = new mysqli("localhost", "root", "", "crochetinghubb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

echo "Tables:\n";
print_r($tables);

foreach(['seller_messages', 'useraccount', 'users', 'sellerproducts'] as $t) {
    if (in_array($t, $tables)) {
        echo "\nStructure of $t:\n";
        $result = $conn->query("DESCRIBE $t");
        while ($row = $result->fetch_assoc()) {
            print_r($row);
        }
    }
}
?>
