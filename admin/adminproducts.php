<?php
// ================= DATABASE CONNECTION =================
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle product deletion
if (isset($_GET['delete_product']) && is_numeric($_GET['delete_product'])) {
    $delete_id = intval($_GET['delete_product']);
    // Optional: Fetch image path to delete file from server
    $img_query = mysqli_query($conn, "SELECT image FROM sellerproducts WHERE id = $delete_id");
    if ($img_row = mysqli_fetch_assoc($img_query)) {
        $img_path = "../seller/uploads/" . $img_row['image'];
        if (file_exists($img_path)) {
            unlink($img_path);
        }
    }
    
    mysqli_query($conn, "DELETE FROM sellerproducts WHERE id = $delete_id");
    echo "<script>window.location.href='adminproducts.php?deleted=1';</script>";
    exit;
}

// Fetch all products
$products = [];
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category_filter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

$sql = "SELECT * FROM sellerproducts WHERE 1=1";
if (!empty($search)) {
    $sql .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
}
if (!empty($category_filter)) {
    $sql .= " AND category = '$category_filter'";
}
$sql .= " ORDER BY id DESC";
$products_result = mysqli_query($conn, $sql);
if ($products_result) {
    while ($p = mysqli_fetch_assoc($products_result)) {
        $products[] = $p;
    }
}

// Fetch categories for filter
$categories_result = mysqli_query($conn, "SELECT * FROM categories");
$categories = [];
if ($categories_result) {
    while ($cat = mysqli_fetch_assoc($categories_result)) {
        $categories[] = $cat['name'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>All Products – Crocheting Hub</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet" />
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body>

<!-- ================= NAVBAR ================= -->
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="admin.php">
            <i class="fas fa-shield-alt me-2"></i>Admin Panel 🧶 CrochetingHubb
        </a>

        <button class="navbar-toggler border-0" type="button"
                data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon" style="filter:brightness(0) invert(1);"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto gap-1">
                <li class="nav-item"><a class="nav-link" href="admin.php"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="adminusers.php"><i class="fas fa-users me-1"></i> Users</a></li>
                <li class="nav-item"><a class="nav-link nav-link--active" href="adminproducts.php"><i class="fas fa-box me-1"></i> Products</a></li>
                <li class="nav-item"><a class="nav-link" href="adminorders.php"><i class="fas fa-shopping-bag me-1"></i> Orders</a></li>
                <li class="nav-item"><a class="nav-link" href="adminsettings.php"><i class="fas fa-cog me-1"></i> Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- ================= MAIN ================= -->
<main>

<!-- ================= PRODUCTS LIST VIEW ================= -->
<section class="admin-hero">
    <div class="container">
        <h1 class="section-title"><i class="fas fa-box me-2"></i>All Products</h1>
        <p class="admin-subtitle">View and manage all products added by sellers</p>
    </div>
</section>

<section class="dashboard-content">
    <div class="container">

        <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>Product deleted successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Search & Filter Bar -->
        <div class="users-toolbar mb-4">
            <form method="GET" action="adminproducts.php" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold"><i class="fas fa-search me-1"></i>Search Products</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by name or description..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold"><i class="fas fa-filter me-1"></i>Filter by Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo (isset($_GET['category']) && $_GET['category'] === $cat) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-pink w-100"><i class="fas fa-search me-1"></i>Search</button>
                </div>
                <div class="col-md-2">
                    <a href="adminproducts.php" class="btn btn-outline-secondary w-100"><i class="fas fa-redo me-1"></i>Reset</a>
                </div>
            </form>
        </div>

        <!-- Products Count Badge -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <span class="badge users-count-badge">
                    <i class="fas fa-box me-1"></i><?php echo count($products); ?> product<?php echo count($products) !== 1 ? 's' : ''; ?> found
                </span>
            </div>
            <a href="admin.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Dashboard</a>
        </div>

        <!-- Products Table -->
        <div class="admin-panel-section">
            <div class="table-responsive">
                <table class="admin-table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Seller ID</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($products) > 0): ?>
                            <?php $serial = 1; foreach ($products as $product): ?>
                            <tr>
                                <td class="fw-bold"><?php echo $serial++; ?></td>
                                <td>
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="../seller/uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="Product" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                    <?php else: ?>
                                        <span class="text-muted small">No Img</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-semibold text-dark"><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($product['category']); ?></span></td>
                                <td class="fw-bold text-success">₹<?php echo htmlspecialchars($product['price']); ?></td>
                                <td><?php echo htmlspecialchars($product['stock']); ?></td>
                                <td>
                                    <?php
                                    $s = strtolower(trim($product['status'] ?? ''));
                                    $stock_val = intval($product['stock'] ?? 0);
                                    $is_available = in_array($s, ['available', 'in stock', 'active', '1', 'yes'])
                                                    || ($s === '' && $stock_val > 0);
                                    ?>
                                    <?php if ($is_available): ?>
                                        <span class="badge bg-success">Available</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Out of Stock</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small">#<?php echo htmlspecialchars($product['seller_id']); ?></td>
                                <td class="text-center">
                                    <a href="adminproducts.php?delete_product=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this product?');" title="Delete Product">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-box-open fa-3x mb-3 d-block" style="color: var(--soft-pink);"></i>
                                        <p class="fs-5 mb-1">No products found</p>
                                        <p class="small">Try adjusting your search filter.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>

</main>

<footer class="text-center py-3">
    &copy; 2025 Crocheting Hub
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
