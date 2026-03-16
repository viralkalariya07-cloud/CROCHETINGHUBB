<?php
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);

if (!$conn) {
    die("Database connection failed");
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: userfeedback.php");
    exit();
}

// Fetch feedback details
$result = mysqli_query($conn, "SELECT * FROM user_feedback WHERE id = $id");
$row = mysqli_fetch_assoc($result);

if (!$row) {
    header("Location: userfeedback.php");
    exit();
}

// Handle Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name   = $_POST['full_name'] ?? '';
    $email       = $_POST['email'] ?? '';
    $feedback_id = $_POST['feedback_id'] ?? '';
    $order_id    = $_POST['order_id'] ?? '';
    $rating      = $_POST['rating'] ?? '';
    $message     = $_POST['message'] ?? '';

    $update_query = "UPDATE user_feedback SET 
                    full_name = '$full_name', 
                    email = '$email', 
                    feedback_id = '$feedback_id', 
                    order_id = '$order_id', 
                    rating = '$rating', 
                    message = '$message' 
                    WHERE id = $id";

    if (mysqli_query($conn, $update_query)) {
        header("Location: userfeedback.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Feedback - Crocheting Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/userfeedback.css">
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="user.php">
            🧶 <span>CrochetingHubb</span>
        </a>
    </div>
</nav>

<main>
<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="section-title mb-3">Edit Your Feedback ✏️</h1>
        <p class="subtitle-text">Update your experience with us.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="feedback-card">
                <div class="card-header-custom">
                    <i class="fas fa-edit me-2"></i>Update Details
                </div>

                <form method="POST" class="p-4">
                    <div class="mb-4">
                        <label class="form-label"><i class="fas fa-user me-2"></i>Full Name <span class="required-asterisk">*</span></label>
                        <input type="text" class="form-control form-control-custom" name="full_name" value="<?php echo htmlspecialchars($row['full_name']); ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><i class="fas fa-envelope me-2"></i>Email <span class="required-asterisk">*</span></label>
                        <input type="email" class="form-control form-control-custom" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label field-feedback-id"><i class="fas fa-id-card me-2"></i>Feedback ID <span class="required-asterisk">*</span></label>
                        <input type="text" class="form-control form-control-custom" name="feedback_id" value="<?php echo htmlspecialchars($row['feedback_id']); ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label field-order-id"><i class="fas fa-hashtag me-2"></i>Order ID <span class="required-asterisk">*</span></label>
                        <input type="text" class="form-control form-control-custom" name="order_id" value="<?php echo htmlspecialchars($row['order_id']); ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-block"><i class="fas fa-star me-2"></i>Rate Your Experience</label>
                        <div class="star-rating" id="starRating">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= $row['rating'] ? 'active' : ''; ?>" data-rating="<?php echo $i; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" id="ratingValue" name="rating" value="<?php echo $row['rating']; ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label"><i class="fas fa-comment-alt me-2"></i>Your Feedback</label>
                        <textarea class="form-control form-control-custom" name="message" rows="5" required><?php echo htmlspecialchars($row['message']); ?></textarea>
                    </div>

                    <div class="text-center d-flex gap-3 justify-content-center">
                        <a href="userfeedback.php" class="btn btn-secondary btn-lg px-5 border-0" style="border-radius:12px;">Cancel</a>
                        <button type="submit" class="btn btn-pink btn-lg px-5">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const stars = document.querySelectorAll('#starRating i');
    const ratingValueInput = document.getElementById('ratingValue');

    stars.forEach(star => {
        star.addEventListener('click', () => {
            const rating = star.dataset.rating;
            ratingValueInput.value = rating;
            stars.forEach((s, i) => s.classList.toggle('active', i < rating));
        });
    });
});
</script>

</body>
</html>
