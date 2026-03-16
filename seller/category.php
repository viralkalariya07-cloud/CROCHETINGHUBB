<?php
session_name('SELLER_SESSION');
session_start();

// ======================
// DATABASE CONNECTION
// ======================
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Ensure seller_id column exists
mysqli_query($conn, "ALTER TABLE categories ADD COLUMN IF NOT EXISTS seller_id INT DEFAULT 0");

// FIX: Drop the old unique key on 'name' alone (prevents different sellers adding same category name)
// Replace with a composite unique key on (name, seller_id) so each seller can have their own unique categories
$old_key = mysqli_query($conn, "SHOW INDEX FROM categories WHERE Key_name = 'uq_cat_name'");
if ($old_key && mysqli_num_rows($old_key) > 0) {
    mysqli_query($conn, "ALTER TABLE categories DROP INDEX uq_cat_name");
}
// Add composite unique key if it doesn't already exist
$comp_key = mysqli_query($conn, "SHOW INDEX FROM categories WHERE Key_name = 'uq_cat_name_seller'");
if ($comp_key && mysqli_num_rows($comp_key) == 0) {
    mysqli_query($conn, "ALTER TABLE categories ADD UNIQUE KEY uq_cat_name_seller (name, seller_id)");
}

// Ensure seller is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../user/login.php");
    exit();
}
$seller_id = $_SESSION['user_id'];

// Always use 'id' as the primary key column for categories table

// ======================
// ADD CATEGORY BACKEND
// ======================
$add_error = '';
$add_success = '';

if (isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);

    if (!empty($category_name)) {
        // Check if this seller already has a category with the same name
        $dup_check = mysqli_prepare($conn, "SELECT id FROM categories WHERE name = ? AND seller_id = ?");
        mysqli_stmt_bind_param($dup_check, "si", $category_name, $seller_id);
        mysqli_stmt_execute($dup_check);
        mysqli_stmt_store_result($dup_check);

        if (mysqli_stmt_num_rows($dup_check) > 0) {
            $add_error = "You already have a category named <strong>" . htmlspecialchars($category_name) . "</strong>. Please use a different name.";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO categories (name, seller_id) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, "si", $category_name, $seller_id);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: category.php?success=1");
                exit();
            } else {
                $add_error = "Failed to add category. Please try again.";
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($dup_check);
    } else {
        $add_error = "Category name cannot be empty.";
    }
}

// Success message after redirect
if (isset($_GET['success'])) {
    $add_success = "Category added successfully! 🎉";
}

// ======================
// DELETE CATEGORY BACKEND
// ======================
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    if ($del_id > 0) {
        // Direct delete by ID — simplest and most reliable approach
        mysqli_query($conn, "DELETE FROM categories WHERE id = $del_id");
    }
    header("Location: category.php?deleted=1");
    exit();
}

// Deleted confirmation message
$delete_success = isset($_GET['deleted']) ? "Category deleted successfully." : '';

// Fetch categories ONLY for this seller
$result = mysqli_query($conn, "SELECT * FROM categories WHERE seller_id = $seller_id ORDER BY id DESC");
if (!$result) {
    $result = mysqli_query($conn, "SELECT * FROM categories WHERE seller_id = $seller_id");
}
?>

<?php include("../includes/seller_header.php"); ?>
<style>
    main.container { background: white; padding: 40px; border-radius: 30px; box-shadow: 0 15px 50px rgba(0,0,0,0.05); margin-top: 50px !important; }
    .section-title { color: var(--primary-pink); font-weight: 800; font-size: 2.4rem; position: relative; display: inline-block; margin-bottom: 10px; }
    .section-shell { background: #fffafc; border-radius: 20px; border: 1px solid #fef0f5; }
    .btn-pink { background: var(--primary-pink); color: white; border-radius: 30px; border: none; padding: 10px 25px; transition: 0.3s; }
    .btn-pink:hover { background: var(--deep-pink); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255, 71, 133, 0.3); color: white; }
    .category-table thead { background: var(--light-pink); }
    .category-table th { border: none; color: var(--deep-pink); font-weight: 700; }
    .category-input { border-radius: 15px; border: 1px solid #fce4ec; padding: 12px 20px; }
    .category-input:focus { border-color: var(--primary-pink); box-shadow: 0 0 0 0.25rem rgba(255, 71, 133, 0.1); }
</style>



<!-- ================= MAIN CONTENT ================= -->
<main class="container py-5 flex-grow-1">

    <h2 class="section-title">Seller Categories</h2>
    <p class="text-muted mb-4">
        Manage your crochet product categories 🧶
    </p>

    <?php if (!empty($delete_success)): ?>
        <div class="alert alert-warning rounded-3 py-2 px-3 mb-4" role="alert">
            <i class="bi bi-trash-fill me-2"></i><?php echo $delete_success; ?> It has been removed from the user categories list.
        </div>
    <?php endif; ?>

    <!-- ADD CATEGORY -->
    <div class="section-shell p-4 mb-4">
        <h5 class="mb-3 text-pink">Add New Category</h5>

        <?php if (!empty($add_success)): ?>
            <div class="alert alert-success rounded-3 py-2 px-3 mb-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo $add_success; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($add_error)): ?>
            <div class="alert alert-danger rounded-3 py-2 px-3 mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $add_error; ?>
            </div>
        <?php endif; ?>

        <form class="row g-3" method="POST" action="">
    <div class="col-md-8">
        <input type="text" name="category_name"
               class="form-control category-input <?php echo !empty($add_error) ? 'is-invalid' : ''; ?>"
               placeholder="Enter category name" required>
    </div>
    <div class="col-md-4 d-grid">

        <button type="submit" name="add_category" class="btn btn-pink">
            <i class="bi bi-plus-circle"></i> Add Category
        </button>
    </div>
</form>

    </div>

    <!-- CATEGORY TABLE -->
    <div class="section-shell p-4">

        <div class="table-responsive">
            <table class="table table-borderless category-table align-middle text-center">
                <thead>
                    <tr>
                        <th>Category ID</th>
                        <th>Category Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
<?php
if ($result && mysqli_num_rows($result) > 0) {
    $counter = 1; // Initialize counter for auto-increment display
    while ($row = mysqli_fetch_assoc($result)) {
?>
<tr>
    <td><?php echo $counter; ?></td>
    <td><?php echo $row['name']; ?></td>

    <!-- 👉 THIS IS WHERE YOUR DELETE BUTTON GOES -->
    <td>
        <a href="?delete=<?php echo intval($row['id']); ?>"
           class="btn btn-outline-danger btn-sm"
           onclick="return confirm('Are you sure you want to delete this category?')">
           <i class="bi bi-trash"></i> Delete
        </a>
    </td>
</tr>
<?php
    $counter++; // Increment counter for next row
}
} else {
?>
<tr>
<td colspan="3">
<div class="empty-category">
    <i class="bi bi-folder-heart"></i>
    <p>No categories added yet</p>
</div>
</td>
</tr>
<?php } ?>
</tbody>

            </table>
        </div>

    </div>

</main>
<!-- =============== END MAIN CONTENT =============== -->


<!-- ================= FOOTER ================= -->
<?php include("../includes/seller_footer.php"); ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>