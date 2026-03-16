<?php
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    mysqli_query($conn, "DELETE FROM user_feedback WHERE id = $id");
}

header("Location: userfeedback.php");
exit();
?>
