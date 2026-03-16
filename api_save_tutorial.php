<?php
// Prevent any stray output from corrupting JSON
ob_start();

include_once(__DIR__ . "/includes/db.php");

// Set error handler to catch warnings/notices and return as JSON if needed
error_reporting(E_ALL);
ini_set('display_errors', 0);

$response = ["status" => "error", "message" => "An unknown error occurred"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST['tutorialName']) ? trim($_POST['tutorialName']) : '';
    $seller_name = isset($_POST['sellerName']) ? trim($_POST['sellerName']) : '';
    $video_link = isset($_POST['videoLink']) ? trim($_POST['videoLink']) : '';
    
    // Handle File Upload
    $photo = "";
    if (isset($_FILES['photoUpload']) && $_FILES['photoUpload']['error'] == 0) {
        $target_dir = "uploads/tutorials/";
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                $response["message"] = "Failed to create directory: " . $target_dir;
                goto end;
            }
        }
        $file_extension = pathinfo($_FILES["photoUpload"]["name"], PATHINFO_EXTENSION);
        $file_name = time() . "_" . uniqid() . "." . $file_extension;
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["photoUpload"]["tmp_name"], $target_file)) {
            $photo = $target_file;
        } else {
            $response["message"] = "Failed to move uploaded file.";
            goto end;
        }
    } else {
        $error_code = isset($_FILES['photoUpload']['error']) ? $_FILES['photoUpload']['error'] : 'missing';
        $response["message"] = "File upload error (code: $error_code)";
        goto end;
    }

    if (!empty($name) && !empty($seller_name) && !empty($video_link) && !empty($photo)) {
        $stmt = $conn->prepare("INSERT INTO tutorials (name, seller_name, video_link, photo) VALUES (?, ?, ?, ?)");
        
        if ($stmt) {
            $stmt->bind_param("ssss", $name, $seller_name, $video_link, $photo);
            
            if ($stmt->execute()) {
                $response = ["status" => "success", "message" => "Tutorial saved successfully"];
            } else {
                $response["message"] = "Database error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $response["message"] = "Failed to prepare statement: " . $conn->error;
        }
    } else {
        $missing = [];
        if (empty($name)) $missing[] = "Name";
        if (empty($seller_name)) $missing[] = "Seller Name";
        if (empty($video_link)) $missing[] = "Video Link";
        if (empty($photo)) $missing[] = "Photo";
        $response["message"] = "Required fields missing: " . implode(", ", $missing);
    }
} else {
    $response["message"] = "Invalid request method";
}

end:
// Clear any buffers (warnings, etc.)
ob_end_clean();
header('Content-Type: application/json');
echo json_encode($response);
?>
