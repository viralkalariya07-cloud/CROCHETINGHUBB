<?php
include_once("includes/db.php");
$result = $conn->query("SELECT * FROM tutorials");
$data = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}
file_put_contents("debug_db.json", json_encode([
    "table_exists" => ($result !== false),
    "error" => $conn->error,
    "count" => count($data),
    "rows" => $data
], JSON_PRETTY_PRINT));
echo "Debug data written to debug_db.json";
?>
