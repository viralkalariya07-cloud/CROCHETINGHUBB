<?php
// ================= DATABASE CONNECTION =================
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

/* ENSURE upi_id COLUMN EXISTS */
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM orderss LIKE 'upi_id'");
if (mysqli_num_rows($check_col) == 0) {
    mysqli_query($conn, "ALTER TABLE orderss ADD COLUMN upi_id VARCHAR(100) DEFAULT NULL AFTER payment_method");
}

// ================= UPDATE ORDER STATUS =================
if (isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['new_status'])) {
    $order_id  = intval($_POST['order_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['new_status']);
    $allowed_statuses = ['Pending', 'Accepted', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
    if (in_array($new_status, $allowed_statuses)) {
        mysqli_query($conn, "UPDATE orderss SET status='$new_status' WHERE id=$order_id");
    }
    echo "<script>window.location.href='adminorders.php?updated=1';</script>";
    exit;
}

// ================= DELETE ORDER =================
if (isset($_GET['delete_order']) && is_numeric($_GET['delete_order'])) {
    $delete_id = intval($_GET['delete_order']);
    mysqli_query($conn, "DELETE FROM orderss WHERE id=$delete_id");
    echo "<script>window.location.href='adminorders.php?deleted=1';</script>";
    exit;
}

// ================= FILTERS =================
$search        = isset($_GET['search'])  ? mysqli_real_escape_string($conn, trim($_GET['search']))  : '';
$status_filter = isset($_GET['status'])  ? mysqli_real_escape_string($conn, $_GET['status'])  : '';

// ================= FETCH ALL ORDERS =================
$sql = "
    SELECT
        o.id,
        o.quantity,
        o.total_price,
        o.payment_method,
        o.upi_id,
        o.status,
        o.order_date,
        COALESCE(u.full_name,  ua.full_name,  'Unknown') AS customer_name,
        COALESCE(u.email,      ua.email,      'N/A')     AS customer_email,
        ua.mobile                                        AS customer_mobile,
        p.name   AS product_name,
        p.image  AS product_image,
        p.price  AS product_price,
        (SELECT CONCAT(street, ', ', city, ' - ', pincode)
           FROM user_address
          WHERE user_id = o.customer_id
          ORDER BY id DESC LIMIT 1)                      AS shipping_address
    FROM orderss o
    LEFT JOIN users        u  ON o.customer_id = u.id
    LEFT JOIN useraccount  ua ON o.customer_id = ua.id
    LEFT JOIN sellerproducts p ON o.product_id  = p.id
    WHERE 1=1
";

if (!empty($search)) {
    $sql .= " AND (COALESCE(u.full_name, ua.full_name, '') LIKE '%$search%'
               OR COALESCE(u.email, ua.email, '')          LIKE '%$search%'
               OR p.name                                   LIKE '%$search%'
               OR o.id                                     LIKE '%$search%')";
}
if (!empty($status_filter)) {
    $sql .= " AND o.status = '$status_filter'";
}
$sql .= " ORDER BY o.order_date DESC";

$orders_result = mysqli_query($conn, $sql);
$orders = [];
if ($orders_result) {
    while ($row = mysqli_fetch_assoc($orders_result)) {
        $orders[] = $row;
    }
}

// ================= STATS =================
$stats_res    = mysqli_query($conn, "SELECT COUNT(*) as total, SUM(total_price) as revenue FROM orderss");
$stats_row    = mysqli_fetch_assoc($stats_res);
$total_orders   = $stats_row['total']   ?? 0;
$total_revenue  = $stats_row['revenue'] ?? 0;

$pending_res  = mysqli_query($conn, "SELECT COUNT(*) as c FROM orderss WHERE status='Pending'");
$pending_row  = mysqli_fetch_assoc($pending_res);
$pending_count = $pending_row['c'] ?? 0;

$delivered_res = mysqli_query($conn, "SELECT COUNT(*) as c FROM orderss WHERE status='Delivered'");
$delivered_row = mysqli_fetch_assoc($delivered_res);
$delivered_count = $delivered_row['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>All Orders – CrochetingHubb Admin</title>
    <meta name="description" content="Admin panel – view and manage all customer orders on CrochetingHubb." />

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet" />
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css" />

    <style>
        /* ---- Extra order-specific tweaks ---- */
        .order-stat-card {
            background: #fff;
            border-radius: 14px;
            padding: 1.2rem 1.5rem;
            box-shadow: 0 4px 18px rgba(0,0,0,.07);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform .2s;
        }
        .order-stat-card:hover { transform: translateY(-3px); }
        .order-stat-icon {
            width: 52px; height: 52px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; color: #fff; flex-shrink: 0;
        }
        .icon-total    { background: linear-gradient(135deg,#f857a6,#ff5858); }
        .icon-pending  { background: linear-gradient(135deg,#f7971e,#ffd200); }
        .icon-delivered{ background: linear-gradient(135deg,#11998e,#38ef7d); }
        .icon-revenue  { background: linear-gradient(135deg,#6a3093,#a044ff); }
        .order-stat-card h4 { margin:0; font-size:1.4rem; font-weight:700; }
        .order-stat-card p  { margin:0; font-size:.8rem; color:#888; }

        .status-badge { border-radius: 50px; padding: .28em .75em; font-size:.75rem; font-weight:600; }
        .status-pending   { background:#fff3cd; color:#856404; }
        .status-accepted  { background:#cfe2ff; color:#084298; }
        .status-processing{ background:#d1ecf1; color:#0c5460; }
        .status-shipped   { background:#d4edff; color:#003d73; }
        .status-delivered { background:#d4edda; color:#155724; }
        .status-cancelled { background:#f8d7da; color:#721c24; }

        .order-img { width:48px; height:48px; object-fit:cover; border-radius:8px; border:1px solid #eee; }
        .customer-avatar {
            width:36px; height:36px; border-radius:50%;
            background: linear-gradient(135deg,#f857a6,#ff5858);
            color:#fff; font-weight:700; font-size:.9rem;
            display:flex; align-items:center; justify-content:center; flex-shrink:0;
        }
        .toolbar-card {
            background:#fff; border-radius:14px;
            padding:1.2rem 1.5rem;
            box-shadow:0 2px 12px rgba(0,0,0,.06);
            margin-bottom:1.5rem;
        }
        .admin-table tbody tr { transition: background .15s; }
        .admin-table tbody tr:hover { background:#fdf5f9 !important; }
        select.status-select {
            border-radius:50px; font-size:.78rem;
            padding:.25em .8em; border:1px solid #ddd;
            background: #fff; cursor:pointer;
        }
    </style>
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
                <li class="nav-item"><a class="nav-link" href="adminproducts.php"><i class="fas fa-box me-1"></i> Products</a></li>
                <li class="nav-item"><a class="nav-link nav-link--active" href="adminorders.php"><i class="fas fa-shopping-bag me-1"></i> Orders</a></li>
                <li class="nav-item"><a class="nav-link" href="adminsettings.php"><i class="fas fa-cog me-1"></i> Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- ================= MAIN ================= -->
<main>

<!-- Hero -->
<section class="admin-hero">
    <div class="container">
        <h1 class="section-title"><i class="fas fa-shopping-bag me-2"></i>All Orders</h1>
        <p class="admin-subtitle">View, filter, and manage every order placed on CrochetingHubb</p>
    </div>
</section>

<section class="dashboard-content">
    <div class="container">

        <!-- Alerts -->
        <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>Order status updated successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-trash-alt me-2"></i>Order deleted successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- ---- Stats Row ---- -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="order-stat-card">
                    <div class="order-stat-icon icon-total"><i class="fas fa-shopping-bag"></i></div>
                    <div>
                        <h4><?php echo $total_orders; ?></h4>
                        <p>Total Orders</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="order-stat-card">
                    <div class="order-stat-icon icon-pending"><i class="fas fa-clock"></i></div>
                    <div>
                        <h4><?php echo $pending_count; ?></h4>
                        <p>Pending Orders</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="order-stat-card">
                    <div class="order-stat-icon icon-delivered"><i class="fas fa-check-double"></i></div>
                    <div>
                        <h4><?php echo $delivered_count; ?></h4>
                        <p>Delivered Orders</p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="order-stat-card">
                    <div class="order-stat-icon icon-revenue"><i class="fas fa-rupee-sign"></i></div>
                    <div>
                        <h4>₹<?php echo number_format($total_revenue, 0); ?></h4>
                        <p>Total Revenue</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ---- Search & Filter Toolbar ---- -->
        <div class="toolbar-card">
            <form method="GET" action="adminorders.php" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold"><i class="fas fa-search me-1"></i>Search Orders</label>
                    <input type="text" name="search" id="searchInput" class="form-control"
                           placeholder="Order ID, customer name, email or product..."
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold"><i class="fas fa-filter me-1"></i>Filter by Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <?php
                        $statuses = ['Pending','Accepted','Processing','Shipped','Delivered','Cancelled'];
                        foreach ($statuses as $s):
                        ?>
                        <option value="<?php echo $s; ?>" <?php echo ($status_filter === $s) ? 'selected' : ''; ?>>
                            <?php echo $s; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-pink w-100"><i class="fas fa-search me-1"></i>Search</button>
                </div>
                <div class="col-md-2">
                    <a href="adminorders.php" class="btn btn-outline-secondary w-100"><i class="fas fa-redo me-1"></i>Reset</a>
                </div>
            </form>
        </div>

        <!-- ---- Count + Back ---- -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="badge users-count-badge">
                <i class="fas fa-shopping-bag me-1"></i><?php echo count($orders); ?> order<?php echo count($orders) !== 1 ? 's' : ''; ?> found
            </span>
            <a href="admin.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
            </a>
        </div>

        <!-- ---- Orders Table ---- -->
        <div class="admin-panel-section">
            <div class="table-responsive">
                <table class="admin-table align-middle" id="ordersTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order ID</th>
                            <th>Product</th>
                            <th>Customer</th>
                            <th>Shipping Address</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($orders) > 0): ?>
                            <?php $serial = 1; foreach ($orders as $order): ?>

                            <?php
                            // Status badge class
                            $sc = 'status-pending';
                            if ($order['status'] === 'Accepted')   $sc = 'status-accepted';
                            if ($order['status'] === 'Processing') $sc = 'status-processing';
                            if ($order['status'] === 'Shipped')    $sc = 'status-shipped';
                            if ($order['status'] === 'Delivered')  $sc = 'status-delivered';
                            if ($order['status'] === 'Cancelled')  $sc = 'status-cancelled';
                            ?>

                            <tr>
                                <!-- Serial -->
                                <td class="fw-bold text-muted"><?php echo $serial++; ?></td>

                                <!-- Order ID -->
                                <td class="fw-bold">#<?php echo $order['id']; ?></td>

                                <!-- Product -->
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!empty($order['product_image'])): ?>
                                            <img src="../seller/uploads/<?php echo htmlspecialchars($order['product_image']); ?>"
                                                 class="order-img" alt="Product">
                                        <?php else: ?>
                                            <div class="order-img d-flex align-items-center justify-content-center bg-light text-muted" style="font-size:.65rem;">No Img</div>
                                        <?php endif; ?>
                                        <div class="small fw-semibold text-truncate" style="max-width:110px;">
                                            <?php echo htmlspecialchars($order['product_name'] ?? 'N/A'); ?>
                                        </div>
                                    </div>
                                </td>

                                <!-- Customer -->
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="customer-avatar">
                                            <?php echo strtoupper(substr($order['customer_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="small fw-semibold"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                            <div class="text-muted" style="font-size:.75rem;"><?php echo htmlspecialchars($order['customer_email']); ?></div>
                                            <?php if (!empty($order['customer_mobile'])): ?>
                                                <div style="font-size:.72rem; color:#f857a6;"><?php echo htmlspecialchars($order['customer_mobile']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <!-- Shipping Address -->
                                <td style="max-width:160px;">
                                    <div class="small text-muted">
                                        <?php echo htmlspecialchars($order['shipping_address'] ?? 'Not provided'); ?>
                                    </div>
                                </td>

                                <!-- Qty -->
                                <td class="text-center fw-semibold"><?php echo $order['quantity']; ?></td>

                                <!-- Total -->
                                <td class="fw-bold" style="color:#f857a6;">₹<?php echo number_format($order['total_price'], 2); ?></td>

                                <!-- Payment -->
                                <td>
                                    <span class="badge bg-light text-dark border small">
                                        <?php echo htmlspecialchars($order['payment_method'] ?? 'N/A'); ?>
                                    </span>
                                    <?php if (strtolower($order['payment_method'] ?? '') === 'upi' && !empty($order['upi_id'])): ?>
                                        <div class="text-muted small mt-1" style="font-size: .7rem;">ID: <?php echo htmlspecialchars($order['upi_id']); ?></div>
                                    <?php endif; ?>
                                </td>

                                <!-- Date -->
                                <td class="small text-muted">
                                    <?php echo date('d M Y', strtotime($order['order_date'])); ?>
                                    <div style="font-size:.7rem;"><?php echo date('h:i A', strtotime($order['order_date'])); ?></div>
                                </td>

                                <!-- Status Badge -->
                                <td>
                                    <span class="status-badge <?php echo $sc; ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </td>

                                <!-- Actions: Update status + Delete -->
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                                        <!-- Status Update Dropdown -->
                                        <form method="POST" class="d-inline-flex">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="new_status" class="status-select me-1" onchange="this.form.submit()" title="Change Status">
                                                <?php foreach ($statuses as $s): ?>
                                                <option value="<?php echo $s; ?>" <?php echo ($order['status'] === $s) ? 'selected' : ''; ?>>
                                                    <?php echo $s; ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                        <!-- Delete -->
                                        <a href="adminorders.php?delete_order=<?php echo $order['id']; ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Delete Order #<?php echo $order['id']; ?>? This cannot be undone.');"
                                           title="Delete Order">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-shopping-bag fa-3x mb-3 d-block" style="color:var(--soft-pink);"></i>
                                        <p class="fs-5 mb-1">No orders found</p>
                                        <p class="small">Try adjusting your search or filter criteria.</p>
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
