<?php
session_name('USER_SESSION');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../user/login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

/* ---------- DATABASE CONNECTION ---------- */
$conn = mysqli_connect("127.0.0.1", "root", "", "crochetinghubb", 3307);
if (!$conn) {
    die("Database connection failed");
}

/* ENSURE upi_id COLUMN EXISTS */
$check_col = mysqli_query($conn, "SHOW COLUMNS FROM orderss LIKE 'upi_id'");
if (mysqli_num_rows($check_col) == 0) {
    mysqli_query($conn, "ALTER TABLE orderss ADD COLUMN upi_id VARCHAR(100) DEFAULT NULL AFTER payment_method");
}

$msg = "";
$error = "";
$active_panel = "profile";
if (isset($_POST['save_address'])) {
    $active_panel = "address";
    $name    = mysqli_real_escape_string($conn, $_POST['addr_name']);
    $mobile  = mysqli_real_escape_string($conn, $_POST['addr_mobile']);
    $street  = mysqli_real_escape_string($conn, $_POST['addr_street']);
    $city    = mysqli_real_escape_string($conn, $_POST['addr_city']);
    $state   = mysqli_real_escape_string($conn, $_POST['addr_state']);
    $pincode = mysqli_real_escape_string($conn, $_POST['addr_pincode']);

    $q = "INSERT INTO user_address (user_id, full_name, mobile, street, city, state, pincode)
          VALUES ('$user_id','$name','$mobile','$street','$city','$state','$pincode')";
    
    if (mysqli_query($conn, $q)) {
        $msg = "Address saved successfully!";
    } else {
        $error = "Address Error: " . mysqli_error($conn);
    }
}


/* ---------- SAVE / UPDATE PROFILE ---------- */
// Only allow profile save if the session role is 'user' (not seller)
if (isset($_POST['save_profile']) && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'seller')) {
    $active_panel = "profile"; // Switch to profile view after saving

    $name   = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email  = mysqli_real_escape_string($conn, $_POST['email']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $gender = !empty($_POST['gender']) ? mysqli_real_escape_string($conn, $_POST['gender']) : NULL;

    // Check if this email is already used by someone ELSE
    $email_check = mysqli_query($conn, "SELECT id FROM useraccount WHERE email = '$email' AND id != '$user_id'");
    
    if (mysqli_num_rows($email_check) > 0) {
        $error = "This email is already in use by another account.";
        $active_panel = "manage-profile"; // stay on form if error
    } else {
        // Update the master users table as well
        mysqli_query($conn, "UPDATE users SET full_name = '$name', email = '$email' WHERE id = '$user_id'");
        $_SESSION['name'] = $name; // Update session name
        $_SESSION['email'] = $email; // Update session email

        // Now check if our current ID exists in useraccount table
        $id_check = mysqli_query($conn, "SELECT id FROM useraccount WHERE id = '$user_id'");
        
        if (mysqli_num_rows($id_check) == 0) {
            // New user profile -> INSERT
            $sql = "INSERT INTO useraccount (id, full_name, email, mobile, gender)
                    VALUES ('$user_id', '$name', '$email', '$mobile', " . ($gender ? "'$gender'" : "NULL") . ")";
        } else {
            // Existing user profile -> UPDATE
            $sql = "UPDATE useraccount SET
                        full_name = '$name',
                        email = '$email',
                        mobile = '$mobile',
                        gender = " . ($gender ? "'$gender'" : "NULL") . "
                    WHERE id = '$user_id'";
        }

        if (!mysqli_query($conn, $sql)) {
            $error = "Save Error: " . mysqli_error($conn);
            $active_panel = "manage-profile"; // stay on form if error
        } else {
            $msg = "Profile saved successfully!";
        }
    }
}

/* ---------- SEND SELLER MESSAGE ---------- */
if (isset($_POST['send_message'])) {
    $active_panel = "seller-contact";
    $seller_id = mysqli_real_escape_string($conn, $_POST['seller_id']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    if (!empty($seller_id) && !empty($message)) {
        $q = "INSERT INTO seller_messages (user_id, seller_id, message) VALUES ('$user_id', '$seller_id', '$message')";
        if (mysqli_query($conn, $q)) {
             $msg = "Message sent successfully to seller!";
        } else {
             $error = "Error sending message: " . mysqli_error($conn);
        }
    } else {
        $error = "Please select a seller and write a message.";
    }
}

/* ---------- FETCH USER DATA ---------- */
$data = [];
if ($user_id) {
    $query = "
    SELECT ua.*
    FROM useraccount ua
    INNER JOIN users u ON ua.id = u.id
    WHERE u.role = 'user'
      AND u.id = ?
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
    } else {
        // Fetch from users table if useraccount profile is not yet created
        $user_main = mysqli_query($conn, "SELECT full_name, email FROM users WHERE id = '$user_id'");
        if ($user_main && mysqli_num_rows($user_main) > 0) {
            $u = mysqli_fetch_assoc($user_main);
            $data['full_name'] = $u['full_name'];
            $data['email'] = $u['email'];
            // Initialize other fields to avoid undefined index warnings
            $data['mobile'] = '';
            $data['gender'] = '';
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Account – Crocheting Hub</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/useraccount.css" />
</head>
<body>


<!-- ================= NAVBAR ================= -->
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="../index.html">
            🧶 <span>CrochetingHubb</span>
        </a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <!-- Other Options with Icons -->
            <ul class="navbar-nav ms-lg-auto align-items-lg-center gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link" href="user.php"><i class="bi bi-house-door me-1"></i>Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-grid me-1"></i>Categories
                    </a>
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
                    <a class="nav-link nav-link--active d-flex align-items-center gap-2" href="useraccount.php">
                        <i class="bi bi-person-circle fs-5"></i>Account
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- ================= MAIN ================= -->
<main>

<section class="account-hero">
    <div class="container">
        <h1 class="section-title">My Account 👤</h1>
        <p class="account-subtitle">Manage your profile, orders and settings</p>
    </div>
</section>

<section class="dashboard-section">
    <div class="container">
        <div class="row g-4">

            <!-- SIDEBAR -->
            <div class="col-lg-3">
                <nav class="sidebar-nav">
                    <ul class="sidebar-list">
                        <li><button class="sidebar-btn <?php echo $active_panel == 'profile' ? 'sidebar-btn--active' : ''; ?>" data-panel="profile"><i class="fas fa-user"></i><span>My Profile</span></button></li>
                        <li><button class="sidebar-btn <?php echo $active_panel == 'manage-profile' ? 'sidebar-btn--active' : ''; ?>" data-panel="manage-profile"><i class="fas fa-user-cog"></i><span>Manage Profile</span></button></li>
                        <li><button class="sidebar-btn <?php echo $active_panel == 'address' ? 'sidebar-btn--active' : ''; ?>" data-panel="address"><i class="fas fa-map-marker-alt"></i><span>My Address</span></button></li>
                        <li><a href="userpayment.php" class="sidebar-btn" style="text-decoration:none;"><i class="fas fa-credit-card"></i><span>Payment Methods</span></a></li>
                        <li><button class="sidebar-btn <?php echo $active_panel == 'orders' ? 'sidebar-btn--active' : ''; ?>" data-panel="orders"><i class="fas fa-box-open"></i><span>My Orders</span></button></li>
                        <li><button class="sidebar-btn <?php echo $active_panel == 'feedback' ? 'sidebar-btn--active' : ''; ?>" data-panel="feedback"><i class="fas fa-star"></i><span>Feedback</span></button></li>
                        <li><button class="sidebar-btn <?php echo $active_panel == 'seller-contact' ? 'sidebar-btn--active' : ''; ?>" data-panel="seller-contact"><i class="fas fa-envelope"></i><span>Seller Contact</span></button></li>
                        <li><button class="sidebar-btn sidebar-btn--logout" data-panel="logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></button></li>
                    </ul>
                </nav>
            </div>

            <!-- CONTENT -->
            <div class="col-lg-9">
                <div class="content-wrapper">

                    <!-- PROFILE -->
                    <div class="panel <?php echo $active_panel != 'profile' ? 'd-none' : ''; ?>" id="panel-profile">
                        <h5 class="panel-title"><i class="fas fa-user me-2"></i>My Profile</h5>

                        <div class="avatar-area text-center mb-4">
                            <div class="avatar-ring">
                                <img id="avatarPreview" class="avatar-img"
                                     src="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='140' height='140'><rect width='140' height='140' rx='70' fill='%23ffd1e1'/><text x='70' y='82' text-anchor='middle' font-size='58'>👤</text></svg>">
                            </div>
                            <label class="upload-label" for="avatarUpload"><i class="fas fa-camera me-1"></i> Change Photo</label>
                            <input type="file" id="avatarUpload" class="d-none" accept="image/*" onchange="previewAvatar(event)">
                        </div>

                        <div class="profile-preview-info text-center">
                            <h4><?php echo $data['full_name'] ?? 'Guest User'; ?></h4>
                            <p class="text-muted"><?php echo $data['email'] ?? 'Not set'; ?></p>
                            <div class="mt-3">
                                <!-- Mobile and Gender badges removed as per user request -->
                            </div>
                        </div>
                    </div>
 
                     <!-- MANAGE PROFILE -->
                    <div class="panel <?php echo $active_panel != 'manage-profile' ? 'd-none' : ''; ?>" id="panel-manage-profile">
    <h5 class="panel-title">
        <i class="fas fa-user-cog me-2"></i>Manage Profile
    </h5>

    <?php if($msg): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i><?php echo $msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="panel-label">Full Name</label>
                <input class="form-control panel-input"
                       name="full_name"
                       value="<?php echo $data['full_name'] ?? ''; ?>" required>
            </div>

            <div class="col-md-6">
                <label class="panel-label">Email</label>
                <input type="email" class="form-control panel-input"
                       name="email"
                       value="<?php echo $data['email'] ?? ''; ?>" required>
            </div>

            <div class="col-md-6">
                <label class="panel-label">Mobile</label>
                <input class="form-control panel-input"
                       name="mobile"
                       maxlength="10"
                       value="<?php echo $data['mobile'] ?? ''; ?>"
                       oninput="restrictToDigits(this)"
                       autocomplete="off">
            </div>

            <div class="col-md-6">
                <label class="panel-label">Gender</label>
                <select class="form-select panel-input" name="gender" autocomplete="off">
                    <option value="">Select</option>
                    <option value="male" <?php echo (isset($data['gender']) && $data['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                    <option value="female" <?php echo (isset($data['gender']) && $data['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                    <option value="other" <?php echo (isset($data['gender']) && $data['gender'] == 'other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-between align-items-center">
            <button type="submit" name="save_profile" class="btn btn-pink">
                Save Changes
            </button>
        </div>
    </form>
</div>


                    <!-- ADDRESS -->
                    <div class="panel d-none" id="panel-address">
                        <h5 class="panel-title"><i class="fas fa-map-marker-alt me-2"></i>My Address</h5>
                        
                        <!-- Saved Address Display -->
                        <div id="savedAddressDisplay" class="mb-4 d-none">
                            <div class="saved-address-card p-3 rounded shadow-sm">
                                <h6 class="text-pink fw-bold mb-1"><i class="fas fa-check-circle me-1"></i> Current Saved Address:</h6>
                                <p id="displayAddressContent" class="mb-0 small text-dark"></p>
                            </div>
                        </div>
                        <form method="POST">
                            <input class="form-control panel-input mb-2" name="addr_name" placeholder="Full Name">
                            <input class="form-control panel-input mb-2" name="addr_mobile" placeholder="Mobile">
                            <input class="form-control panel-input mb-2" name="addr_street" placeholder="Street Address">
                            <input class="form-control panel-input mb-2" name="addr_city" placeholder="City">
                            <input class="form-control panel-input mb-2" name="addr_state" placeholder="State">
                            <input class="form-control panel-input mb-2" name="addr_pincode" placeholder="Pincode">

                            <button class="btn btn-pink mt-3" name="save_address">
                                Save Address
                            </button>
                        </form>
                    </div>

                    <!-- ORDERS -->
                    <div class="panel <?php echo $active_panel != 'orders' ? 'd-none' : ''; ?>" id="panel-orders">
                        <h5 class="panel-title"><i class="fas fa-box-open me-2"></i>My Orders</h5>
                        <?php
                        $orders_query = "SELECT o.*, p.name as product_name, p.image as product_image 
                                         FROM orderss o 
                                         JOIN sellerproducts p ON o.product_id = p.id 
                                         WHERE o.customer_id = '$user_id' 
                                         ORDER BY o.order_date DESC";
                        $orders_res = mysqli_query($conn, $orders_query);
                        
                        if (mysqli_num_rows($orders_res) > 0): 
                        ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Order</th>
                                            <th>Product</th>
                                            <th>Qty</th>
                                            <th>Total</th>
                                            <th>Payment</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($order = mysqli_fetch_assoc($orders_res)): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="../seller/uploads/<?php echo htmlspecialchars($order['product_image']); ?>" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                                    <span class="small fw-semibold"><?php echo htmlspecialchars($order['product_name']); ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo $order['quantity']; ?></td>
                                            <td class="text-pink fw-bold">₹<?php echo number_format($order['total_price'], 2); ?></td>
                                            <td>
                                                <span class="badge bg-light text-dark border small">
                                                    <?php echo htmlspecialchars($order['payment_method'] ?? 'N/A'); ?>
                                                </span>
                                                <?php if (strtolower($order['payment_method'] ?? '') === 'upi' && !empty($order['upi_id'])): ?>
                                                    <div class="text-muted small mt-1" style="font-size: .7rem;">ID: <?php echo htmlspecialchars($order['upi_id']); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $s = $order['status'];
                                                $badge = 'bg-warning';
                                                if($s == 'Delivered' || $s == 'Accepted') $badge = 'bg-success';
                                                if($s == 'Cancelled') $badge = 'bg-danger';
                                                ?>
                                                <span class="badge <?php echo $badge; ?>"><?php echo $s; ?></span>
                                            </td>
                                            <td class="small text-muted"><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 bg-light rounded">
                                <i class="fas fa-shopping-bag fa-3x mb-3 text-muted"></i>
                                <p class="text-muted">You haven't placed any orders yet.</p>
                                <a href="user.php" class="btn btn-pink btn-sm">Start Shopping</a>
                            </div>
                        <?php endif; ?>
                    </div>


                    <!-- FEEDBACK -->
                    <div class="panel <?php echo $active_panel == 'feedback' ? '' : 'd-none'; ?>" id="panel-feedback">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="panel-title mb-0"><i class="fas fa-star me-2"></i>My Feedbacks</h5>
                            <a href="userfeedback.php" class="btn btn-sm btn-pink">Give Feedback</a>
                        </div>
                        
                        <div class="recent-feedback-list">
                            <?php
                            // Assuming we identify user by name or email stored in session
                            $userName = $_SESSION['name'] ?? 'Viral Kalariya'; 
                            $query = "SELECT * FROM user_feedback WHERE full_name = '$userName' ORDER BY id DESC LIMIT 5";
                            $result = mysqli_query($conn, $query);

                            if(mysqli_num_rows($result) > 0) {
                                while($row = mysqli_fetch_assoc($result)) {
                                    ?>
                                    <div class="feedback-item-card mb-3 p-3 shadow-sm border rounded">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-soft-pink text-pink">Order: <?php echo $row['order_id']; ?></span>
                                            <div class="rating-stars small">
                                                <?php 
                                                for($i=1; $i<=5; $i++){
                                                    echo $i <= $row['rating'] ? '<i class="fas fa-star text-warning"></i>' : '<i class="far fa-star text-muted"></i>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <p class="mb-1 text-dark small">"<?php echo $row['message']; ?>"</p>
                                        <div class="text-end">
                                            <small class="text-muted italic" style="font-size: 0.7rem;">Feedback ID: <?php echo $row['feedback_id']; ?></small>
                                        </div>
                                    </div>
                                    <?php
                                }
                            } else {
                                echo '<div class="text-center py-4 bg-light rounded"><i class="fas fa-comment-slash fa-2x mb-2 text-muted"></i><p class="mb-0 text-muted">No recent feedback given.</p></div>';
                            }
                            ?>
                        </div>
                    </div>

                    <!-- SELLER CONTACT -->
                    <!-- SELLER CONTACT -->
                    <div class="panel <?php echo $active_panel == 'seller-contact' ? '' : 'd-none'; ?>" id="panel-seller-contact">
                        <h5 class="panel-title"><i class="fas fa-envelope me-2"></i>Seller Contact</h5>
                        
                        <?php if($active_panel == 'seller-contact' && $msg): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fas fa-check-circle me-2"></i><?php echo $msg; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($active_panel == 'seller-contact' && $error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="panel-label">Select Seller</label>
                                    <div class="seller-search-container position-relative">
                                        <input type="text" class="form-control panel-input" id="sellerSearchInput" placeholder="🔍 Search or choose a seller..." autocomplete="off" onfocus="showSellerOptions()" onkeyup="filterSellers()" required>
                                        <div id="sellerDropdown" class="seller-dropdown-list d-none shadow-sm">
                                            <?php
                                            $sellers_res = mysqli_query($conn, "SELECT id, full_name FROM users WHERE role = 'seller' ORDER BY full_name ASC");
                                            if ($sellers_res && mysqli_num_rows($sellers_res) > 0):
                                                while ($s = mysqli_fetch_assoc($sellers_res)):
                                                    $sid  = $s['id'];
                                                    $sname = htmlspecialchars($s['full_name'], ENT_QUOTES);
                                            ?>
                                                <div class="seller-option" onclick="selectSeller('<?php echo $sname; ?>', '<?php echo $sid; ?>')"><?php echo $sname; ?></div>
                                            <?php
                                                endwhile;
                                            else:
                                            ?>
                                                <div class="seller-option text-muted" style="pointer-events:none;">No sellers available</div>
                                            <?php endif; ?>
                                        </div>
                                        <input type="hidden" name="seller_id" id="contactSellerId">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="panel-label">Message</label>
                                    <textarea name="message" id="contactMsg" class="form-control panel-input" rows="4" placeholder="How can we help you?" required></textarea>
                                </div>
                            </div>
                            <button type="submit" name="send_message" class="btn btn-pink mt-3">Send Message</button>
                        </form>
                    </div>

                    <!-- LOGOUT -->
                    <div class="panel d-none" id="panel-logout">
                        <button class="btn btn-danger" onclick="confirmLogout()">Confirm Logout</button>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>
</main>

<!-- MODAL -->
<div class="modal fade" id="alertModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content modal-custom">
            <div class="modal-body text-center">
                <div id="modalIcon"></div>
                <h6 id="modalTitle"></h6>
                <p id="modalMessage"></p>
                <button class="btn btn-pink mt-3" onclick="closeModal()">OK</button>
            </div>
        </div>
    </div>
</div>

<footer class="premium-footer text-center py-3">
    &copy; 2025 Crocheting Hub
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
(function () {
"use strict";

let cb=null;
function showModal(t,ti,m,o){
modalIcon.innerHTML=t==="success"?"✔":"ℹ";
modalTitle.innerText=ti;
modalMessage.innerText=m;
cb=o||null;
bootstrap.Modal.getOrCreateInstance(alertModal).show();
}
window.closeModal=function(){
bootstrap.Modal.getOrCreateInstance(alertModal).hide();
if(cb) cb();
};

window.switchPanel=function(p){
document.querySelectorAll(".panel").forEach(x=>x.classList.add("d-none"));
document.getElementById("panel-"+p)?.classList.remove("d-none");
};

document.addEventListener("click",e=>{
let b=e.target.closest(".sidebar-btn");
if(b) {
    document.querySelectorAll(".sidebar-btn").forEach(btn=>btn.classList.remove("sidebar-btn--active"));
    b.classList.add("sidebar-btn--active");
    switchPanel(b.dataset.panel);
}
});

window.previewAvatar=e=>{
avatarPreview.src=URL.createObjectURL(e.target.files[0]);
};

window.restrictToDigits=e=>e.value=e.value.replace(/\D/g,"");

window.showSellerOptions=()=>document.getElementById('sellerDropdown').classList.remove('d-none');
window.filterSellers=()=>{
    let i=document.getElementById('sellerSearchInput').value.toLowerCase();
    document.querySelectorAll('.seller-option').forEach(o=>o.style.display=o.innerText.toLowerCase().includes(i)?'block':'none');
};
window.selectSeller=(n,id)=>{
    document.getElementById('sellerSearchInput').value=n;
    document.getElementById('contactSellerId').value=id;
    document.getElementById('sellerDropdown').classList.add('d-none');
};
document.addEventListener('click',e=>{if(!e.target.closest('.seller-search-container'))document.getElementById('sellerDropdown')?.classList.add('d-none');});

window.saveAddress=()=>{
    const name = document.getElementById('addrName').value;
    const mobile = document.getElementById('addrMobile').value;
    const street = document.getElementById('addrStreet').value;
    const city = document.getElementById('addrCity').value;
    const state = document.getElementById('addrState').value;
    const pincode = document.getElementById('addrPincode').value;

    if(name && mobile && street && city && state && pincode) {
        const display = document.getElementById('savedAddressDisplay');
        const content = document.getElementById('displayAddressContent');
        
        content.innerHTML = `<strong>${name}</strong><br>${street}, ${city}, ${state} - ${pincode}<br>Mobile: ${mobile}`;
        display.classList.remove('d-none');
        
        showModal("success","Saved","Address updated successfully");
    } else {
        showModal("info","Incomplete","Please fill all address fields");
    }
};
window.saveManageProfile=()=>showModal("success","Saved","Profile updated successfully");
window.submitFeedback=()=>showModal("success","Thanks","Feedback sent");
window.sendSellerMessage=()=>showModal("success","Sent","Your message has been sent to the seller");
window.deleteAccount=()=>{
    if(confirm("Are you sure you want to permanently delete your account? This action cannot be undone.")){
        showModal("info","Account Deleted","We are sad to see you go.");
        setTimeout(()=>window.location.href="login.php", 2000);
    }
};
window.confirmLogout=()=>showModal("info","Logged out","See you soon!", () => window.location.href = "logout.php");
})();
</script>

</body>
</html>
