<?php
include_once("includes/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    
    if (!empty($id)) {
        // Optional: Delete physical file
        $stmt_get = $conn->prepare("SELECT photo FROM tutorials WHERE id = ?");
        $stmt_get->bind_param("i", $id);
        $stmt_get->execute();
        $res = $stmt_get->get_result();
        if ($row = $res->fetch_assoc()) {
            if (file_exists($row['photo'])) {
                unlink($row['photo']);
            }
        }
        $stmt_get->close();

        $stmt = $conn->prepare("DELETE FROM tutorials WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Tutorial deleted successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error deleting from database"]);
        }
        $stmt->close();
    }
}
?>
