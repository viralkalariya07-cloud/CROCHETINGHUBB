<?php
include_once(__DIR__ . "/includes/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['tutorialName'];
    $seller_name = $_POST['sellerName'];
    $video_link = $_POST['videoLink'];
    
    // Handle File Upload
    $photo = "";
    if (isset($_FILES['photoUpload']) && $_FILES['photoUpload']['error'] == 0) {
        $target_dir = "uploads/tutorials/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES["photoUpload"]["name"], PATHINFO_EXTENSION);
        $file_name = time() . "_" . uniqid() . "." . $file_extension;
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["photoUpload"]["tmp_name"], $target_file)) {
            $photo = $target_file;
        }
    }

    if (!empty($name) && !empty($seller_name) && !empty($video_link) && !empty($photo)) {
        $stmt = $conn->prepare("INSERT INTO tutorials (name, seller_name, video_link, photo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $seller_name, $video_link, $photo);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Tutorial saved successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error saving to database: " . $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
    }
}
?>
