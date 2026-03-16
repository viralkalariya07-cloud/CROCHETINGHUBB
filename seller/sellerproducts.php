<?php
session_name('SELLER_SESSION');
session_start();

/* DATABASE CONNECTION */
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);
if (!$conn) {
    die("Database connection failed");
}

// Ensure seller is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../user/login.php");
    exit();
}

$seller_id = $_SESSION['user_id'];

/* FETCH SELLER PRODUCTS */
$sql = "SELECT * FROM sellerproducts WHERE seller_id = '$seller_id' ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("SQL Error: " . mysqli_error($conn));
}

?>

<?php include("../includes/seller_header.php"); ?>
<style>
    .products-main { padding: 60px 0; background: #fffafc; }
    .products-header h2 { color: var(--primary-pink); font-weight: 700; font-size: 2.5rem; margin-bottom: 10px; }
    .btn-add-product { background: var(--primary-pink); color: white; border-radius: 30px; padding: 12px 30px; font-weight: 600; transition: 0.3s; border: none; }
    .btn-add-product:hover { background: var(--deep-pink); transform: translateY(-3px); box-shadow: 0 8px 20px rgba(255, 71, 133, 0.3); color: white; }
    .product-box { background: white; border-radius: 20px; padding: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: 0.3s; margin-bottom: 30px; display: flex; align-items: center; border: 1px solid #f0f0f0; }
    .product-box:hover { transform: translateY(-5px); border-color: var(--primary-pink); }
    .product-box-image img { border-radius: 15px; width: 150px; height: 150px; object-fit: cover; }
    .product-box-details { flex-grow: 1; padding: 0 30px; }
    .product-box-details h4 { font-weight: 700; color: #333; }
    .product-price { color: var(--primary-pink); font-weight: 700; font-size: 1.4rem; }
    .btn-edit-box, .btn-delete-box { padding: 8px 20px; border-radius: 20px; text-decoration: none; font-weight: 600; transition: 0.3s; margin-left: 10px; }
    .btn-edit-box { background: #fdf2f5; color: var(--primary-pink); }
    .btn-edit-box:hover { background: var(--primary-pink); color: white; }
    .btn-delete-box { background: #fff0f0; color: #ff4747; }
    .btn-delete-box:hover { background: #ff4747; color: white; }
</style>



<section class="products-main">
    <div class="products-container container">

        <!-- Page Heading -->
        <div class="products-header text-center mb-5">
            <h2>My Products</h2>
            <p>Manage your handmade crochet products</p>
        </div>

        <!-- Add Product Button -->
        <div class="text-end mb-4">
            <a href="selleraddproducts.php">
                <button class="btn btn-add-product">+ Add New Product</button>
            </a>
        </div>

        <!-- Product List -->
        <div class="product-list-section">
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($product = mysqli_fetch_assoc($result)) {
            ?>
                <div class="product-box">
                    <div class="product-box-image">
                        <?php if (!empty($product['image'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image">
                        <?php else: ?>
                            <div class="no-image">No Image</div>
                        <?php endif; ?>
                    </div>
                    <div class="product-box-details">
                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                        <p class="product-category">Category: <?php echo htmlspecialchars($product['category']); ?></p>
                        <p class="product-price">₹<?php echo number_format($product['price'], 2); ?></p>
                        <p class="product-stock">Stock: <?php echo $product['stock']; ?> | Status: <?php echo htmlspecialchars($product['status']); ?></p>
                    </div>
                    <div class="product-box-actions">
                        <a href="selleraddproducts.php?edit_id=<?php echo $product['id']; ?>" class="btn-edit-box">Edit</a>
                        <a href="selleraddproducts.php?delete_id=<?php echo $product['id']; ?>" 
                           class="btn-delete-box" 
                           onclick="return confirm('Delete this product?')">Delete</a>
                    </div>
                </div>
            <?php
                }
            } else {
            ?>
                <div class="no-products-box">
                    <p>No products found. Click "Add New Product" to get started!</p>
                </div>
            <?php
            }
            ?>
        </div>
      
    </div>
</section>


<!-- ================= FOOTER ================= -->
<?php include("../includes/seller_footer.php"); ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>