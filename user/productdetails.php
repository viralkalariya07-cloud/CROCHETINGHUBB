<?php
session_name('USER_SESSION');
session_start();
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);
if (!$conn) die("Database connection failed: " . mysqli_connect_error());

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id <= 0) { header("Location: user.php"); exit(); }

// Fetch product + seller name
$sql = "SELECT sp.*, u.full_name AS seller_name
        FROM sellerproducts sp
        LEFT JOIN users u ON u.id = sp.seller_id
        WHERE sp.id = $product_id";
$result  = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);
if (!$product) { header("Location: user.php"); exit(); }

// Fetch all product images from product_images table
$imgs_res   = mysqli_query($conn, "SELECT * FROM product_images WHERE product_id = $product_id ORDER BY is_primary DESC, id ASC");
$all_images = [];
if ($imgs_res && mysqli_num_rows($imgs_res) > 0) {
    while ($r = mysqli_fetch_assoc($imgs_res)) $all_images[] = $r['image'];
}
// Fallback to legacy single image column
if (empty($all_images) && !empty($product['image'])) {
    $all_images[] = $product['image'];
}

// Handle Add to Cart
if (isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    $user_id = (int)$_SESSION['user_id'];
    $p_id    = (int)$_POST['product_id'];

    $check = mysqli_query($conn, "SELECT stock FROM sellerproducts WHERE id = $p_id");
    if ($check && mysqli_num_rows($check) > 0) {
        $pd = mysqli_fetch_assoc($check);
        if ($pd['stock'] > 0) {
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE quantity = quantity + 1");
            $stmt->bind_param("ii", $user_id, $p_id);
            if ($stmt->execute()) {
                echo "<script>alert('Product added to cart!'); window.location.href='usercart.php';</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Sorry, this product is out of stock.');</script>";
        }
    }
}

// Fetch related products (same category, excluding current)
$rel_sql  = "SELECT * FROM sellerproducts WHERE category = '" . mysqli_real_escape_string($conn, $product['category']) . "' AND id != $product_id AND stock > 0 LIMIT 4";
$rel_res  = mysqli_query($conn, $rel_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> | CrochetingHubb</title>
    <meta name="description" content="<?php echo htmlspecialchars(substr($product['description'] ?? '', 0, 155)); ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/user.css">

    <style>
        :root {
            --pink: #e8247e;
            --pink-light: #fff0f7;
            --pink-dark: #c01a68;
        }

        /* ── PRODUCT IMAGE ── */
        .product-image-wrapper {
            background: #fafafa;
            border-radius: 20px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(232,36,126,.10);
        }
        .product-image-wrapper img {
            width: 100%;
            max-height: 480px;
            object-fit: contain;
            border-radius: 14px;
            transition: transform .4s ease;
        }
        .product-image-wrapper:hover img { transform: scale(1.04); }

        .no-image-box {
            height: 380px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
            color: #ddd;
            background: #f9f9f9;
            border-radius: 14px;
        }

        /* ── INFO ── */
        .product-info { padding: 10px 0; }
        .category-badge {
            background: var(--pink-light);
            color: var(--pink);
            border: 1px solid #f9c8e0;
            border-radius: 30px;
            padding: 4px 16px;
            font-size: .8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 14px;
        }
        .product-title { font-size: 2rem; font-weight: 800; color: #1a1a2e; line-height: 1.2; }
        .price-tag { font-size: 2.2rem; font-weight: 800; color: var(--pink); }
        .stock-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 14px;
            border-radius: 30px;
            font-size: .82rem;
            font-weight: 600;
        }
        .stock-pill.in  { background: #e8f5e9; color: #2e7d32; }
        .stock-pill.out { background: #fce4ec; color: #c62828; }

        .seller-box {
            background: var(--pink-light);
            border: 1px solid #f9c8e0;
            border-radius: 14px;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .seller-avatar {
            width: 44px; height: 44px;
            background: var(--pink);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 1.2rem; font-weight: 700;
            flex-shrink: 0;
        }
        .seller-label { font-size: .75rem; color: #888; margin-bottom: 2px; }
        .seller-name  { font-size: .97rem; font-weight: 700; color: #333; }

        .description-box {
            background: #fff;
            border-left: 4px solid var(--pink);
            border-radius: 0 12px 12px 0;
            padding: 16px 20px;
            font-size: .97rem;
            color: #444;
            line-height: 1.7;
        }

        /* ── BUTTONS ── */
        .btn-add-cart {
            background: linear-gradient(135deg, var(--pink) 0%, var(--pink-dark) 100%);
            color: #fff;
            border: none;
            border-radius: 40px;
            padding: 14px 36px;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: .5px;
            transition: all .3s;
            box-shadow: 0 6px 20px rgba(232,36,126,.30);
        }
        .btn-add-cart:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(232,36,126,.45); color: #fff; }
        .btn-back {
            border: 2px solid var(--pink);
            color: var(--pink);
            border-radius: 40px;
            padding: 12px 28px;
            font-weight: 600;
            background: transparent;
            transition: all .3s;
            text-decoration: none;
        }
        .btn-back:hover { background: var(--pink); color: #fff; }

        /* ── VIDEO ── */
        .video-embed {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 6px 24px rgba(0,0,0,.12);
        }
        .video-link-box {
            background: var(--pink-light);
            border: 2px dashed #f9c8e0;
            border-radius: 14px;
            padding: 20px;
            text-align: center;
        }
        .video-link-box a {
            color: var(--pink);
            font-weight: 700;
            font-size: .95rem;
            text-decoration: none;
        }
        .video-link-box a:hover { text-decoration: underline; }

        /* ── DETAILS TABLE ── */
        .detail-table td { padding: 10px 14px; vertical-align: middle; }
        .detail-table td:first-child { color: #888; font-weight: 600; width: 140px; }
        .detail-table td:last-child  { color: #222; font-weight: 500; }
        .detail-table tr + tr td { border-top: 1px solid #f0f0f0; }

        /* ── RELATED ── */
        .related-card { border-radius: 14px; overflow: hidden; transition: transform .25s, box-shadow .25s; border: none; }
        .related-card:hover { transform: translateY(-5px); box-shadow: 0 12px 32px rgba(0,0,0,.12); }
        .related-card img { height: 180px; object-fit: cover; }
        .related-price { color: var(--pink); font-weight: 700; }

        /* ── SECTION HEADING ── */
        .section-heading {
            font-size: 1.4rem;
            font-weight: 800;
            color: #1a1a2e;
            border-bottom: 3px solid var(--pink);
            display: inline-block;
            padding-bottom: 4px;
            margin-bottom: 24px;
        }

        .trust-strip {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 18px;
        }
        .trust-item {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: .82rem;
            color: #666;
        }
        .trust-item i { color: var(--pink); font-size: 1rem; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="../index.html">🧶 <span>CrochetingHubb</span></a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#userNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="userNavbar">
            <ul class="navbar-nav ms-lg-auto align-items-lg-center gap-lg-1">
                <li class="nav-item"><a class="nav-link" href="user.php"><i class="bi bi-house-door me-1"></i>Home</a></li>
                <li class="nav-item"><a class="nav-link" href="usercart.php"><i class="bi bi-cart3 me-1"></i>Cart</a></li>
                <li class="nav-item ms-lg-2">
                    <a class="nav-link d-flex align-items-center gap-2" href="useraccount.php">
                        <i class="bi bi-person-circle fs-5"></i>Account
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- BREADCRUMB -->
<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="user.php" class="text-decoration-none" style="color:var(--pink)">Home</a></li>
            <li class="breadcrumb-item"><a href="user.php?category=<?php echo urlencode($product['category']); ?>" class="text-decoration-none" style="color:var(--pink)"><?php echo htmlspecialchars($product['category']); ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
        </ol>
    </nav>
</div>

<!-- MAIN PRODUCT SECTION -->
<main class="container py-3 pb-5">
    <div class="row g-5 align-items-start">

        <!-- LEFT – IMAGE GALLERY -->
        <div class="col-lg-5">
            <?php if (!empty($all_images)): ?>
                <!-- Carousel -->
                <div id="productCarousel" class="carousel slide product-image-wrapper p-0 overflow-hidden" data-bs-ride="false"
                     style="border-radius:20px;box-shadow:0 8px 32px rgba(232,36,126,.10);">
                    <div class="carousel-inner">
                        <?php foreach ($all_images as $idx => $img): ?>
                        <div class="carousel-item <?php echo $idx === 0 ? 'active' : ''; ?>">
                            <img src="../seller/uploads/<?php echo htmlspecialchars($img); ?>"
                                 class="d-block w-100"
                                 style="max-height:440px;object-fit:contain;background:#fafafa;border-radius:20px;"
                                 alt="<?php echo htmlspecialchars($product['name']); ?> photo <?php echo $idx+1; ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($all_images) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev"
                            style="background:rgba(232,36,126,.15);width:36px;height:36px;border-radius:50%;top:50%;transform:translateY(-50%);left:10px;">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next"
                            style="background:rgba(232,36,126,.15);width:36px;height:36px;border-radius:50%;top:50%;transform:translateY(-50%);right:10px;">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                    <?php endif; ?>
                </div>

                <!-- Thumbnails -->
                <?php if (count($all_images) > 1): ?>
                <div class="d-flex flex-wrap gap-2 mt-3 justify-content-center">
                    <?php foreach ($all_images as $idx => $img): ?>
                    <img src="../seller/uploads/<?php echo htmlspecialchars($img); ?>"
                         onclick="goToSlide(<?php echo $idx; ?>)"
                         style="width:60px;height:60px;object-fit:cover;border-radius:8px;cursor:pointer;border:2px solid <?php echo $idx===0?'#e8247e':'#f9c8e0'; ?>;transition:border-color .2s;"
                         id="thumb-<?php echo $idx; ?>"
                         alt="thumb <?php echo $idx+1; ?>">
                    <?php endforeach; ?>
                </div>
                <script>
                function goToSlide(idx) {
                    const carousel = bootstrap.Carousel.getOrCreateInstance(document.getElementById('productCarousel'));
                    carousel.to(idx);
                    document.querySelectorAll('[id^="thumb-"]').forEach((t,i) => {
                        t.style.borderColor = i === idx ? '#e8247e' : '#f9c8e0';
                    });
                }
                document.getElementById('productCarousel').addEventListener('slid.bs.carousel', e => {
                    goToSlide(e.to);
                });
                </script>
                <?php endif; ?>

            <?php else: ?>
                <div class="product-image-wrapper">
                    <div class="no-image-box">🧶</div>
                </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT – DETAILS -->
        <div class="col-lg-7">
            <div class="product-info">

                <span class="category-badge">
                    <i class="bi bi-tag-fill me-1"></i><?php echo htmlspecialchars($product['category']); ?>
                </span>

                <h1 class="product-title mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>

                <!-- Price -->
                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="price-tag">₹<?php echo number_format($product['price'], 2); ?></span>
                </div>

                <!-- Stock & Status -->
                <div class="d-flex align-items-center gap-3 mb-4">
                    <?php if ($product['stock'] > 0): ?>
                        <span class="stock-pill in">
                            <i class="bi bi-check-circle-fill"></i>
                            In Stock &nbsp;<strong><?php echo $product['stock']; ?></strong> units
                        </span>
                    <?php else: ?>
                        <span class="stock-pill out">
                            <i class="bi bi-x-circle-fill"></i>Out of Stock
                        </span>
                    <?php endif; ?>

                    <?php if (!empty($product['status'])): ?>
                        <span class="badge bg-light text-dark border px-3 py-2" style="border-radius:30px;font-size:.8rem;">
                            <?php echo htmlspecialchars($product['status']); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <?php if (!empty($product['description'])): ?>
                <div class="mb-4">
                    <div class="description-box">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Seller Info -->
                <?php if (!empty($product['seller_name'])): ?>
                <div class="seller-box mb-4">
                    <div class="seller-avatar"><?php echo strtoupper(substr($product['seller_name'], 0, 1)); ?></div>
                    <div>
                        <div class="seller-label"><i class="bi bi-shop me-1"></i>Sold by</div>
                        <div class="seller-name"><?php echo htmlspecialchars($product['seller_name']); ?></div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Product Details Table -->
                <div class="mb-4">
                    <table class="detail-table w-100">
                        <tr>
                            <td><i class="bi bi-grid me-1"></i>Category</td>
                            <td><?php echo htmlspecialchars($product['category']); ?></td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-box-seam me-1"></i>Stock</td>
                            <td><?php echo $product['stock']; ?> units</td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-info-circle me-1"></i>Status</td>
                            <td><?php echo htmlspecialchars($product['status'] ?? 'Available'); ?></td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-truck me-1"></i>Shipping</td>
                            <td>
                                <?php if (!empty($product['shipping_charges']) && $product['shipping_charges'] > 0): ?>
                                    <strong style="color:var(--pink)">₹<?php echo number_format($product['shipping_charges'], 2); ?></strong>
                                <?php else: ?>
                                    <span class="text-success fw-semibold">Free</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if (!empty($product['seller_name'])): ?>
                        <tr>
                            <td><i class="bi bi-shop me-1"></i>Seller</td>
                            <td><?php echo htmlspecialchars($product['seller_name']); ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>

                <!-- Buttons -->
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <form method="post" action="">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <button type="submit" name="add_to_cart"
                                class="btn-add-cart"
                                <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                            <i class="bi bi-cart-plus me-2"></i>
                            <?php echo $product['stock'] > 0 ? 'Add to Cart' : 'Out of Stock'; ?>
                        </button>
                    </form>
                    <a href="user.php" class="btn-back"><i class="bi bi-arrow-left me-1"></i>Back to Shop</a>
                </div>

                <!-- Trust Strip -->
                <div class="trust-strip">
                    <div class="trust-item"><i class="bi bi-shield-check"></i>Secure Checkout</div>
                    <div class="trust-item"><i class="bi bi-truck"></i>Free delivery above ₹499</div>
                    <div class="trust-item"><i class="bi bi-arrow-counterclockwise"></i>Easy Returns</div>
                </div>

            </div>
        </div>
    </div>

    <!-- VIDEO TUTORIAL SECTION -->
    <?php if (!empty($product['video_link'])): ?>
    <div class="mt-5 pt-3 border-top">
        <h3 class="section-heading"><i class="bi bi-play-circle me-2"></i>Tutorial Video</h3>
        <?php
        // Try to embed YouTube videos; fallback to a link for other URLs
        $video_url  = $product['video_link'];
        $embed_url  = '';
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([A-Za-z0-9_\-]{11})/', $video_url, $m)) {
            $embed_url = 'https://www.youtube.com/embed/' . $m[1];
        } elseif (strpos($video_url, 'youtube.com/embed/') !== false) {
            $embed_url = $video_url;
        }
        ?>
        <?php if ($embed_url): ?>
            <div class="video-embed">
                <div class="ratio ratio-16x9">
                    <iframe src="<?php echo htmlspecialchars($embed_url); ?>"
                            title="Tutorial Video"
                            allowfullscreen></iframe>
                </div>
            </div>
        <?php else: ?>
            <div class="video-link-box">
                <i class="bi bi-camera-video-fill fs-2 text-pink d-block mb-2" style="color:var(--pink)"></i>
                <p class="mb-2 text-muted">Watch the tutorial for this product:</p>
                <a href="<?php echo htmlspecialchars($video_url); ?>" target="_blank" rel="noopener">
                    <i class="bi bi-box-arrow-up-right me-1"></i><?php echo htmlspecialchars($video_url); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- RELATED PRODUCTS -->
    <?php if ($rel_res && mysqli_num_rows($rel_res) > 0): ?>
    <div class="mt-5 pt-3 border-top">
        <h3 class="section-heading"><i class="bi bi-grid me-2"></i>More in <?php echo htmlspecialchars($product['category']); ?></h3>
        <div class="row row-cols-2 row-cols-md-4 g-3">
            <?php while ($rel = mysqli_fetch_assoc($rel_res)): ?>
            <div class="col">
                <div class="card related-card shadow-sm h-100">
                    <a href="productdetails.php?id=<?php echo $rel['id']; ?>" class="text-decoration-none">
                        <?php if (!empty($rel['image'])): ?>
                            <img src="../seller/uploads/<?php echo htmlspecialchars($rel['image']); ?>"
                                 class="card-img-top"
                                 alt="<?php echo htmlspecialchars($rel['name']); ?>">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center bg-light" style="height:180px;font-size:2.5rem;">🧶</div>
                        <?php endif; ?>
                        <div class="card-body p-3">
                            <p class="mb-1 fw-semibold text-dark small"><?php echo htmlspecialchars($rel['name']); ?></p>
                            <span class="related-price">₹<?php echo number_format($rel['price'], 2); ?></span>
                        </div>
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>

</main>

<!-- FOOTER -->
<footer class="premium-footer mt-5">
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
                <p class="mb-0 opacity-75">&copy; <?php echo date("Y"); ?> CrochetingHubb. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
