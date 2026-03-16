<?php
// ================= DATABASE CONNECTION =================
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle user deletion
if (isset($_GET['delete_user']) && is_numeric($_GET['delete_user'])) {
    $delete_id = intval($_GET['delete_user']);
    mysqli_query($conn, "DELETE FROM users WHERE id = $delete_id");
    echo "<script>window.location.href='adminusers.php?deleted=1';</script>";
    exit;
}

// Fetch all users
$users = [];
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$role_filter = isset($_GET['role']) ? mysqli_real_escape_string($conn, $_GET['role']) : '';

$sql = "SELECT * FROM users WHERE 1=1";
if (!empty($search)) {
    $sql .= " AND (full_name LIKE '%$search%' OR email LIKE '%$search%')";
}
if (!empty($role_filter)) {
    $sql .= " AND role = '$role_filter'";
}
$sql .= " ORDER BY id DESC";
$users_result = mysqli_query($conn, $sql);
if ($users_result) {
    while ($u = mysqli_fetch_assoc($users_result)) {
        $users[] = $u;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>All Users – Crocheting Hub</title>

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
                <li class="nav-item"><a class="nav-link nav-link--active" href="adminusers.php"><i class="fas fa-users me-1"></i> Users</a></li>
                <li class="nav-item"><a class="nav-link" href="adminproducts.php"><i class="fas fa-box me-1"></i> Products</a></li>
                <li class="nav-item"><a class="nav-link" href="adminorders.php"><i class="fas fa-shopping-bag me-1"></i> Orders</a></li>
                <li class="nav-item"><a class="nav-link" href="adminsettings.php"><i class="fas fa-cog me-1"></i> Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- ================= MAIN ================= -->
<main>

<!-- ================= USERS LIST VIEW ================= -->
<section class="admin-hero">
    <div class="container">
        <h1 class="section-title"><i class="fas fa-users me-2"></i>All Users</h1>
        <p class="admin-subtitle">View and manage all registered users on Crocheting Hub</p>
    </div>
</section>

<section class="dashboard-content">
    <div class="container">

        <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>User deleted successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Search & Filter Bar -->
        <div class="users-toolbar mb-4">
            <form method="GET" action="adminusers.php" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold"><i class="fas fa-search me-1"></i>Search Users</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold"><i class="fas fa-filter me-1"></i>Filter by Role</label>
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        <option value="user" <?php echo (isset($_GET['role']) && $_GET['role'] === 'user') ? 'selected' : ''; ?>>User / Buyer</option>
                        <option value="seller" <?php echo (isset($_GET['role']) && $_GET['role'] === 'seller') ? 'selected' : ''; ?>>Seller</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-pink w-100"><i class="fas fa-search me-1"></i>Search</button>
                </div>
                <div class="col-md-2">
                    <a href="adminusers.php" class="btn btn-outline-secondary w-100"><i class="fas fa-redo me-1"></i>Reset</a>
                </div>
            </form>
        </div>

        <!-- Users Count Badge -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <span class="badge users-count-badge">
                    <i class="fas fa-users me-1"></i><?php echo count($users); ?> user<?php echo count($users) !== 1 ? 's' : ''; ?> found
                </span>
            </div>
            <a href="admin.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Dashboard</a>
        </div>

        <!-- Users Table -->
        <div class="admin-panel-section">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><i class="fas fa-user me-1"></i>Full Name</th>
                            <th><i class="fas fa-envelope me-1"></i>Email</th>
                            <th><i class="fas fa-tag me-1"></i>Role</th>
                            <th class="text-center"><i class="fas fa-cogs me-1"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php $serial = 1; foreach ($users as $user): ?>
                            <tr>
                                <td class="fw-bold"><?php echo $serial++; ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                        </div>
                                        <span class="fw-semibold"><?php echo htmlspecialchars($user['full_name']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>" class="text-decoration-none user-email">
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if ($user['role'] === 'seller'): ?>
                                        <span class="badge role-badge role-badge--seller"><i class="fas fa-store me-1"></i>Seller</span>
                                    <?php else: ?>
                                        <span class="badge role-badge role-badge--user"><i class="fas fa-user me-1"></i>User</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="adminusers.php?delete_user=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this user?');" title="Delete User">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-user-slash fa-3x mb-3 d-block" style="color: var(--soft-pink);"></i>
                                        <p class="fs-5 mb-1">No users found</p>
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
