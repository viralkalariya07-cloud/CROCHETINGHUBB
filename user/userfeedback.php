<?php
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);

if (!$conn) {
    die("Database connection failed");
}

/* ================= OPTIONAL BACKEND (READY BUT JS CONTROLS SUBMIT) =================
This code is ready when you later connect JS/AJAX or remove preventDefault().
It does NOT affect current behavior.
*/
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name   = $_POST['full_name'] ?? '';
    $email       = $_POST['email'] ?? '';
    $feedback_id = $_POST['feedback_id'] ?? '';
    $order_id    = $_POST['order_id'] ?? '';
    $rating      = $_POST['rating'] ?? '';
    $message     = $_POST['message'] ?? '';

    $query = "INSERT INTO user_feedback (full_name, email, feedback_id, order_id, rating, message)
              VALUES ('$full_name', '$email', '$feedback_id', '$order_id', '$rating', '$message')";
    mysqli_query($conn, $query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - Crocheting Hub</title>

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/userfeedback.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="../index.html">
            🧶 <span>CrochetingHubb</span>
        </a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#userNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="userNavbar">
            <!-- 2. Other Options with Icons -->
            <ul class="navbar-nav ms-lg-auto align-items-lg-center gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link" href="user.php"><i class="bi bi-house-door me-1"></i>Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-grid me-1"></i>Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="userfeedback.php"><i class="bi bi-chat-dots me-1"></i>Feedback</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link position-relative" href="usercart.php">
                        <i class="bi bi-cart3 me-1"></i>Cart
                    </a>
                </li>
                <li class="nav-item ms-lg-2">
                    <a class="nav-link d-flex align-items-center gap-2" href="useraccount.php">
                        <i class="bi bi-person-circle fs-5"></i>Account
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Content -->
<main>
<div class="container py-5">

<div class="text-center mb-5">
    <h1 class="section-title mb-3">We Value Your Feedback 💗</h1>
    <p class="subtitle-text">Your thoughts help us create better crochet experiences for everyone!</p>
</div>

<div class="row justify-content-center">
<div class="col-lg-8 col-md-10">
<div class="feedback-card">
<div class="card-header-custom">
    <i class="fas fa-pen-fancy me-2"></i>Share Your Experience
</div>

<form id="feedbackForm" class="p-4">

<div class="mb-4">
    <label class="form-label"><i class="fas fa-user me-2"></i>Full Name <span class="required-asterisk">*</span></label>
    <input type="text" class="form-control form-control-custom" id="fullName" name="full_name" required>
    <div class="error-message" id="nameError"></div>
</div>

<div class="mb-4">
    <label class="form-label"><i class="fas fa-envelope me-2"></i>Email <span class="required-asterisk">*</span></label>
    <input type="email" class="form-control form-control-custom" id="email" name="email" required>
    <div class="error-message" id="emailError"></div>
</div>

<div class="mb-4">
    <label class="form-label field-feedback-id"><i class="fas fa-id-card me-2"></i>Feedback ID <span class="required-asterisk">*</span></label>
    <input type="text" class="form-control form-control-custom" id="feedbackId" name="feedback_id" placeholder="e.g. FB98765" required>
    <div class="error-message" id="idError"></div>
</div>

<div class="mb-4">
    <label class="form-label field-order-id"><i class="fas fa-hashtag me-2"></i>Order ID <span class="required-asterisk">*</span></label>
    <input type="text" class="form-control form-control-custom" id="orderId" name="order_id" placeholder="e.g. #CH12345" required>
    <div class="error-message" id="orderError"></div>
</div>

<div class="mb-4">
    <label class="form-label d-block"><i class="fas fa-star me-2"></i>Rate Your Experience</label>
    <div class="star-rating" id="starRating">
        <i class="fas fa-star" data-rating="1"></i>
        <i class="fas fa-star" data-rating="2"></i>
        <i class="fas fa-star" data-rating="3"></i>
        <i class="fas fa-star" data-rating="4"></i>
        <i class="fas fa-star" data-rating="5"></i>
    </div>
    <input type="hidden" id="ratingValue" name="rating" value="0">
    <div class="rating-text mt-2" id="ratingText">Click to rate</div>
    <div class="error-message" id="ratingError"></div>
</div>

<div class="mb-4">
    <label class="form-label"><i class="fas fa-comment-alt me-2"></i>Your Feedback</label>
    <textarea class="form-control form-control-custom" id="feedbackMessage" name="message" rows="5" required></textarea>
    <div class="char-counter mt-2"><span id="charCount">0</span>/500 characters</div>
    <div class="error-message" id="messageError"></div>
</div>

<div class="text-center">
    <button type="submit" class="btn btn-pink btn-lg px-5">
        <i class="fas fa-paper-plane me-2"></i>Submit Feedback
    </button>
</div>

</form>
</div>
</div>
</div>

</div>

<!-- Previous Feedback Table -->
<div class="row mt-5 justify-content-center">
    <div class="col-lg-10">
        <div class="feedback-card">
            <div class="card-header-custom bg-dark text-white">
                <i class="fas fa-list-alt me-2"></i>Recent Feedbacks
            </div>
            <div class="table-responsive p-4">
                <table class="table table-hover custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Order ID</th>
                            <th>Rating</th>
                            <th>Feedback</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $get_feedback = mysqli_query($conn, "SELECT * FROM user_feedback ORDER BY id DESC");
                        if(mysqli_num_rows($get_feedback) > 0) {
                            while($row = mysqli_fetch_assoc($get_feedback)) {
                                ?>
                                <tr>
                                    <td><?php echo $row['feedback_id']; ?></td>
                                    <td><?php echo $row['full_name']; ?></td>
                                    <td><?php echo $row['order_id']; ?></td>
                                    <td>
                                        <div class="rating-stars">
                                            <?php 
                                            for($i=1; $i<=5; $i++){
                                                echo $i <= $row['rating'] ? '<i class="fas fa-star text-warning"></i>' : '<i class="far fa-star text-muted"></i>';
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td><small><?php echo substr($row['message'], 0, 50); ?>...</small></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="edit_feedback.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-primary shadow-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete_feedback.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-danger shadow-sm ms-1" onclick="return confirm('Are you sure you want to delete this feedback?')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center text-muted py-4'>No feedback found yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</main>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content success-modal-content">
<div class="modal-body text-center p-5">
    <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
    <h3>Thank You! 💗</h3>
    <p>Your feedback has been successfully submitted.</p>
    <button class="btn btn-pink" data-bs-dismiss="modal">Close</button>
</div>
</div>
</div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Merged JavaScript -->
<script>
<?php
// JS pasted directly
?>
document.addEventListener('DOMContentLoaded', function () {

const feedbackForm = document.getElementById('feedbackForm');
const fullNameInput = document.getElementById('fullName');
const emailInput = document.getElementById('email');
const feedbackIdInput = document.getElementById('feedbackId');
const orderIdInput = document.getElementById('orderId');
const feedbackMessage = document.getElementById('feedbackMessage');
const ratingValueInput = document.getElementById('ratingValue');
const charCountSpan = document.getElementById('charCount');

const nameError = document.getElementById('nameError');
const emailError = document.getElementById('emailError');
const idError = document.getElementById('idError');
const orderError = document.getElementById('orderError');
const ratingError = document.getElementById('ratingError');
const messageError = document.getElementById('messageError');

const stars = document.querySelectorAll('#starRating i');
const ratingText = document.getElementById('ratingText');
const successModal = new bootstrap.Modal(document.getElementById('successModal'));

let currentRating = 0;

stars.forEach(star => {
    star.addEventListener('click', () => {
        currentRating = star.dataset.rating;
        ratingValueInput.value = currentRating;
        stars.forEach((s, i) => s.classList.toggle('active', i < currentRating));
        ratingText.textContent = `Rated ${currentRating} Star(s)`;
        ratingError.textContent = '';
    });
});

feedbackMessage.addEventListener('input', () => {
    charCountSpan.textContent = feedbackMessage.value.length;
});

feedbackForm.addEventListener('submit', function(e) {
    e.preventDefault();

    // Clear previous errors
    document.querySelectorAll('.error-message').forEach(el => {
        el.textContent = '';
        el.classList.remove('show');
    });

    // Validation
    let hasError = false;

    if (!fullNameInput.value.trim()) {
        nameError.textContent = "Name is required";
        nameError.classList.add('show');
        hasError = true;
    }
    if (!emailInput.value.trim()) {
        emailError.textContent = "Email is required";
        emailError.classList.add('show');
        hasError = true;
    }
    if (!feedbackIdInput.value.trim()) {
        idError.textContent = "Feedback ID is required";
        idError.classList.add('show');
        hasError = true;
    }
    if (!orderIdInput.value.trim()) {
        orderError.textContent = "Order ID is required";
        orderError.classList.add('show');
        hasError = true;
    }
    if (ratingValueInput.value == 0) {
        ratingError.textContent = "Please select a rating";
        ratingError.classList.add('show');
        hasError = true;
    }
    if (feedbackMessage.value.length < 10) {
        messageError.textContent = "Feedback must be at least 10 characters";
        messageError.classList.add('show');
        hasError = true;
    }

    if (hasError) return;

    // Submit form via AJAX (Fetch)
    const formData = new FormData(feedbackForm);

    fetch('userfeedback.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.ok) {
            // Show success modal
            successModal.show();
            
            // Reset form
            feedbackForm.reset();
            stars.forEach(s => s.classList.remove('active'));
            ratingText.textContent = "Click to rate";
            charCountSpan.textContent = "0";
            ratingValueInput.value = 0;
        } else {
            alert("Something went wrong. Please try again.");
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Connection error. Please try again.");
    });
});
});
</script>

</body>
</html>
