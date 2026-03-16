<?php
session_name('USER_SESSION');
session_start();

/* DATABASE CONNECTION */
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);
if (!$conn) {
    die("Database connection failed");
}

/* TEMP USER ID (REMOVE AFTER LOGIN SYSTEM) */
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // testing
}
$user_id = $_SESSION['user_id'];

// Create user_payments table if not exists
$sql_payments = "CREATE TABLE IF NOT EXISTS user_payments (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    upi_id VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $sql_payments);

// Create orderss table if not exists
$sql_orders = "CREATE TABLE IF NOT EXISTS orderss (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    seller_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    upi_id VARCHAR(100) DEFAULT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $sql_orders);

// Create seller_notifications table if not exists
$sql_notifs = "CREATE TABLE IF NOT EXISTS seller_notifications (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seller_id INT(11) NOT NULL,
    order_id INT(11) NOT NULL,
    customer_name VARCHAR(150) DEFAULT 'Customer',
    product_name VARCHAR(255) DEFAULT '',
    total_price DECIMAL(10,2) NOT NULL DEFAULT 0,
    payment_method VARCHAR(50) DEFAULT '',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $sql_notifs);

/* ENSURE upi_id COLUMN EXISTS */
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM orderss LIKE 'upi_id'");
if (mysqli_num_rows($check_col) == 0) {
    mysqli_query($conn, "ALTER TABLE orderss ADD COLUMN upi_id VARCHAR(100) DEFAULT NULL AFTER payment_method");
}

/* HANDLE PAYMENT DATA */
$order_placed = false;
$is_confirming = false;

/* CALCULATE TOTAL AMOUNT */
$total_amount = 0;
$cart_items_query = "SELECT c.quantity, p.price FROM cart c JOIN sellerproducts p ON c.product_id = p.id WHERE c.user_id = '$user_id'";
$cart_items_res = mysqli_query($conn, $cart_items_query);
if ($cart_items_res) {
    while($row = mysqli_fetch_assoc($cart_items_res)) {
        $total_amount += $row['price'] * $row['quantity'];
    }
}

// Step 1: Confirm Payment Method
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['confirm_payment'])) {
    $payment_method = $_POST['payment_method'] ?? '';
    $upi_id = $_POST['upi_id'] ?? NULL;

    if ($payment_method === 'upi' && empty($upi_id)) {
        $error = "UPI ID required";
    } else {
        $_SESSION['temp_payment_method'] = $payment_method;
        $_SESSION['temp_upi_id'] = $upi_id;
        $is_confirming = true;
    }
}

// Step 2: Confirm Final Order
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['place_order'])) {
    $payment_method = $_SESSION['temp_payment_method'] ?? 'Unknown';
    $upi_id = $_SESSION['temp_upi_id'] ?? NULL;

    // Save payment info
    $stmt_pay = mysqli_prepare($conn, "INSERT INTO user_payments (user_id, payment_method, upi_id) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt_pay, "iss", $user_id, $payment_method, $upi_id);
    mysqli_stmt_execute($stmt_pay);

    // Fetch cart items to place orders
    $cart_query = "SELECT c.*, p.price, p.seller_id FROM cart c JOIN sellerproducts p ON c.product_id = p.id WHERE c.user_id = '$user_id'";
    $cart_res = mysqli_query($conn, $cart_query);

    if (mysqli_num_rows($cart_res) > 0) {
        // STOCK VALIDATION: Check if enough stock exists for ALL items in cart
        $stock_error = "";
        $stock_verify_query = "SELECT c.quantity as req_qty, p.name, p.stock as available_stock 
                              FROM cart c 
                              JOIN sellerproducts p ON c.product_id = p.id 
                              WHERE c.user_id = '$user_id'";
        $stock_verify_res = mysqli_query($conn, $stock_verify_query);
        
        while ($check = mysqli_fetch_assoc($stock_verify_res)) {
            if ($check['req_qty'] > $check['available_stock']) {
                if ($check['available_stock'] <= 0) {
                    $stock_error = "Sorry, '{$check['name']}' is just went out of stock.";
                } else {
                    $stock_error = "Only {$check['available_stock']} units of '{$check['name']}' are currently available. Please adjust your cart.";
                }
                break;
            }
        }

        if ($stock_error) {
            $error = $stock_error;
            $is_confirming = false; // Send back to payment selection to fix cart if needed
        } else {
            // Proceed to finalize order since stock is verified

        // Fetch customer name for notification
        $cust_name_res = mysqli_query($conn, "SELECT full_name FROM useraccount WHERE id = '$user_id' LIMIT 1");
        $cust_name_row = $cust_name_res ? mysqli_fetch_assoc($cust_name_res) : null;
        if (!$cust_name_row) {
            $cust_res2 = mysqli_query($conn, "SELECT full_name FROM users WHERE id = '$user_id' LIMIT 1");
            $cust_name_row = $cust_res2 ? mysqli_fetch_assoc($cust_res2) : null;
        }
        $customer_name = $cust_name_row['full_name'] ?? 'A Customer';

        while ($item = mysqli_fetch_assoc($cart_res)) {
            $prod_id = $item['product_id'];
            $sell_id = $item['seller_id'];
            $qty = $item['quantity'];
            $total = $item['price'] * $qty;

            // Insert into orders table
            $stmt_ord = mysqli_prepare($conn, "INSERT INTO orderss (customer_id, product_id, seller_id, quantity, total_price, payment_method, upi_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_ord, "iiiidss", $user_id, $prod_id, $sell_id, $qty, $total, $payment_method, $upi_id);
            mysqli_stmt_execute($stmt_ord);
            $new_order_id = mysqli_insert_id($conn);

            // Update Stock
            mysqli_query($conn, "UPDATE sellerproducts SET stock = stock - '$qty' WHERE id = '$prod_id'");
            
            // Auto-update status if stock becomes zero
            mysqli_query($conn, "UPDATE sellerproducts SET status = 'Out of Stock' WHERE id = '$prod_id' AND stock <= 0");


            // Fetch product name for notification
            $prod_name_res = mysqli_query($conn, "SELECT name FROM sellerproducts WHERE id = '$prod_id' LIMIT 1");
            $prod_name_row = $prod_name_res ? mysqli_fetch_assoc($prod_name_res) : null;
            $product_name = $prod_name_row['name'] ?? 'Product';

            // Insert seller notification
            $stmt_notif = mysqli_prepare($conn, "INSERT INTO seller_notifications (seller_id, order_id, customer_name, product_name, total_price, payment_method) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_notif, "iissds", $sell_id, $new_order_id, $customer_name, $product_name, $total, $payment_method);
            mysqli_stmt_execute($stmt_notif);
        }

        // Clear Cart
        mysqli_query($conn, "DELETE FROM cart WHERE user_id = '$user_id'");

        $order_placed = true;
        $placed_method = $payment_method;
        unset($_SESSION['temp_payment_method']);
        unset($_SESSION['temp_upi_id']);
        } // End of stock else
    } else {

        $error = "Your cart is empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Payment Methods – Crocheting Hub</title>
    <meta name="description" content="Choose your payment method on Crocheting Hub." />

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom Payment CSS -->
    <link rel="stylesheet" href="../assets/css/userpayment.css" />
</head>
<body>

<!-- ================= NAVBAR ================= -->
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="../index.html">
            🧶 <span>CrochetingHubb</span>
        </a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
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
                    <a class="nav-link" href="userfeedback.php"><i class="bi bi-chat-dots me-1"></i>Feedback</a>
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

<!-- ================= MAIN ================= -->
<main>

<!-- Hero Section -->
<section class="payment-hero">
    <div class="container">
        <h1 class="section-title">Payment Method 💳</h1>
        <p class="payment-subtitle">Choose how you'd like to pay for your order</p>
    </div>
</section>

<!-- Payment Body -->
<section class="payment-body-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <?php if ($order_placed): ?>
                    <!-- Thank You Message -->
                    <div class="payment-methods-card text-center py-5 animate__animated animate__fadeIn">
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="fw-bold mb-3">Order Successfully Placed!</h2>
                        <?php if ($placed_method === 'cod'): ?>
                            <p class="lead mb-4">Your order has been placed successfully via <strong>Cash on Delivery (COD)</strong>.</p>
                            <div class="alert bg-soft-pink text-pink border-0 mb-4">
                                <i class="bi bi-info-circle me-2"></i> Thank you for applying order on COD. Please keep the exact amount ready at the time of delivery.
                            </div>
                        <?php else: ?>
                            <p class="lead mb-4">Your payment via <strong>UPI</strong> was successful and your order is placed.</p>
                        <?php endif; ?>
                        
                        <div class="d-grid gap-2">
                            <a href="user.php" class="btn btn-pink py-3 rounded-pill">Continue Shopping</a>
                            <a href="useraccount.php" class="btn btn-outline-secondary py-3 rounded-pill">View My Orders</a>
                        </div>
                    </div>

                <?php elseif ($is_confirming): ?>
                    <!-- Confirm Order Step -->
                    <div class="payment-methods-card animate__animated animate__fadeIn">
                        <?php if ($_SESSION['temp_payment_method'] === 'upi'): ?>
                            <h5 class="panel-title mb-4 text-center"><i class="fas fa-qrcode me-2"></i>Scan & Pay</h5>
                            
                            <div class="qr-container text-center my-4">
                                <div class="qr-card p-4 rounded shadow-sm bg-white border">
                                    <div class="user-info mb-3 d-flex align-items-center justify-content-center gap-2">
                                        <div class="user-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-weight: bold;">V</div>
                                        <span class="fw-bold">Viral Kalariya</span>
                                    </div>
                                    <div class="qr-image-wrapper mb-3 position-relative d-inline-block">
                                         <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=upi://pay?pa=viralkalariya07@okicici&pn=Viral%20Kalariya&am=<?php echo $total_amount; ?>&cu=INR" alt="Payment QR Code" class="img-fluid rounded border p-2 bg-white" style="max-width: 250px;">
                                         <div class="gpay-logo-overlay">
                                             <img src="https://www.gstatic.com/lamda/images/google_pay_logo.png" alt="GPay">
                                         </div>
                                    </div>
                                    <p class="mb-1 text-muted small"><strong>Pay to:</strong> viralkalariya07@okicici</p>
                                    <p class="mb-1 text-muted small"><strong>Your UPI ID:</strong> <?php echo htmlspecialchars($_SESSION['temp_upi_id']); ?></p>
                                    <p class="mb-2 text-danger fw-bold">Amount to Pay: ₹<?php echo number_format($total_amount, 2); ?></p>
                                    <p class="mb-3 text-muted small">Scan to pay with any UPI app (GPay, PhonePe, Paytm)</p>
                                    
                                    <div class="timer-box bg-light p-3 rounded-pill border d-inline-flex align-items-center gap-2 mb-4">
                                        <i class="bi bi-clock-history text-danger"></i>
                                        <span id="paymentTimer" class="fw-bold fs-5 text-danger">01:30</span>
                                    </div>

                                    <form method="POST" action="">
                                        <button type="submit" name="place_order" class="btn btn-pink w-100 py-3 rounded-pill shadow-sm">
                                            <i class="fas fa-check-circle me-2"></i>I Have Paid
                                        </button>
                                        <a href="userpayment.php" class="btn btn-link w-100 mt-2 text-decoration-none text-muted">Cancel Payment</a>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            <h5 class="panel-title mb-4"><i class="fas fa-shopping-bag me-2"></i>Final Order Confirmation</h5>
                            
                            <div class="order-details-box bg-light p-3 rounded mb-4">
                                <p class="mb-2"><strong>Payment Method:</strong> <?php echo strtoupper($_SESSION['temp_payment_method']); ?></p>
                                <p class="mb-2 text-pink"><strong>Total Amount:</strong> ₹<?php echo number_format($total_amount, 2); ?></p>
                                <p class="mb-0 text-muted small">Please click the button below to finalize your purchase.</p>
                            </div>

                            <form method="POST" action="">
                                <button type="submit" name="place_order" class="btn btn-pink w-100 py-3 rounded-pill shadow-sm">
                                    <i class="fas fa-check-circle me-2"></i>Confirm Order
                                </button>
                                <a href="userpayment.php" class="btn btn-link w-100 mt-2 text-decoration-none text-muted">Go Back</a>
                            </form>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <div class="payment-methods-card">
                        <h5 class="panel-title"><i class="fas fa-wallet me-2"></i>Select Payment Method</h5>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form id="paymentForm" method="POST" action="">
                            <!-- UPI Option -->
                            <label class="method-card" id="methodUpi" onclick="selectMethod('upi')">
                                <div class="d-flex align-items-center">
                                    <input type="radio" name="payment_method" value="upi" class="method-radio" id="radioUpi" required>
                                    <div class="method-icon ms-2 me-3">
                                        <i class="fas fa-mobile-alt"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="method-name mb-0">UPI</p>
                                        <p class="method-detail">Pay using GPay, PhonePe, Paytm, BHIM & more</p>
                                    </div>
                                </div>

                                <!-- UPI Details (shown when selected) -->
                                <div class="upi-details d-none" id="upiDetails">
                                    <hr style="border-top:1.5px solid #f0e0e6; margin:1rem 0;">
                                    <div class="upi-icon-grid">
                                        <div class="upi-icon-item active">GPay</div>
                                        <div class="upi-icon-item">PhonePe</div>
                                        <div class="upi-icon-item">Paytm</div>
                                        <div class="upi-icon-item">BHIM</div>
                                    </div>
                                    <label class="panel-label">Enter UPI ID</label>
                                    <input type="text" name="upi_id" class="form-control panel-input" id="upiIdInput"
                                           placeholder="yourname@upi" onclick="event.stopPropagation()">
                                </div>
                            </label>

                            <!-- COD Option -->
                            <label class="method-card" id="methodCod" onclick="selectMethod('cod')">
                                <div class="d-flex align-items-center">
                                    <input type="radio" name="payment_method" value="cod" class="method-radio" id="radioCod">
                                    <div class="method-icon ms-2 me-3">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="method-name mb-0">Cash on Delivery</p>
                                        <p class="method-detail">Pay with cash when your order arrives</p>
                                    </div>
                                </div>

                                <!-- COD Details (shown when selected) -->
                                <div class="cod-details d-none" id="codDetails">
                                    <hr style="border-top:1.5px solid #f0e0e6; margin:1rem 0;">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-soft-pink text-pink p-2 px-3">
                                            <i class="fas fa-check-circle me-1"></i> Always Available
                                        </span>
                                        <span class="text-muted small">No extra charges</span>
                                    </div>
                                </div>
                            </label>

                            <!-- Confirm Button -->
                            <button type="button" name="confirm_payment_btn" class="btn btn-pink w-100 mt-4" id="confirmBtn" onclick="submitPaymentForm()">
                                <i class="fas fa-lock me-2"></i>Confirm Payment Method
                            </button>
                            <input type="hidden" name="confirm_payment" value="1">
                        </form>

                        <!-- Security Note -->
                        <div class="security-note">
                            <i class="fas fa-shield-alt"></i>
                            <span>Your payment info is encrypted and secure. We never share your details.</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

</main>

<!-- ================= FOOTER ================= -->
<footer class="premium-footer">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-5 mb-3 mb-md-0">
                <h5 class="footer-brand mb-1">🧶 Crocheting Hub</h5>
                <p class="footer-tagline mb-0">Handcrafted with love & yarn</p>
            </div>
            <div class="col-md-2 text-md-center mb-3 mb-md-0">
                <div class="footer-social">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-pinterest"></i></a>
                    <a href="#"><i class="fab fa-x-twitter"></i></a>
                </div>
            </div>
            <div class="col-md-5 text-md-end">
                <p class="footer-copy mb-0">&copy; 2025 Crocheting Hub. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
(function () {
    "use strict";

    /* ---------- Select Payment Method ---------- */
    window.selectMethod = function(type) {
        // Check the radio
        document.getElementById('radioUpi').checked = (type === 'upi');
        document.getElementById('radioCod').checked = (type === 'cod');

        // Toggle selected card styling
        document.getElementById('methodUpi').classList.toggle('method-card--selected', type === 'upi');
        document.getElementById('methodCod').classList.toggle('method-card--selected', type === 'cod');

        // Show/hide details
        document.getElementById('upiDetails').classList.toggle('d-none', type !== 'upi');
        document.getElementById('codDetails').classList.toggle('d-none', type !== 'cod');
    };

    /* ---------- UPI icon selection ---------- */
    document.querySelectorAll('.upi-icon-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.stopPropagation();
            document.querySelectorAll('.upi-icon-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
        });
    });

    /* ---------- Submit Payment Form ---------- */
    window.submitPaymentForm = function() {
        const selected = document.querySelector('input[name="payment_method"]:checked');
        if (!selected) {
            alert('Please select a payment method.');
            return;
        }

        if (selected.value === 'upi') {
            const upiId = document.getElementById('upiIdInput').value.trim();
            if (!upiId) {
                alert('Please enter your UPI ID.');
                return;
            }
        }
        
        // Submit the form
        document.getElementById('paymentForm').submit();
    };

    /* ---------- Payment Timer ---------- */
    function startTimer() {
        let timerDisplay = document.getElementById('paymentTimer');
        if (!timerDisplay) return;

        let timeLeft = 90; // 1:30

        const countdown = setInterval(() => {
            if (timeLeft <= 0) {
                clearInterval(countdown);
                timerDisplay.innerHTML = "Expired";
                timerDisplay.classList.remove('text-danger');
                timerDisplay.classList.add('text-muted');
            } else {
                let minutes = Math.floor(timeLeft / 60);
                let seconds = timeLeft % 60;
                timerDisplay.innerHTML = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
                timeLeft--;
            }
        }, 1000);
    }

    // Start timer on page load if the element exists
    document.addEventListener('DOMContentLoaded', startTimer);

})();
</script>

</body>
</html>
