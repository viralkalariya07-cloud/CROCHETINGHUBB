<?php
session_name('SELLER_SESSION');
session_start();

// Database connection
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Ensure user is logged in AND is a seller
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../user/login.php");
    exit();
}
$seller_id = $_SESSION['user_id'];

/* ================= HANDLE POST ACTIONS ================= */
$msg = "";
$active_section = "profile";

// 1. Update Profile — writes ONLY to `selleraccount` (never touches `useraccount`)
if (isset($_POST['update_profile'])) {
    $active_section = "manage";
    $name   = trim($_POST['name'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $city   = trim($_POST['city'] ?? '');
    $bio    = trim($_POST['bio'] ?? '');
    $link   = trim($_POST['link'] ?? '');

    if (!empty($name) && !empty($email)) {
        // Step A: Update the master `users` table (name + email only)
        $stmt_u = mysqli_prepare($conn, "UPDATE users SET full_name=?, email=? WHERE id=?");
        mysqli_stmt_bind_param($stmt_u, "ssi", $name, $email, $seller_id);
        mysqli_stmt_execute($stmt_u);
        mysqli_stmt_close($stmt_u);
        $_SESSION['name']  = $name;
        $_SESSION['email'] = $email;

        // Step B: Ensure `selleraccount` table and its extra columns exist
        mysqli_query($conn, "CREATE TABLE IF NOT EXISTS selleraccount (
            id         INT PRIMARY KEY,
            full_name  VARCHAR(150) DEFAULT '',
            email      VARCHAR(150) DEFAULT '',
            mobile     VARCHAR(20)  DEFAULT '',
            gender     VARCHAR(10)  DEFAULT '',
            city       VARCHAR(100) DEFAULT '',
            bio        TEXT,
            link       VARCHAR(255) DEFAULT ''
        )");
        $sa_cols_res = mysqli_query($conn, "SHOW COLUMNS FROM selleraccount");
        $sa_cols = []; while($c = mysqli_fetch_assoc($sa_cols_res)){ $sa_cols[] = $c['Field']; }
        if (!in_array('city',   $sa_cols)) mysqli_query($conn, "ALTER TABLE selleraccount ADD city   VARCHAR(100) DEFAULT ''");
        if (!in_array('bio',    $sa_cols)) mysqli_query($conn, "ALTER TABLE selleraccount ADD bio    TEXT");
        if (!in_array('link',   $sa_cols)) mysqli_query($conn, "ALTER TABLE selleraccount ADD link   VARCHAR(255) DEFAULT ''");
        if (!in_array('mobile', $sa_cols)) mysqli_query($conn, "ALTER TABLE selleraccount ADD mobile VARCHAR(20)  DEFAULT ''");
        if (!in_array('gender', $sa_cols)) mysqli_query($conn, "ALTER TABLE selleraccount ADD gender VARCHAR(10)  DEFAULT ''");

        // Step C: Upsert into `selleraccount` ONLY
        $sa_check = mysqli_query($conn, "SELECT id FROM selleraccount WHERE id='$seller_id'");
        if (mysqli_num_rows($sa_check) > 0) {
            $sql  = "UPDATE selleraccount SET full_name=?, email=?, mobile=?, gender=?, city=?, bio=?, link=? WHERE id=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssssssi", $name, $email, $mobile, $gender, $city, $bio, $link, $seller_id);
        } else {
            $sql  = "INSERT INTO selleraccount (id, full_name, email, mobile, gender, city, bio, link) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "isssssss", $seller_id, $name, $email, $mobile, $gender, $city, $bio, $link);
        }

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_msg'] = "Profile updated successfully!";
        }
        mysqli_stmt_close($stmt);

        header("Location: selleraccount.php?section=profile");
        exit();
    }
}

// 2. Save Address
if (isset($_POST['save_address'])) {
    $active_section = "address";
    $full_name = $_POST['full_name'] ?? '';
    $mobile    = $_POST['mobile'] ?? '';
    $street    = $_POST['street'] ?? '';
    $city      = $_POST['city'] ?? '';
    $state     = $_POST['state'] ?? '';
    $pincode   = $_POST['pincode'] ?? '';

    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'seller_address'");
    if (mysqli_num_rows($table_check) == 0) {
    $conn2 = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);
    if ($conn2) {
        mysqli_query($conn2, "CREATE TABLE seller_address (id INT AUTO_INCREMENT PRIMARY KEY, seller_id INT, full_name VARCHAR(100), mobile VARCHAR(20), street TEXT, city VARCHAR(50), state VARCHAR(50), pincode VARCHAR(10))");
        mysqli_close($conn2);
    }
    }

    $stmt = mysqli_prepare($conn, "REPLACE INTO seller_address (seller_id, full_name, mobile, street, city, state, pincode) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issssss", $seller_id, $full_name, $mobile, $street, $city, $state, $pincode);
    if(mysqli_stmt_execute($stmt)) {
        $_SESSION['success_msg'] = "Address updated successfully!";
    }
    mysqli_stmt_close($stmt);

    header("Location: selleraccount.php?section=address");
    exit();
}

/* ================= FETCH DATA (After potential updates) ================= */
// Determine active section from URL
if (isset($_GET['section'])) {
    $active_section = $_GET['section'];
}

/* ================= FETCH DATA (After potential updates) ================= */
// Determine active section from URL
if (isset($_GET['section'])) {
    $active_section = $_GET['section'];
}

// Fetch Profile from `selleraccount` (NEVER from `useraccount`)
// First ensure the table exists so the page doesn't crash on first visit
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS selleraccount (
    id        INT PRIMARY KEY,
    full_name VARCHAR(150) DEFAULT '',
    email     VARCHAR(150) DEFAULT '',
    mobile    VARCHAR(20)  DEFAULT '',
    gender    VARCHAR(10)  DEFAULT '',
    city      VARCHAR(100) DEFAULT '',
    bio       TEXT,
    link      VARCHAR(255) DEFAULT ''
)");

$sql = "SELECT u.full_name AS name, u.email,
               sa.mobile, sa.gender, sa.city, sa.bio, sa.link
        FROM users u
        LEFT JOIN selleraccount sa ON u.id = sa.id
        WHERE u.id = '$seller_id' AND u.role = 'seller'";
$user_result = mysqli_query($conn, $sql);

if ($user_result && mysqli_num_rows($user_result) > 0) {
    $user = mysqli_fetch_assoc($user_result);
    $user['mobile'] = $user['mobile'] ?? '';
    $user['gender'] = $user['gender'] ?? '';
    $user['city']   = $user['city']   ?? '';
    $user['bio']    = $user['bio']    ?? '';
    $user['link']   = $user['link']   ?? '';
} else {
    $user = ['name' => $_SESSION['name'] ?? 'Seller', 'email' => $_SESSION['email'] ?? '', 'mobile' => '', 'gender' => '', 'city' => '', 'bio' => '', 'link' => ''];
}

// Fetch Address
$address_result = mysqli_query($conn, "SELECT * FROM seller_address WHERE seller_id='$seller_id'");
$address = ($address_result && mysqli_num_rows($address_result) > 0) ? mysqli_fetch_assoc($address_result) : ["full_name" => "", "mobile" => "", "street" => "", "city" => "", "state" => "", "pincode" => ""];

/* ================= FETCH ORDERS ================= */
// Using sellerproducts instead of productstb
$orders_query = "
    SELECT o.*, p.name as product_name 
    FROM orders o
    JOIN sellerproducts p ON o.product_id = p.id
    WHERE p.seller_id='$seller_id'
";
$orders = mysqli_query($conn, $orders_query);
if (!$orders) { $orders = false; } // Handle case where orders table doesn't exist

/* ================= FETCH FEEDBACK ================= */
// Using seller_feedback instead of feedback
$feedback = mysqli_query($conn, "SELECT * FROM seller_feedback WHERE seller_id='$seller_id' OR order_id IN (SELECT id FROM orders WHERE id IN (SELECT id FROM sellerproducts WHERE seller_id='$seller_id'))");
if (!$feedback) {
    $feedback = mysqli_query($conn, "SELECT * FROM seller_feedback"); // Fallback
}
?>

<?php include("../includes/seller_header.php"); ?>
<?php if(isset($_SESSION['success_msg'])): ?>
    <div class="alert alert-success alert-toast alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 1050;">
        <i class="bi bi-check-circle-fill me-2"></i> <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <script>setTimeout(() => { document.querySelector('.alert-toast')?.classList.remove('show'); }, 3000);</script>
<?php endif; ?>

<title>Seller Account | CrochetingHubb</title>
<link rel="stylesheet" href="../assets/css/selleraccount.css">
<style>
    .account-section { display: none; }
    .account-section.active { display: block; animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .btn-pink { background: linear-gradient(135deg, #ff4785 0%, #e02d6a 100%); color: white; border-radius: 20px; border: none; padding: 8px 25px; }
    .btn-pink:hover { opacity: 0.9; color: white; }
    .alert-toast { position: fixed; top: 20px; right: 20px; z-index: 1050; min-width: 250px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); border: none; border-radius: 10px; background: #fff; border-left: 5px solid #28a745; }
</style>


<div class="container my-5">
    <div class="account-wrapper">
        <div class="account-sidebar">
            <a class="<?= $active_section=='profile'?'active':''; ?>" onclick="showSection('profile', this)"><i class="bi bi-person-circle"></i> My Profile</a>
            <a class="<?= $active_section=='manage'?'active':''; ?>" onclick="showSection('manage', this)"><i class="bi bi-pencil-square"></i> Manage Profile</a>
            <a class="<?= $active_section=='address'?'active':''; ?>" onclick="showSection('address', this)"><i class="bi bi-geo-alt"></i> Seller Address</a>
            <a class="<?= $active_section=='orders'?'active':''; ?>" onclick="showSection('orders', this)"><i class="bi bi-box-seam"></i> My Orders</a>
            <a class="<?= $active_section=='feedback'?'active':''; ?>" onclick="showSection('feedback', this)"><i class="bi bi-star-fill"></i> Feedback</a>
            <a class="<?= $active_section=='contact'?'active':''; ?>" onclick="showSection('contact', this)"><i class="bi bi-chat-dots"></i> User Messages</a>
            <a href="logout.php" class="text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
        </div>

        <div class="account-content">
            <!-- 1. MY PROFILE -->
            <div id="profile" class="account-section <?= $active_section=='profile'?'active':''; ?>">
                <h4 class="section-title">My Profile</h4>
                <div class="row align-items-center">
                    <div class="col-md-3 text-center">
                        <i class="bi bi-person-circle profile-icon" style="font-size: 5rem; color: #d83174;"></i>
                    </div>
                    <div class="col-md-9">
                        <p><strong>Name:</strong> <?= htmlspecialchars($user['name'] ?? $user['full_name'] ?? 'Seller'); ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
                        <p><strong>Contact:</strong> <?= htmlspecialchars($user['mobile'] ?? ''); ?></p>
                        <p><strong>Gender:</strong> <?= ucfirst(htmlspecialchars($user['gender'] ?? '')); ?></p>
                        <p><strong>City:</strong> <?= htmlspecialchars($user['city'] ?? ''); ?></p>
                        <p><strong>Bio:</strong> <?= htmlspecialchars($user['bio'] ?? ''); ?></p>
                        <?php if(!empty($user['link'])): ?>
                            <p><strong>Link:</strong> <a href="<?= htmlspecialchars($user['link']); ?>" target="_blank" class="bio-link"><?= htmlspecialchars($user['link']); ?></a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- 2. MANAGE PROFILE -->
            <div id="manage" class="account-section <?= $active_section=='manage'?'active':''; ?>">
                <h4 class="section-title">Manage Profile</h4>
                <form method="POST">
                    <label>Full Name</label>
                    <input class="form-control mb-3" name="name" value="<?= htmlspecialchars($user['name'] ?? $user['full_name'] ?? ''); ?>">
                    <label>Email Address</label>
                    <input class="form-control mb-3" name="email" value="<?= htmlspecialchars($user['email']); ?>">
                    <label>Mobile Number</label>
                    <input class="form-control mb-3" name="mobile" value="<?= htmlspecialchars($user['mobile'] ?? ''); ?>" placeholder="Enter mobile number" autocomplete="off">
                    <label>Gender</label>
                    <select class="form-control mb-3" name="gender" autocomplete="off">
                        <option value="">Select Gender</option>
                        <option value="male" <?= ($user['gender'] ?? '') =="male"?"selected":""; ?>>Male</option>
                        <option value="female" <?= ($user['gender'] ?? '') =="female"?"selected":""; ?>>Female</option>
                        <option value="other" <?= ($user['gender'] ?? '') =="other"?"selected":""; ?>>Other</option>
                    </select>
                    <label>City</label>
                    <input class="form-control mb-3" name="city" value="<?= htmlspecialchars($user['city'] ?? ''); ?>">
                    <label>Bio</label>
                    <textarea class="form-control mb-3" name="bio"><?= htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    <label>Social/Website Link</label>
                    <input class="form-control mb-3" name="link" value="<?= htmlspecialchars($user['link'] ?? ''); ?>">
                    <button type="submit" name="update_profile" class="btn btn-pink">Save Changes</button>
                </form>
            </div>

            <!-- 3. SELLER ADDRESS -->
            <div id="address" class="account-section <?= $active_section=='address'?'active':''; ?>">
                <h4 class="section-title">Seller Address</h4>
                <form method="POST">
                    <input class="form-control mb-3" name="full_name" placeholder="Business Name / Full Name" value="<?= htmlspecialchars($address['full_name']); ?>">
                    <input class="form-control mb-3" name="mobile" placeholder="Mobile Number" value="<?= htmlspecialchars($address['mobile']); ?>">
                    <input class="form-control mb-3" name="street" placeholder="Street Address" value="<?= htmlspecialchars($address['street']); ?>">
                    <input class="form-control mb-3" name="city" placeholder="City" value="<?= htmlspecialchars($address['city']); ?>">
                    <input class="form-control mb-3" name="state" placeholder="State" value="<?= htmlspecialchars($address['state']); ?>">
                    <input class="form-control mb-3" name="pincode" placeholder="Pincode" value="<?= htmlspecialchars($address['pincode']); ?>">
                    <button type="submit" name="save_address" class="btn btn-pink">Save Address</button>
                </form>
            </div>

            <!-- 4. MY ORDERS -->
            <div id="orders" class="account-section">
                <h4 class="section-title">My Orders</h4>
                <?php if ($orders && mysqli_num_rows($orders) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row=mysqli_fetch_assoc($orders)): ?>
                            <tr>
                                <td>#<?= $row['id']; ?></td>
                                <td><?= htmlspecialchars($row['product_name']); ?></td>
                                <td><?= $row['quantity'] ?? 1; ?></td>
                                <td><?= date("M d, Y", strtotime($row['order_date'] ?? $row['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="text-center py-4 text-muted">No orders found.</p>
                <?php endif; ?>
            </div>

            <!-- 5. FEEDBACK -->
            <div id="feedback" class="account-section">
                <h4 class="section-title">Feedback & Reviews</h4>
                <?php if ($feedback && mysqli_num_rows($feedback) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Rating</th>
                                <th>Message</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row=mysqli_fetch_assoc($feedback)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['customer_name'] ?? 'Guest'); ?></td>
                                <td class="text-warning"><?= $row['rating']; ?> ⭐</td>
                                <td><?= htmlspecialchars($row['feedback']); ?></td>
                                <td><?= date("M d, Y", strtotime($row['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <p class="text-center py-4 text-muted">No feedback received yet.</p>
                <?php endif; ?>
            </div>

            <!-- 6. SELLER CONTACT / MESSAGES -->
            <div id="contact" class="account-section">
                <h4 class="section-title">User Messages</h4>
                <?php include 'sellercontact.php'; ?>
            </div>
        </div>
    </div>
</div>

<!-- FOOTER SECTION -->
<?php include("../includes/seller_footer.php"); ?>

<script>
    function showSection(id, element) {
        // Toggle Sections
        document.querySelectorAll('.account-section').forEach(s => s.classList.remove('active'));
        document.getElementById(id).classList.add('active');

        // Toggle Sidebar Links
        document.querySelectorAll('.account-sidebar a').forEach(a => a.classList.remove('active'));
        element.classList.add('active');
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>