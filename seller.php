<?php
session_name('SELLER_SESSION');
session_start();
include("includes/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: user/login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];
$seller_name = $_SESSION['name'] ?? 'Seller';

// Get seller email
$seller_email = "";
$res = mysqli_query($conn, "SELECT email FROM users WHERE id = '$seller_id'");
if ($res && $u = mysqli_fetch_assoc($res)) {
    $seller_email = $u['email'];
}

// Get Stats — safe wrappers so a missing table returns 0 instead of a fatal error
$_r1 = mysqli_query($conn, "SELECT COUNT(*) as total FROM sellerproducts WHERE seller_id = '$seller_id'");
$totalProducts = ($_r1 && $row = mysqli_fetch_assoc($_r1)) ? (int)$row['total'] : 0;

$_r2 = mysqli_query($conn, "SELECT COUNT(*) as total FROM orderss WHERE seller_id = '$seller_id'");
$totalOrders = ($_r2 && $row = mysqli_fetch_assoc($_r2)) ? (int)$row['total'] : 0;

$_r3 = mysqli_query($conn, "SELECT SUM(total_price) as total FROM orderss WHERE seller_id = '$seller_id'");
$totalRevenue = ($_r3 && $row = mysqli_fetch_assoc($_r3)) ? (float)($row['total'] ?? 0) : 0;

// Review count — seller_feedback table may not exist yet
$_reviewQ = "SELECT COUNT(*) as total FROM seller_feedback sf
             JOIN orderss o ON sf.order_id = o.id
             WHERE o.seller_id = '$seller_id'";
$_r4 = mysqli_query($conn, $_reviewQ);
$totalReviews = ($_r4 && $row = mysqli_fetch_assoc($_r4)) ? (int)$row['total'] : 0;
?>
<?php 
$hide_nav = true;
include("includes/seller_header.php"); 
?>

<style>
    /* ── HERO ── */
    .hero-section {
        background: linear-gradient(145deg, #fff0f6 0%, #fffcfd 55%, #fce4ef 100%);
        padding: 70px 0 90px;
        text-align: center;
        position: relative;
        overflow: hidden;
        border-bottom: 1px solid rgba(216,49,116,.08);
    }
    .hero-section::before {
        content:''; position:absolute;
        width:500px; height:500px;
        background:radial-gradient(circle,rgba(216,49,116,.10) 0%,transparent 70%);
        top:-130px; right:-100px; border-radius:50%; pointer-events:none;
    }
    .hero-section::after {
        content:''; position:absolute;
        width:320px; height:320px;
        background:radial-gradient(circle,rgba(216,49,116,.07) 0%,transparent 70%);
        bottom:-80px; left:-60px; border-radius:50%; pointer-events:none;
    }
    .seller-avatar {
        width:72px; height:72px; border-radius:50%;
        background: linear-gradient(135deg, #d83174, #ff6b9d);
        color:#fff; font-size:1.9rem; font-weight:800;
        display:inline-flex; align-items:center; justify-content:center;
        box-shadow:0 8px 24px rgba(216,49,116,.30); margin-bottom:18px;
    }
    .hero-section h1 {
        font-family: 'Playfair Display', serif;
        font-size: clamp(1.8rem, 3.5vw, 2.8rem);
        font-weight: 800; color: #1a1a2e; margin-bottom: 8px;
    }
    .hero-section h1 span { color: #d83174; }
    .hero-section .hero-email {
        display:inline-flex; align-items:center; gap:6px;
        background:rgba(216,49,116,.06); border:1px solid rgba(216,49,116,.14);
        color:#d83174; border-radius:30px; padding:4px 16px;
        font-size:.82rem; font-weight:600; margin-bottom:10px;
    }
    .yarn-float { position:absolute; font-size:3rem; opacity:.09; pointer-events:none;
        animation:floatY 5s ease-in-out infinite alternate; }
    @keyframes floatY { from{transform:translateY(0)} to{transform:translateY(-16px)} }

    /* ── STATS ── */
    .stats-section { margin-top:-40px; padding-bottom:30px; position:relative; z-index:2; }
    .stat-item {
        background:#fff; border-radius:20px;
        box-shadow:0 8px 28px rgba(0,0,0,.07);
        padding:24px 20px; text-align:center;
        transition:.3s; border:1.5px solid rgba(216,49,116,.06);
        overflow:hidden; position:relative;
    }
    .stat-item::before {
        content:''; position:absolute; top:0; left:0; right:0;
        height:4px; background:linear-gradient(90deg,#d83174,#ff6b9d);
        border-radius:20px 20px 0 0;
    }
    .stat-item:hover { transform:translateY(-8px); box-shadow:0 16px 40px rgba(216,49,116,.14); border-color:rgba(216,49,116,.18); }
    .stat-icon { font-size:1.8rem; color:#d83174; margin-bottom:8px; }
    .stat-number { font-size:1.7rem; font-weight:800; color:#1a1a2e; margin-bottom:2px; }
    .stat-label { font-size:.78rem; font-weight:600; letter-spacing:.5px; text-transform:uppercase; color:#999; }

    /* ── DASHBOARD SECTION ── */
    .dashboard-section { padding:60px 0 80px; }
    .section-label { font-size:.75rem; font-weight:700; letter-spacing:2px;
        text-transform:uppercase; color:#d83174; margin-bottom:8px; }
    .section-title {
        font-family:'Playfair Display',serif; font-size:clamp(1.8rem,3vw,2.4rem);
        font-weight:800; color:#1a1a2e; margin-bottom:40px;
    }

    .dashboard-card {
        background:#fff; border-radius:20px;
        box-shadow:0 6px 24px rgba(0,0,0,.06);
        padding:32px 28px; height:100%;
        border:1.5px solid rgba(216,49,116,.06);
        border-left:4px solid #d83174;
        transition:.35s ease;
        display:flex; flex-direction:column;
    }
    .dashboard-card:hover {
        transform:translateY(-8px);
        box-shadow:0 18px 44px rgba(216,49,116,.14);
        border-left-color:#ff6b9d;
    }
    .card-icon {
        width:56px; height:56px;
        background:linear-gradient(135deg,rgba(216,49,116,.10),rgba(255,107,157,.08));
        color:#d83174; border-radius:16px;
        display:flex; align-items:center; justify-content:center;
        font-size:1.6rem; margin-bottom:18px;
        transition:.3s;
    }
    .dashboard-card:hover .card-icon {
        background:linear-gradient(135deg,#d83174,#ff6b9d);
        color:#fff; transform:scale(1.1);
    }
    .dashboard-card h3 { font-size:1.1rem; font-weight:700; color:#1a1a2e; margin-bottom:8px; }
    .dashboard-card p  { font-size:.88rem; color:#777; line-height:1.6; flex:1; }
    .btn-custom {
        display:inline-flex; align-items:center; gap:8px;
        padding:10px 24px; background:linear-gradient(135deg,#d83174,#ff6b9d);
        color:#fff; border-radius:30px; text-decoration:none;
        font-weight:700; font-size:.85rem;
        box-shadow:0 4px 14px rgba(216,49,116,.25);
        transition:.3s; margin-top:16px; width:fit-content;
    }
    .btn-custom:hover { transform:translateY(-2px); box-shadow:0 8px 22px rgba(216,49,116,.35); color:#fff; }
</style>

<!-- HERO SECTION -->
<section class="hero-section">
    <span class="yarn-float" style="top:15%;left:5%;">🧶</span>
    <span class="yarn-float" style="bottom:12%;right:6%;animation-delay:2s;">🎀</span>
    <div class="container" style="position:relative;z-index:1;">
        <div class="seller-avatar"><?php echo strtoupper(substr($seller_name, 0, 1)); ?></div>
        <h1>Welcome back, <span><?php echo htmlspecialchars($seller_name); ?></span>! ❤️</h1>
        <div class="hero-email"><i class="bi bi-envelope-fill"></i><?php echo htmlspecialchars($seller_email); ?></div>
        <p class="text-muted mt-2">Manage your handmade crochet products and grow your business</p>
    </div>
</section>

<!-- STATS SECTION -->
<section class="stats-section">
    <div class="container">
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-icon"><i class="bi bi-bag-check-fill"></i></div>
                    <div class="stat-number"><?php echo $totalProducts; ?></div>
                    <div class="stat-label">Total Products</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-icon"><i class="bi bi-cart-check-fill"></i></div>
                    <div class="stat-number"><?php echo $totalOrders; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-icon"><i class="bi bi-currency-rupee"></i></div>
                    <div class="stat-number"><?php echo number_format($totalRevenue, 0); ?></div>
                    <div class="stat-label">Revenue</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-icon"><i class="bi bi-star-fill"></i></div>
                    <div class="stat-number"><?php echo $totalReviews; ?></div>
                    <div class="stat-label">Reviews</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- DASHBOARD SECTION -->
<section class="dashboard-section">
    <div class="container">
        <div class="text-center mb-5">
            <p class="section-label">Your Panel</p>
            <h2 class="section-title">Seller Dashboard</h2>
        </div>

        <div class="row g-4">
            <!-- Products -->
            <div class="col-lg-4 col-md-6">
                <div class="dashboard-card">
                    <div class="card-icon"><i class="bi bi-bag-heart-fill"></i></div>
                    <h3>Products</h3>
                    <p>Add, edit, and manage your handmade crochet products. Upload images and set prices.</p>
                    <a href="seller/sellerproducts.php" class="btn-custom"><i class="bi bi-arrow-right-circle"></i>Manage Products</a>
                </div>
            </div>
            <!-- Categories -->
            <div class="col-lg-4 col-md-6">
                <div class="dashboard-card">
                    <div class="card-icon"><i class="bi bi-tags-fill"></i></div>
                    <h3>Categories</h3>
                    <p>Organize your products into categories like amigurumi, blankets, accessories, and more.</p>
                    <a href="seller/category.php" class="btn-custom"><i class="bi bi-arrow-right-circle"></i>Manage Categories</a>
                </div>
            </div>
            <!-- Orders -->
            <div class="col-lg-4 col-md-6">
                <div class="dashboard-card">
                    <div class="card-icon"><i class="bi bi-box-seam-fill"></i></div>
                    <h3>Orders</h3>
                    <p>View and process customer orders. Track order status and manage deliveries.</p>
                    <a href="seller/sellerorder.php" class="btn-custom"><i class="bi bi-arrow-right-circle"></i>View Orders</a>
                </div>
            </div>
            <!-- Contact -->
            <div class="col-lg-4 col-md-6">
                <div class="dashboard-card">
                    <div class="card-icon"><i class="bi bi-envelope-heart-fill"></i></div>
                    <h3>Contact</h3>
                    <p>Respond to customer inquiries and messages. Maintain great customer relationships.</p>
                    <a href="seller/sellercontact.php" class="btn-custom"><i class="bi bi-arrow-right-circle"></i>View Messages</a>
                </div>
            </div>
            <!-- Feedback -->
            <div class="col-lg-4 col-md-6">
                <div class="dashboard-card">
                    <div class="card-icon"><i class="bi bi-chat-heart-fill"></i></div>
                    <h3>Feedback</h3>
                    <p>Read customer reviews and feedback. Use insights to improve your products.</p>
                    <a href="seller/feedback.php" class="btn-custom"><i class="bi bi-arrow-right-circle"></i>View Feedback</a>
                </div>
            </div>
            <!-- Tutorials -->
            <div class="col-lg-4 col-md-6">
                <div class="dashboard-card">
                    <div class="card-icon"><i class="bi bi-journal-text"></i></div>
                    <h3>Tutorials</h3>
                    <p>Share your creativity with users and make your business big.</p>
                    <a href="tutorial.php" class="btn-custom"><i class="bi bi-arrow-right-circle"></i>Add Tutorials</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<?php include("includes/seller_footer.php"); ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
