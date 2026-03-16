<?php
session_name('USER_SESSION');
session_start();

/* ================= DATABASE CONNECTION ================= */
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

/* ================= USER SESSION ================= */
$user_id = $_SESSION['user_id'] ?? 1;

/* ================= REMOVE FROM CART ================= */
if (isset($_POST['remove_product_id'])) {
    $pid = $_POST['remove_product_id'];
    mysqli_query(
        $conn,
        "DELETE FROM cart WHERE user_id='$user_id' AND product_id='$pid'"
    );
}

/* ================= FETCH CART ITEMS ================= */
$cart_sql = "
    SELECT
        c.quantity,
        p.id AS pid,
        p.name,
        p.price,
        p.image,
        p.category,
        p.stock,
        IFNULL(p.shipping_charges, 0) AS shipping_charges
    FROM cart c
    JOIN sellerproducts p ON c.product_id = p.id
    WHERE c.user_id = '$user_id'
";

$cart_result = mysqli_query($conn, $cart_sql);
$cart_count  = ($cart_result !== false) ? mysqli_num_rows($cart_result) : 0;

// Sum shipping charges of all cart items (server-side for JS)
$total_shipping = 0;
if ($cart_count > 0) {
    $tmp = mysqli_query($conn, "
        SELECT SUM(IFNULL(p.shipping_charges, 0)) AS total_ship
        FROM cart c
        JOIN sellerproducts p ON c.product_id = p.id
        WHERE c.user_id = '$user_id'
    ");
    if ($tmp) {
        $ts = mysqli_fetch_assoc($tmp);
        $total_shipping = floatval($ts['total_ship']);
    }
    mysqli_data_seek($cart_result, 0); // reset pointer for later loop
}

// CHECK FOR ANY STOCK ERRORS
$has_stock_error = false;
$stock_error_msg = "";
if ($cart_count > 0) {
    while ($item = mysqli_fetch_assoc($cart_result)) {
        if ($item['stock'] <= 0) {
            $has_stock_error = true;
            $stock_error_msg = "Some items in your cart are out of stock.";
            break;
        }
        if ($item['quantity'] > $item['stock']) {
            $has_stock_error = true;
            $stock_error_msg = "You have more items in your cart than available in stock.";
            break;
        }
    }
    mysqli_data_seek($cart_result, 0);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Your Shopping Cart – Crocheting Hub</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet" />
    <!-- Custom Cart CSS -->
    <link rel="stylesheet" href="../assets/css/usercart.css" />
</head>
<body>

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


            <!-- Other Options with Icons -->
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
                    <a class="nav-link nav-link--active position-relative" href="usercart.php">
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

<main>
    <section class="cart-hero">
        <div class="container">
            <h1 class="section-title">Your Shopping Cart 🛒</h1>
            <p class="cart-subtitle">Review your selected crochet items</p>
        </div>
    </section>

    <section class="cart-body-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">

                    <?php if ($cart_count == 0): ?>
                        <div id="emptyCartMsg" class="empty-cart-box">
                            <i class="fas fa-shopping-cart empty-cart-icon"></i>
                            <h5 class="mt-3 mb-1">Your cart is empty</h5>
                            <p class="text-muted mb-3">Looks like you haven't added anything yet. Start exploring!</p>
                            <a href="user.php" class="btn btn-pink">Browse Products</a>
                        </div>
                    <?php else: ?>
                        <?php if ($has_stock_error): ?>
                            <div class="alert alert-danger shadow-sm border-0 mb-4 animate__animated animate__headShake">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?php echo $stock_error_msg; ?>
                            </div>
                        <?php endif; ?>
                        <div id="cartItemsList">
                            <?php
                            while ($item = mysqli_fetch_assoc($cart_result)) {
                                $image_path = !empty($item['image']) ? "../seller/uploads/" . htmlspecialchars($item['image']) : "https://via.placeholder.com/80?text=No+Img";
                            ?>
                                <div class="card mb-3 border-0 shadow-sm cart-item-card <?php echo ($item['stock'] <= 0) ? 'item-oos' : ''; ?>" 
                                     data-price="<?php echo $item['price']; ?>"
                                     data-stock="<?php echo $item['stock']; ?>">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <!-- Image -->
                                            <img src="<?php echo $image_path; ?>" alt="Product" class="rounded me-3" style="width: 80px; height: 80px; object-fit: cover;">
                                            
                                            <!-- Details -->
                                            <div class="flex-grow-1">
                                                <h5 class="card-title mb-1"><?php echo htmlspecialchars($item['name']); ?></h5>
                                                <p class="text-muted small mb-0"><?php echo htmlspecialchars($item['category']); ?></p>
                                                <h6 class="text-pink fw-bold mt-1">₹<?php echo number_format($item['price'], 2); ?></h6>
                                                <?php if($item['stock'] <= 0): ?>
                                                    <span class="badge bg-danger rounded-pill mt-1">Sold Out</span>
                                                <?php elseif($item['stock'] <= 5): ?>
                                                    <span class="badge bg-warning text-dark rounded-pill mt-1">Only <?php echo $item['stock']; ?> left!</span>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Quantity Display -->
                                            <div class="d-flex align-items-center bg-light rounded-pill px-3 py-1 mx-3">
                                                <button class="btn btn-sm btn-link text-dark p-0 me-2" onclick="changeQty(this, -1)"><i class="bi bi-dash"></i></button>
                                                <span class="text-muted small me-2">Qty:</span>
                                                <span class="fw-bold qty-value me-2"><?php echo $item['quantity']; ?></span>
                                                <button class="btn btn-sm btn-link text-dark p-0" onclick="changeQty(this, 1)"><i class="bi bi-plus"></i></button>
                                            </div>

                                            <!-- Remove Button (Placeholder action for now) -->
                                             <form method="POST" class="ms-2">
                                                <input type="hidden" name="remove_product_id" value="<?php echo $item['pid']; ?>">
                                                <button 
                                                    type="submit" 
                                                    class="btn btn-link text-danger p-0"
                                                    onclick="return confirm('Remove this item from cart?');">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>

                                        </div>
                                    </div>
                                </div>
                            <?php
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>

                    

                <div class="col-lg-4">
                    <div class="order-summary-card sticky-top" style="top:24px;">
                        <h5 class="summary-title"><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                        <hr class="summary-divider" />

                        <div class="summary-row"><span>Subtotal</span><span id="subtotalVal">₹0.00</span></div>
                        <div class="summary-row"><span>Shipping</span><span id="shippingVal"><?php echo $total_shipping > 0 ? '₹' . number_format($total_shipping, 2) : '₹0.00'; ?></span></div>
                        <div class="summary-row"><span>Tax (5%)</span><span id="taxVal">₹0.00</span></div>

                        <hr class="summary-divider" />

                        <div class="summary-row summary-total"><span>Total</span><span id="totalVal">₹0.00</span></div>

                        <a href="userpayment.php" class="btn btn-pink w-100 mt-4 checkout-btn <?php echo ($has_stock_error || $cart_count == 0) ? 'disabled' : ''; ?>" id="checkoutBtn">
                            <i class="fas fa-lock me-2"></i>Proceed to Checkout
                        </a>

                        <p class="summary-note mt-3"><i class="fas fa-shield-alt me-1"></i>Secure checkout – your details are safe</p>
                    </div>
                </div>

            </div>
        </div>
    </section>
</main>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    (function () {
        const SHIPPING = <?php echo json_encode($total_shipping); ?>;
        const TAX_RATE = 0.05;

        // Formatter
        const fmtINR = (n) => "₹" + n.toLocaleString("en-IN", { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        // Calculate Totals
        function updateSummary() {
            let subtotal = 0;
            const items = document.querySelectorAll(".cart-item-card");
            
            items.forEach(item => {
                const price = parseFloat(item.dataset.price);
                const qty = parseInt(item.querySelector(".qty-value").innerText);
                subtotal += price * qty;
            });

            const tax = subtotal * TAX_RATE;
            // Only apply shipping if cart is not empty (subtotal > 0)
            const shipping = subtotal > 0 ? SHIPPING : 0;
            const total = subtotal + tax + shipping;

            // Update DOM
            document.getElementById("subtotalVal").innerText = fmtINR(subtotal);
            document.getElementById("shippingVal").innerText = shipping > 0 ? fmtINR(shipping) : "₹0.00";
            document.getElementById("taxVal").innerText = fmtINR(tax);
            document.getElementById("totalVal").innerText = fmtINR(total);
        }

        // Quantity Change Handler
        window.changeQty = function(btn, delta) {
            const card = btn.closest('.cart-item-card');
            const maxStock = parseInt(card.dataset.stock);
            const qtySpan = card.querySelector('.qty-value');
            let currentQty = parseInt(qtySpan.innerText);
            let newQty = currentQty + delta;
            
            if (newQty < 1) return; 
            if (newQty > maxStock) {
                alert("Only " + maxStock + " units available in stock.");
                return;
            }
            
            qtySpan.innerText = newQty;
            updateSummary(); // Recalculate totals
        };

        // Initialize on Load
        document.addEventListener("DOMContentLoaded", updateSummary);

        // Optional: Handle remove button (visual only for now)
        document.querySelectorAll(".remove-btn").forEach(btn => {
            btn.addEventListener("click", function() {
                if(confirm("Remove this item?")) {
                    this.closest(".cart-item-card").remove();
                    updateSummary();
                    
                    // Check if empty
                    if(document.querySelectorAll(".cart-item-card").length === 0) {
                        location.reload(); // Reload to show PHP empty state
                    }
                }
            });
        });

    })();
</script>

</body>
</html>
