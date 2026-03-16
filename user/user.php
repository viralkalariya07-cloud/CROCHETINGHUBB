<?php
session_name('USER_SESSION');
session_start();
// Database connection
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Create cart table if not exists (Temporary solution to ensure table exists)
$sql_cart = "CREATE TABLE IF NOT EXISTS cart (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    quantity INT(11) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cart_item (user_id, product_id)
)";
mysqli_query($conn, $sql_cart);

// Handle Add to Cart
if (isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    $user_id = $_SESSION['user_id'];

    // Check if product exists and has stock
    $check_stock = mysqli_query($conn, "SELECT stock FROM sellerproducts WHERE id = $product_id");
    if ($check_stock && mysqli_num_rows($check_stock) > 0) {
        $product_data = mysqli_fetch_assoc($check_stock);
        if ($product_data['stock'] > 0) {
            // Check how many are already in the cart
            $cart_check = mysqli_query($conn, "SELECT quantity FROM cart WHERE user_id = $user_id AND product_id = $product_id");
            $existing_qty = 0;
            if ($cart_check && mysqli_num_rows($cart_check) > 0) {
                $cart_item = mysqli_fetch_assoc($cart_check);
                $existing_qty = $cart_item['quantity'];
            }

            if (($existing_qty + 1) > $product_data['stock']) {
                echo "<script>alert('Cannot add more. Only " . $product_data['stock'] . " units available.');</script>";
            } else {
                // Insert or Update Cart
                $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE quantity = quantity + 1");
                $stmt->bind_param("ii", $user_id, $product_id);
                if ($stmt->execute()) {
                    echo "<script>alert('Product added to cart!'); window.location.href='usercart.php';</script>";
                } else {
                    echo "<script>alert('Error adding to cart');</script>";
                }
                $stmt->close();
            }
        } else {
            echo "<script>alert('Sorry, this product is out of stock.');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Home | CrochetingHubb</title>
    <meta name="description" content="Explore our curated collection of premium handmade crochet products.">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/user.css">

    <style>
        /* ── HERO ── */
        /* ===== RESET (IMPORTANT) ===== */
html, body {
    margin: 0;
    padding: 0;
}

/* ===== HERO FIX ===== */
.welcome-section {
    position: relative;
    width: 100%;
    min-height: 100vh;          /* 🔥 THIS IS THE KEY FIX */
    display: flex;
    align-items: center;
    justify-content: center;

    padding: 120px 20px;
    text-align: center;

    background: linear-gradient(135deg, #fff0f6, #ffe3ec);
    overflow: visible;          /* 🔥 prevent clipping */
}

/* container */
.welcome-section .container {
    position: relative;
    z-index: 2;
    max-width: 1100px;
    margin: auto;
}

/* badge */
.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 16px;
    background: rgba(216, 49, 116, 0.15);
    color: #d83174;
    font-weight: 600;
    border-radius: 50px;
    margin-bottom: 18px;
}

/* heading */
.welcome-section h1 {
    font-size: 3.2rem;
    font-weight: 800;
    line-height: 1.2;
    color: #1f2937;
}

.welcome-section h1 span {
    color: #d83174;
}

/* subtitle */
.welcome-section .sub {
    max-width: 650px;
    margin: 18px auto 30px;
    font-size: 1.05rem;
    color: #6b7280;
}

/* buttons */
.btn-pink {
    background: #d83174;
    color: #fff;
    border: none;
}

.btn-pink:hover {
    background: #c02664;
}

/* decorations */
.yarn-deco {
    position: absolute;
    font-size: 3rem;
    opacity: 0.15;
    animation: floatY 6s ease-in-out infinite;
    pointer-events: none;
    z-index: 1;
}

/* animation */
@keyframes floatY {
    0% { transform: translateY(0); }
    50% { transform: translateY(-15px); }
    100% { transform: translateY(0); }
}

/* responsive */
@media (max-width: 768px) {
    .welcome-section h1 {
        font-size: 2.2rem;
    }
}
        /* ── PRODUCT CARD ENHANCEMENTS ── */
        .product-card { border-radius: 20px; overflow: hidden; border: 1.5px solid rgba(216,49,116,.07);
            box-shadow: 0 4px 18px rgba(0,0,0,.05); transition: all .35s ease; background:#fff; }
        .product-card:hover { transform: translateY(-8px); box-shadow: 0 18px 40px rgba(216,49,116,.15); border-color: rgba(216,49,116,.18); }

        .product-img-wrapper { position: relative; overflow: hidden; height: 220px; }
        .product-img { width:100%; height:100%; object-fit: cover; transition: transform .5s ease; }
        .product-card:hover .product-img { transform: scale(1.08); }

        /* hover-reveal overlay */
        .img-overlay { position: absolute; inset:0; background: rgba(216,49,116,.55);
            display: flex; align-items:center; justify-content:center;
            opacity: 0; transition: opacity .3s; }
        .product-card:hover .img-overlay { opacity: 1; }
        .overlay-btn { color:#fff; border:2px solid #fff; border-radius:30px;
            padding: 8px 22px; font-weight:700; font-size:.85rem; text-decoration:none;
            backdrop-filter: blur(4px); }

        /* category badge on image */
        .cat-badge {
            position: absolute; top: 12px; left: 12px;
            background: rgba(255,255,255,.92); color: var(--primary-pink);
            border-radius: 20px; padding: 3px 12px; font-size: .72rem;
            font-weight: 700; letter-spacing: .3px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }
        /* stock pill */
        .stock-pill {
            font-size: .72rem; font-weight: 600; border-radius: 20px;
            padding: 3px 10px; display: inline-flex; align-items: center; gap: 4px;
        }
        .stock-pill.in  { background:#e8f5e9; color:#2e7d32; }
        .stock-pill.out { background:#fce4ec; color:#c62828; }

        /* section header */
        .products-header { text-align: center; margin-bottom: 48px; }
        .products-header .pre-label { font-size:.75rem; font-weight:700; letter-spacing:2px;
            text-transform:uppercase; color:var(--primary-pink); margin-bottom:8px; }
        .products-header h2 { font-family:var(--font-heading); font-size:clamp(1.8rem,3vw,2.5rem);
            font-weight:800; color:#1a1a2e; }
        .products-header p { color:#777; }
    </style>
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
                <!-- Other Options with Icons -->
                <ul class="navbar-nav ms-lg-auto align-items-lg-center gap-lg-1">
                    <li class="nav-item">
                        <a class="nav-link" href="user.php"><i class="bi bi-house-door me-1"></i>Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-grid me-1"></i>Categories
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="user.php">All Categories</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php
                            // Show ONLY categories added by sellers in category.php
                            // seller_id > 0 means it was added by a real seller (not system/default)
                            $cat_query = mysqli_query($conn, "SELECT * FROM categories WHERE seller_id > 0 ORDER BY name ASC");
                            if ($cat_query && mysqli_num_rows($cat_query) > 0) {
                                while ($cat = mysqli_fetch_assoc($cat_query)) {
                                    $cat_name = htmlspecialchars($cat['name']);
                                    $active_class = (isset($_GET['category']) && $_GET['category'] == $cat['name']) ? ' active' : '';
                                    echo '<li><a class="dropdown-item' . $active_class . '" href="user.php?category=' . urlencode($cat['name']) . '">' . $cat_name . '</a></li>';
                                }
                            } else {
                                echo '<li><span class="dropdown-item text-muted">No categories yet</span></li>';
                            }
                            ?>
                        </ul>

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

    <!-- Welcome Hero -->
    <header class="welcome-section">
        <span class="yarn-deco" style="top:12%;left:4%;">🧶</span>
        <span class="yarn-deco" style="bottom:10%;right:5%;animation-delay:2s;">🎀</span>
        <div class="container" style="position:relative;z-index:1;">
            <div class="hero-badge"><i class="bi bi-stars"></i> Handmade Collections</div>
            <h1>Discover <span>Handmade</span><br>Crochet Treasures</h1>
            <p class="sub">Explore our curated collection of premium handmade creations from talented artists around the world.</p>
            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="usercart.php" class="btn btn-pink px-4 py-2 rounded-pill fw-bold">
                    <i class="bi bi-cart3 me-2"></i>View Cart
                </a>
                <a href="useraccount.php" class="btn btn-outline-light px-4 py-2 rounded-pill fw-bold" style="border-color:rgba(216,49,116,.4);color:var(--primary-pink);background:rgba(255,255,255,.6);">
                    <i class="bi bi-person-circle me-2"></i>My Account
                </a>
            </div>
        </div>
    </header>

    <main class="container py-5">
        <div class="products-header">
            <p class="pre-label">Fresh Arrivals</p>
            <h2>Explore Our Collections</h2>
            <p>Discover unique handmade items crafted just for you</p>
        </div>
        
        <?php
        $category_filter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
        
        if ($category_filter && $category_filter != 'All Categories') {
             $sql = "SELECT * FROM sellerproducts WHERE category = '$category_filter' ORDER BY id DESC";
             echo '<div class="mb-4 text-center"><span class="badge bg-pink text-white fs-6">Category: ' . htmlspecialchars($category_filter) . ' <a href="user.php" class="text-white ms-2 text-decoration-none"><i class="bi bi-x-circle"></i></a></span></div>';
        } else {
             $sql = "SELECT * FROM sellerproducts ORDER BY id DESC";
        }
        
        $result = mysqli_query($conn, $sql);
        ?>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($product = mysqli_fetch_assoc($result)) {
            ?>
                <div class="col">
                <div class="card product-card h-100 border-0">

                    <!-- Image with overlay -->
                    <div class="product-img-wrapper">
                        <?php if (!empty($product['image'])): ?>
                            <img src="../seller/uploads/<?php echo htmlspecialchars($product['image']); ?>" class="product-img" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center bg-light product-img text-muted" style="height:220px;font-size:2.5rem;">🧶</div>
                        <?php endif; ?>
                        <!-- Category badge -->
                        <span class="cat-badge"><?php echo htmlspecialchars($product['category']); ?></span>
                        <!-- Hover overlay -->
                        <div class="img-overlay">
                            <a href="productdetails.php?id=<?php echo $product['id']; ?>" class="overlay-btn">
                                <i class="bi bi-eye me-1"></i>View Details
                            </a>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="card-body d-flex flex-column px-3 pt-3 pb-1">
                        <a href="productdetails.php?id=<?php echo $product['id']; ?>" class="text-decoration-none">
                            <h5 class="card-title fw-bold text-dark mb-1" style="font-size:1rem;"><?php echo htmlspecialchars($product['name']); ?></h5>
                        </a>
                        <div class="mt-auto d-flex justify-content-between align-items-center mt-2">
                            <span class="fw-bold" style="color:var(--primary-pink);font-size:1.1rem;">₹<?php echo number_format($product['price'], 2); ?></span>
                            <?php if ($product['stock'] > 0): ?>
                                <span class="stock-pill in"><i class="bi bi-check-circle-fill"></i><?php echo $product['stock']; ?> left</span>
                            <?php else: ?>
                                <span class="stock-pill out"><i class="bi bi-x-circle-fill"></i>Sold Out</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="card-footer bg-white border-0 px-3 pb-3 pt-2">
                        <form method="post" action="">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" name="add_to_cart" class="btn btn-pink w-100 rounded-pill"
                                <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                                <i class="bi bi-cart-plus me-2"></i><?php echo $product['stock'] > 0 ? 'Add to Cart' : 'Out of Stock'; ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php
                }
            } else {
            ?>
                <div class="col-12 text-center py-5">
                    <div class="text-muted">
                        <i class="bi bi-box-seam display-1"></i>
                        <p class="mt-3 fs-5">No products available at the moment.</p>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="premium-footer">
        <div class="container">
            <div class="row gy-4">
                <div class="col-12 col-lg-6 text-center text-lg-start">
                    <a href="#" class="footer-brand">🧶 CrochetingHubb</a>
                    <p class="mb-0 opacity-75">Connect with the crochet community and find the best handmade treasures.</p>
                </div>
                <div class="col-12 col-lg-6 text-center text-lg-end">
                    <div class="footer-social-links mb-3">
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-youtube"></i></a>
                    </div>
                    <div class="footer-bottom p-0 border-0">
                        <p class="mb-0 opacity-75">&copy; <?php echo date("Y"); ?> CrochetingHubb. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
