<?php
session_name('SELLER_SESSION');
session_start();

/* ================= DATABASE CONNECTION ================= */
include("../includes/db.php");
/* ENSURE upi_id COLUMN EXISTS */
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM orderss LIKE 'upi_id'");
if (mysqli_num_rows($check_col) == 0) {
    mysqli_query($conn, "ALTER TABLE orderss ADD COLUMN upi_id VARCHAR(100) DEFAULT NULL AFTER payment_method");
}

/* ================= CHECK SELLER LOGIN ================= */

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Default seller ID for testing
}

$seller_id = $_SESSION['user_id'];

/* ================= UPDATE ORDER STATUS ================= */
if (isset($_POST['update_status'])) {
    $order_id   = intval($_POST['order_id']);
    $new_status = $_POST['new_status'];

    $stmt = mysqli_prepare($conn, "UPDATE orderss SET status=? WHERE id=? AND seller_id=?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sii", $new_status, $order_id, $seller_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

/* ================= FETCH SELLER ORDERS ================= */
$query = "
SELECT 
    o.id,
    COALESCE(u.full_name, ua.full_name, 'Unknown Customer') AS customer_name,
    COALESCE(u.email, ua.email, 'No Email') AS customer_email,
    p.name AS product_name,
    p.image AS product_image,
    o.quantity,
    o.total_price,
    o.payment_method,
    o.upi_id,
    o.status,
    o.order_date,
    ua.mobile AS customer_mobile,
    (SELECT CONCAT(street, ', ', city, ' - ', pincode) FROM user_address WHERE user_id = o.customer_id ORDER BY id DESC LIMIT 1) as shipping_address
FROM orderss o
LEFT JOIN users u ON o.customer_id = u.id
LEFT JOIN useraccount ua ON o.customer_id = ua.id
JOIN sellerproducts p ON o.product_id = p.id
WHERE o.seller_id = ?
ORDER BY o.order_date DESC
";

$stmt = mysqli_prepare($conn, $query);
if (!$stmt) {
    // Table or column may not exist yet — return empty result gracefully
    $result    = false;
    $has_orders = false;
} else {
    mysqli_stmt_bind_param($stmt, "i", $seller_id);
    mysqli_stmt_execute($stmt);
    $result     = mysqli_stmt_get_result($stmt);
    $has_orders = ($result && mysqli_num_rows($result) > 0);
}
?>

<?php include("../includes/seller_header.php"); ?>
<style>
    main.container { background: white; padding: 40px; border-radius: 30px; box-shadow: 0 15px 50px rgba(0,0,0,0.05); margin-top: 50px !important; }
    .section-title { color: var(--primary-pink); font-weight: 800; font-size: 2.4rem; position: relative; display: inline-block; margin-bottom: 40px; }
    .section-title::after { content: ''; display: block; width: 50%; height: 5px; background: var(--primary-pink); margin: 10px auto 0; border-radius: 10px; opacity: 0.3; }
    .order-table thead { background: var(--light-pink); }
    .order-table th { border: none; color: var(--deep-pink); font-weight: 700; padding: 15px; }
    .order-table td { padding: 20px 15px; border-bottom: 1px solid #f8f8f8; }
    .text-pink { color: var(--primary-pink) !important; }
    .badge { border-radius: 8px; padding: 6px 12px; font-weight: 600; }
    .btn-success { background: #2ecc71; border: none; font-weight: 600; }
    .btn-danger { background: #e74c3c; border: none; font-weight: 600; }
    .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
</style>


<!-- Main Section -->
<main class="container my-5">

    <h2 class="section-title text-center mb-4">My Orders</h2>

    <div class="table-responsive">
        <table class="table order-table align-middle">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Shipping Address</th>
                    <th>Qty</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
               <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td class="fw-bold">#<?= $row['id']; ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="uploads/<?= htmlspecialchars($row['product_image']); ?>" class="rounded" style="width: 45px; height: 45px; object-fit: cover; border: 1px solid #eee;">
                                <div class="small fw-semibold text-truncate" style="max-width: 120px;"><?= htmlspecialchars($row['product_name']); ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="small fw-semibold"><?= htmlspecialchars($row['customer_name']); ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($row['customer_email']); ?></div>
                            <div class="text-pink small fw-medium"><?= htmlspecialchars($row['customer_mobile'] ?? 'No Mobile'); ?></div>
                        </td>
                        <td style="max-width: 200px;">
                            <div class="small text-muted line-clamp-2"><?= htmlspecialchars($row['shipping_address'] ?? 'Address not found'); ?></div>
                        </td>
                        <td class="text-center"><?= $row['quantity']; ?></td>
                        <td class="text-pink fw-bold">₹<?= number_format($row['total_price'], 2); ?></td>
                        <td>
                            <span class="badge bg-light text-dark border small"><?= htmlspecialchars($row['payment_method']); ?></span>
                            <?php if (strtolower($row['payment_method']) === 'upi' && !empty($row['upi_id'])): ?>
                                <div class="text-muted small mt-1" style="font-size: 0.7rem;">ID: <?= htmlspecialchars($row['upi_id']); ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted"><?= date("d M", strtotime($row['order_date'])); ?></td>
                        <td>
                            <?php
                            $status_class = 'bg-primary';
                            if($row['status'] == 'Pending') $status_class = 'bg-warning text-dark';
                            if($row['status'] == 'Delivered') $status_class = 'bg-success';
                            if($row['status'] == 'Cancelled') $status_class = 'bg-danger';
                            ?>
                            <span class="badge <?= $status_class; ?>" style="font-size: 0.7rem;">
                                <?= htmlspecialchars($row['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['status'] === 'Pending'): ?>
                                <div class="d-flex gap-2">
                                    <form method="POST">
                                        <input type="hidden" name="order_id" value="<?= $row['id']; ?>">
                                        <input type="hidden" name="new_status" value="Accepted">
                                        <button type="submit" name="update_status" class="btn btn-sm btn-success px-3 rounded-pill shadow-sm" style="font-size: 0.75rem;">
                                            <i class="fa fa-check me-1"></i> Accept
                                        </button>
                                    </form>
                                    <form method="POST">
                                        <input type="hidden" name="order_id" value="<?= $row['id']; ?>">
                                        <input type="hidden" name="new_status" value="Cancelled">
                                        <button type="submit" name="update_status" class="btn btn-sm btn-danger px-3 rounded-pill shadow-sm" style="font-size: 0.75rem;">
                                            <i class="fa fa-times me-1"></i> Decline
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="text-muted small italic">Status updated</div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-muted">
                        No orders available yet.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (!$has_orders): ?>
    <div class="text-center mt-4 empty-message">
        <i class="fa fa-box-open fa-3x mb-3 text-muted"></i>
        <p class="text-muted">No orders available yet.</p>
    </div>
    <?php endif; ?>

</main>

<!-- Footer -->
<?php include("../includes/seller_footer.php"); ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>